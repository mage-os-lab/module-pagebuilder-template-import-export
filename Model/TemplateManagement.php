<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\Api\ImageContent;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\PageBuilderTemplateImportExport\Api\ZipArchive;
use Magento\Framework\Data\Wysiwyg\Normalizer;
use MageOS\PageBuilderTemplateImportExport\Helper\Aliases as TemplateAliasHelper;
use Magento\PageBuilder\Model\TemplateRepository;
use MageOS\PageBuilderTemplateImportExport\DataConverter\CmsConverter;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Api\ImageContentFactory;
use Magento\Framework\Api\ImageContentValidator;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Xml\Parser as XmlParser;

class TemplateManagement implements \MageOS\PageBuilderTemplateImportExport\Api\TemplateManagementInterface
{
    /**
     * @param CmsConverter $cmsConverter
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param TemplateRepository $templateRepository
     * @param TemplateFactory $templateFactory
     * @param AdapterFactory $imageAdapterFactory
     * @param ImageContentFactory $imageContentFactory
     * @param Database $mediaStorage
     * @param ImageContentValidator $imageContentValidator
     * @param XmlParser $xmlParser
     */
    public function __construct(
        private readonly CmsConverter          $cmsConverter,
        private readonly Filesystem            $filesystem,
        private readonly FileDriver            $fileDriver,
        private readonly StoreManagerInterface $storeManager,
        private readonly TemplateRepository    $templateRepository,
        private readonly TemplateFactory       $templateFactory,
        private readonly Normalizer            $wysiswygNormalizer,
        private readonly AdapterFactory        $imageAdapterFactory,
        private readonly ImageContentFactory   $imageContentFactory,
        private readonly Database              $mediaStorage,
        private readonly ImageContentValidator $imageContentValidator,
        private readonly ConvertArray          $convertArray,
        private readonly XmlParser             $xmlParser
    ) {}

    /**
     * @param string $sourcePath
     * @param string|null $destinationPath
     * @return array
     */
    private function copyAssetsFilesToMediaDirectory(string $sourcePath, string $destinationPath = null): array
    {
        $exceptionMessages = [];

        if (!$destinationPath) {

            $destinationPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        }
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($sourcePath, $flags);
        /** @var \FilesystemIterator $entity */

        foreach ($iterator as $entity) {

            try {

                if ($entity->isDir()) {

                    $exceptionMessages = array_merge($exceptionMessages, $this->copyAssetsFilesToMediaDirectory($entity->getPathname(), $destinationPath . $entity->getFilename()));
                } else {

                    $this->fileDriver->copy($entity->getPathname(), $destinationPath . $entity->getFilename());
                }
            } catch (FileSystemException $exception) {

                $exceptionMessages[] = $exception->getMessage();
            }
        }

        return $exceptionMessages;
    }

    /**
     * @param $preview
     * @return string|null
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws InputException
     */
    private function storePreviewImage($preview): ?string
    {
        $fileName = preg_replace("/[^A-Za-z0-9]/", '', str_replace(
                ' ',
                '-',
                "import"
            )) . uniqid() . '.jpg';

        // phpcs:ignore
        $decodedImage = $preview;

        $imageProperties = getimagesizefromstring($decodedImage);

        if (!$imageProperties) {

            throw new LocalizedException(__('Unable to get properties from image.'));
        }

        /* @var ImageContent $imageContent */
        $imageContent = $this->imageContentFactory->create();
        $imageContent->setBase64EncodedData(base64_encode($preview));
        $imageContent->setName($fileName);
        $imageContent->setType($imageProperties['mime']);

        if ($this->imageContentValidator->isValid($imageContent)) {

            $mediaDirWrite = $this->filesystem
                ->getDirectoryWrite(DirectoryList::MEDIA);
            $directory = $mediaDirWrite->getAbsolutePath('.template-manager');
            $mediaDirWrite->create($directory);
            $fileAbsolutePath = $directory . $fileName;
            // Write the file to the directory
            $mediaDirWrite->getDriver()->filePutContents($fileAbsolutePath, $decodedImage);
            // Generate a thumbnail, called -thumb next to the image for usage in the grid
            $thumbPath = str_replace('.jpg', '-thumb.jpg', $fileName);
            $thumbAbsolutePath = $directory . $thumbPath;
            $imageFactory = $this->imageAdapterFactory->create();
            $imageFactory->open($fileAbsolutePath);
            $imageFactory->resize(350);
            $imageFactory->save($thumbAbsolutePath);
            $this->mediaStorage->saveFile($fileAbsolutePath);
            $this->mediaStorage->saveFile($thumbAbsolutePath);
            // Store the preview image within the new entity
            return $mediaDirWrite->getRelativePath($fileAbsolutePath);
        }

        return null;
    }

    /**
     * @param TemplateInterface $template
     * @return array|string
     * @throws DataConversionException
     */
    private function convertTemplateHtml($template) {
        return $this->cmsConverter->convert($template->getTemplate());
    }

    /**
     * @param string $exportFile
     * @return array
     * @throws FileSystemException
     */
    public function openExportArchive(string $exportFile) : array
    {
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_EXPORT);
        $exportDestination = $writer->getAbsolutePath() . $exportFile;
        $zip = new \ZipArchive();
        $zip->open($exportDestination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        return [$zip, $writer];
    }

