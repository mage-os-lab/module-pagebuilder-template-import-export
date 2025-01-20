<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteTemplateRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterfaceFactory;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate\CollectionFactory;
use Magento\Framework\Xml\Parser as XmlParser;
use MageOS\PageBuilderTemplateImportExport\Api\DropboxInterface;

class RemoteStorageManagement implements RemoteStorageManagementInterface
{
    /**
     * @param XmlParser $xmlParser
     * @param RemoteTemplateRepositoryInterface $remoteTemplateRepository
     * @param RemoteTemplateInterfaceFactory $remoteTemplateFactory
     * @param CollectionFactory $remoteTemplateCollectionFactory
     * @param DropboxInterface $dropbox
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected readonly XmlParser $xmlParser,
        protected readonly RemoteTemplateRepositoryInterface $remoteTemplateRepository,
        protected readonly RemoteTemplateInterfaceFactory $remoteTemplateFactory,
        protected readonly CollectionFactory $remoteTemplateCollectionFactory,
        protected readonly DropboxInterface $dropbox,
        protected readonly SerializerInterface $serializer,
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return void
     */
    public function updateRemoteTemplatesInformations(): void
    {
        $dropboxCredentials = $this->scopeConfig
            ->getValue('pagebuilder_template_importexport/general/dropbox_credentials');

        $templates = [];
        foreach ($this->serializer->unserialize($dropboxCredentials) as $credentials) {
            $templateList = $this->dropbox->listTemplates(
                "",
                false,
                $credentials["app_key"],
                $credentials["app_secret"],
                $credentials["refresh_token"]
            );
            if (isset($templateList["entries"])) {
                foreach ($templateList["entries"] as $template) {
                    if ($template[".tag"] === "folder") {
                        $templateContent = $this->dropbox->listFolder(
                            $template["path_lower"],
                            false,
                            $credentials["app_key"],
                            $credentials["app_secret"],
                            $credentials["refresh_token"]
                        );
                        $templateName = $template["name"];
                        $templates[$templateName]["name"] = $template["name"];
                        $templates[$templateName]["id"] = $template["id"];
                        foreach ($templateContent["entries"] as $content) {
                            if ($content["name"] === "template.html") {
                                $templates[$templateName]["last_update"] = $content["server_modified"];
                            }
                            if ($content["name"] === "preview.jpg") {
                                $templates[$templateName]["thumb"] = base64_encode($this->dropbox->getThumbnail(
                                    $content["path_display"],
                                    'jpeg',
                                    'w256h256',
                                    $credentials["app_key"],
                                    $credentials["app_secret"],
                                    $credentials["refresh_token"]
                                ));
                            }
                        }
                        $templates[$templateName]["file"] = $template["path_lower"];
                        $templates[$templateName]["storage_id"] = $credentials["app_key"];
                    }
                }
            }
        }
        foreach ($templates as $name => $template) {
            try {
                try {
                    $remoteTemplate = $this->remoteTemplateRepository
                        ->getByTemplateAndStorageId($template["id"], $template["storage_id"]);
                } catch (NoSuchEntityException $e) {
                    $remoteTemplate = $this->remoteTemplateFactory->create();
                    $remoteTemplate->setTemplateId($template["id"]);
                    $remoteTemplate->setRemoteStorageId($template["storage_id"]);
                }
                $remoteTemplate->setTemplateName($template["name"]);
                $remoteTemplate->setFilePath($template["file"]);
                $remoteTemplate->setPreview($template["thumb"]);
                $this->remoteTemplateRepository->save($remoteTemplate);
            } catch (\Exception $e) {
                //TODO LOG SOMETHING
            }
        }
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return RemoteTemplateInterface[]
     */
    public function listRemoteTemplates(): array
    {
        return $this->remoteTemplateCollectionFactory->create()->toArray();
    }
}
