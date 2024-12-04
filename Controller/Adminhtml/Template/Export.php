<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\PageBuilder\Api\TemplateRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\TemplateManagementInterface;
use MageOS\PageBuilderTemplateImportExport\Helper\Aliases as TemplateAliasHelper;
use Psr\Log\LoggerInterface;

class Export extends Action implements HttpGetActionInterface
{

    public const ADMIN_RESOURCE = 'MageOS_PageBuilderTemplateImportExport::pagebuilder_template_export';

    /**
     * @param LoggerInterface $logger
     * @param TemplateRepositoryInterface $templateRepository
     * @param TemplateManagementInterface $templateManagement
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param Context $context
     */
    public function __construct(
        private LoggerInterface $logger,
        private TemplateRepositoryInterface $templateRepository,
        private TemplateManagementInterface $templateManagement,
        private FileFactory $fileFactory,
        private Filesystem $filesystem,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Export template
     *
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();

        try {
            $template = $this->templateRepository->get($request->getParam('template_id'));
            $exportFile = TemplateAliasHelper::DEFAULT_TEMPLATE_ARCHIVE_FILENAME;
            $exportPath = 'tmp';
            $config = [
                'name' => $template->getName(),
                'type' => $template->getCreatedFor(),
                'description' => '',
                'themes' => ''
            ];

            $exportedArchivePath = $this->templateManagement->exportTemplate(
                $exportFile,
                $exportPath,
                $template,
                $config
            );

            $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_EXPORT);
            if ($directory->isFile($exportedArchivePath)) {
                return $this->fileFactory->create(
                    $exportedArchivePath,
                    ['type' => 'filename', 'value' => $exportedArchivePath],
                    DirectoryList::VAR_EXPORT
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $this->messageManager->addErrorMessage(__('An error occurred while trying to export this template.'));
        return $resultRedirect->setPath('*/*/');
    }
}