    /**
     * @param string $path
     * @return void
     * @throws FileSystemException
     */
    public function deleteTmpFolder(string $path) : void {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::VAR_EXPORT)->getAbsolutePath() . $path;
        $this->fileDriver->deleteDirectory($path);
    }

    /**
     * @param \ZipArchive $zip
     * @return void
     */
    public function closeExportArchive(\ZipArchive $zip) : void {
        $zip->close();
    }

    /**
     * @param WriteInterface $writer
     * @param \ZipArchive $zip
     * @param TemplateInterface $template
     * @param string $exportPath
     * @return void
     * @throws DataConversionException
     */
    public function generateTemplateFileAndRelativeAssets(WriteInterface $writer, \ZipArchive $zip, TemplateInterface $template, string $exportPath) : void
    {
        $convertedTemplate = $this->convertTemplateHtml($template);
        $exportName = TemplateAliasHelper::TEMPLATE_FILE;
        $templateFile = $writer->openFile($exportPath . "/" . $exportName, 'w');

        try {

            $templateFile->lock();
            try {

                $templateFile->write($convertedTemplate["value"]);
            }
            finally {

                $templateFile->unlock();
            }
        }
        finally {

            $templateFile->close();
            $zip->addFile($writer->getAbsolutePath() . $exportPath . "/" . $exportName, $exportName);
        }

        foreach ($convertedTemplate["assets"] as $asset) {
            /** @var ReadInterface $reader */
            $reader = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
            $zip->addFile(
                $reader->getAbsolutePath() . $asset,
                TemplateAliasHelper::ASSETS_FOLDER_NAME . "/" . $asset
            );
        }
    }

    /**
     * @param WriteInterface $writer
     * @param \ZipArchive $zip
     * @param TemplateInterface $template
     * @param string $exportPath
     * @return void
     * @throws DataConversionException
     */
    public function generateTemplatePreviewFile(WriteInterface $writer, \ZipArchive $zip, TemplateInterface $template, string $exportPath) : void
    {
        $previewFile = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $template->getPreviewImage();
        $zip->addFile($previewFile, TemplateAliasHelper::PREVIEW_FILE);
    }

    /**
     * @param WriteInterface $writer
     * @param \ZipArchive $zip
     * @param array $configXml
     * @param string $exportPath
     * @return void
     * @throws FileSystemException
     */
    public function generateConfigFile(WriteInterface $writer, \ZipArchive $zip, array $configXml, string $exportPath) : void
    {
        $exportName = TemplateAliasHelper::CONFIG_FILE;
        $simpleXmlContents = $this->convertArray->assocToXml($configXml,"config");
        $configXml = $simpleXmlContents->asXML();
        $configFile = $writer->openFile($exportPath . "/" . $exportName, 'w');

        try {

            $configFile->lock();
            try {

                $configFile->write($configXml);
            }
            finally {

                $configFile->unlock();
            }
        }
        finally {

            $configFile->close();
            $zip->addFile(
                $writer->getAbsolutePath() . $exportPath . "/" . $exportName,
                TemplateAliasHelper::CONFIG_FILE
            );
        }
    }

    /**
     * @param string $exportFile
     * @param string $exportPath
     * @param TemplateInterface $template
     * @param array $config
     * @return string
     * @throws DataConversionException
     * @throws FileSystemException
     */
    public function exportTemplateToArchive(string $exportFile, string $exportPath, TemplateInterface $template, array $config) : string
    {
        /**
         * @var \ZipArchive $zip
         * @var WriteInterface $writer
         */
        list($zip, $writer) = $this->openExportArchive($exportFile);
        $this->generateTemplateFileAndRelativeAssets($writer, $zip, $template, $exportPath);
        $this->generateTemplatePreviewFile($writer, $zip, $template, $exportPath);
        $this->generateConfigFile($writer, $zip, $config, $exportPath);
        $this->closeExportArchive($zip);
        $this->deleteTmpFolder($exportPath);
        return $writer->getAbsolutePath() . $exportFile;
    }

    /**
     * @param string $importPath
     * @return int
     * @throws FileSystemException
     * @throws InputException
     * @throws LocalizedException
     */
    public function importTemplateFromArchive(string $importPath) : int
    {
        $importedTemplate = null;
        $reader = $this->filesystem->getDirectoryRead(DirectoryList::VAR_EXPORT);
        $zip = new \ZipArchive();
        $zip->open($importPath);
        $tmpFolder = $reader->getAbsolutePath() . "tmp";
        $zip->extractTo($tmpFolder);
        $zip->close();
        $templateHtmlContent = $reader->readFile($tmpFolder . "/" . TemplateAliasHelper::TEMPLATE_FILE);

        $baseUrl = trim($this->storeManager->getStore()->getBaseUrl(), "/");
        $baseUrl = $this->wysiswygNormalizer->replaceReservedCharacters($baseUrl);
        $templateHtmlContent = str_replace(TemplateAliasHelper::CMS_WIDGET_URL_PLACEHOLDER, $baseUrl, $templateHtmlContent);
        $previewFileName = $this->storePreviewImage($reader->readFile($tmpFolder . "/" . TemplateAliasHelper::PREVIEW_FILE));
        $exceptionMessages = $this->copyAssetsFilesToMediaDirectory(
            $tmpFolder . "/" . TemplateAliasHelper::ASSETS_FOLDER_NAME . "/media/",
            $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
        );

        if (empty($exceptionMessages)) {

            $config = $this->xmlParser->load($tmpFolder . "/" . TemplateAliasHelper::CONFIG_FILE)->xmlToArray()["config"];
            $template = $this->templateFactory->create();
            $template->setName($config["name"]);
            $template->setTemplate($templateHtmlContent);
            $template->setCreatedFor($config["type"]);
            $template->setPreviewImage($previewFileName);
            $importedTemplate = $this->templateRepository->save($template);
            $this->fileDriver->deleteDirectory($tmpFolder);
        }

        if ($importedTemplate) {

            return intval($importedTemplate->getId());
        }

        return 0;
    }
}