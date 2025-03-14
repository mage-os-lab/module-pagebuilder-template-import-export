<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteTemplateRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteCursorRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterfaceFactory;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate\CollectionFactory;
use Magento\Framework\Xml\Parser as XmlParser;
use MageOS\PageBuilderTemplateImportExport\Api\DropboxInterface;
use MageOS\PageBuilderTemplateImportExport\Helper\ModuleConfig;
use Magento\Framework\Stdlib\DateTime\DateTime;

class RemoteStorageManagement implements RemoteStorageManagementInterface
{
    /**
     * @param XmlParser $xmlParser
     * @param RemoteTemplateRepositoryInterface $remoteTemplateRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param RemoteTemplateInterfaceFactory $remoteTemplateFactory
     * @param CollectionFactory $remoteTemplateCollectionFactory
     * @param DropboxInterface $dropbox
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleConfig $moduleConfig
     * @param DateTime $dateTime
     */
    public function __construct(
        protected XmlParser $xmlParser,
        protected RemoteTemplateRepositoryInterface $remoteTemplateRepository,
        protected RemoteCursorRepositoryInterface $remoteCursorRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        protected RemoteTemplateInterfaceFactory $remoteTemplateFactory,
        protected CollectionFactory $remoteTemplateCollectionFactory,
        protected DropboxInterface $dropbox,
        protected SerializerInterface $serializer,
        protected ScopeConfigInterface $scopeConfig,
        protected ModuleConfig $moduleConfig,
        protected DateTime $dateTime
    ) {
    }

    /**
     * @param $fullSync
     * @param array $credentials
     * @return void
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     */
    public function updateRemoteTemplatesInformations($fullSync = false, array $credentials = []): void
    {
        if (empty($credentials)) {
            $dropboxCredentials = $this->moduleConfig->getDropboxCredentials();
        } else {
            $dropboxCredentials = [$credentials];
        }
        $templates = [];
        foreach ($dropboxCredentials as $credentials) {
            $remoteTemplatesCount = 0;
            if (!$fullSync) {
                $searchCriteria = $this->searchCriteriaBuilderFactory->create()
                    ->addFilter('remote_storage_id', $credentials["app_key"])
                    ->create();
                $searchResult = $this->remoteTemplateRepository->getList($searchCriteria);
                $remoteTemplatesCount = $searchResult->getTotalCount();
            }
            if ($remoteTemplatesCount === 0) {
                //full sync from remote
                $templateList = $this->dropbox->listFolder(
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
                            $templateContent["name"] = $template["name"];
                            $templateContent["id"] = $template["id"];
                            $templateContent["path_lower"] = $template["path_lower"];
                            $this->populateTemplatesInsertData($templateContent, $credentials, $templates);
                        }
                    }
                }
            } else {
                //delta sync from remote
                $latestCursor = $this->remoteCursorRepository->getByStorageId($credentials["app_key"]);
                if ($latestCursor->getStorageId()) {
                    $listFolderContinue = $this->dropbox->listFolderContinue(
                        $latestCursor->getLatestCursor(),
                        $credentials["app_key"],
                        $credentials["app_secret"],
                        $credentials["refresh_token"]
                    );
                    if (isset($listFolderContinue["entries"]) && !empty($listFolderContinue["entries"])) {
                        foreach ($listFolderContinue["entries"] as $entry) {
                            if ($entry[".tag"] === "deleted") {
                                try {
                                    $remoteTemplate = $this->remoteTemplateRepository
                                        ->getByTemplateNameAndStorageId($entry["name"], $credentials["app_key"]);
                                    $this->remoteTemplateRepository->delete($remoteTemplate);
                                } catch (NoSuchEntityException $e) {
                                    //Do nothing and update latestCursor later
                                }
                            }
                            if ($entry[".tag"] === "folder") {
                                $templateContent = $this->dropbox->listFolder(
                                    $entry["path_lower"],
                                    false,
                                    $credentials["app_key"],
                                    $credentials["app_secret"],
                                    $credentials["refresh_token"]
                                );
                                $templateContent["name"] = $entry["name"];
                                $templateContent["id"] = $entry["id"];
                                $templateContent["path_lower"] = $entry["path_lower"];
                                $this->populateTemplatesInsertData($templateContent, $credentials, $templates);
                                try {
                                    $remoteTemplate = $this->remoteTemplateRepository
                                        ->getByTemplateNameAndStorageId($entry["name"], $credentials["app_key"]);
                                    $templates[$templateContent["name"]]["entity_id"] = $remoteTemplate->getId();
                                } catch (NoSuchEntityException $e) {
                                    $templates[$templateContent["name"]]["entity_id"] = 0;
                                }
                            }
                        }
                    }
                    $latestCursor->setLatestCursor($listFolderContinue["cursor"]);
                    $this->remoteCursorRepository->save($latestCursor);
                }
            }
        }
        foreach ($templates as $name => $template) {
            if (!isset($template["entity_id"])) {
                try {
                    $remoteTemplate = $this->remoteTemplateRepository
                        ->getByTemplateNameAndStorageId($template["name"], $template["storage_id"]);
                    $template["entity_id"] = $remoteTemplate->getId();
                } catch (NoSuchEntityException $e) {
                    $template["entity_id"] = 0;
                    $remoteTemplate = $this->remoteTemplateFactory->create();
                    $remoteTemplate->setTemplateId($template["id"]);
                }
            } else {
                $remoteTemplate = $this->remoteTemplateFactory->create();
            }
            $remoteTemplate->setRemoteStorageId($template["storage_id"]);
            $remoteTemplate->setTemplateName($template["name"]);
            $remoteTemplate->setFilePath($template["file"]);
            $remoteTemplate->setPreview($template["thumb"]);
            $remoteTemplate->setLastUpdate($template["last_update"]);
            if ($template["entity_id"]) {
                $remoteTemplate->setId($template["entity_id"]);
            }
            $this->remoteTemplateRepository->save($remoteTemplate);
        }
    }

    /**
     * @param array $credentials
     * @return void
     * @throws CouldNotDeleteException
     */
    public function deleteRemoteTemplates(array $credentials): void
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('remote_storage_id', $credentials["app_key"])
            ->create();
        foreach ($this->remoteTemplateRepository->getList($searchCriteria)->getItems() as $remoteTemplate) {
            $this->remoteTemplateRepository->delete($remoteTemplate);
        }
    }

    /**
     * @param $templateContent
     * @param $credentials
     * @param $templates
     * @return void
     */
    protected function populateTemplatesInsertData($templateContent, $credentials, &$templates): void
    {
        $templateName = $templateContent["name"];
        $templates[$templateName]["name"] = $templateName;
        $templates[$templateName]["id"] = $templateContent["id"];
        foreach ($templateContent["entries"] as $content) {
            if ($content["name"] === "template.html") {
                $templates[$templateName]["last_update"] = $this->dateTime
                    ->gmtDate(null, $content["server_modified"]);
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
        $templates[$templateName]["file"] = $templateContent["path_lower"];
        $templates[$templateName]["storage_id"] = $credentials["app_key"];
    }

    /**
     * @return array|RemoteTemplateInterface[]
     */
    public function listRemoteTemplates(): array
    {
        return $this->remoteTemplateCollectionFactory->create()->toArray();
    }
}
