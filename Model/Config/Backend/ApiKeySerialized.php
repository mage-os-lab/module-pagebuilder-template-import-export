<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model\Config\Backend;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\ClientFactory as DropboxClientFactory;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox as DropboxService;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteCursorInterfaceFactory;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteCursorRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;

class ApiKeySerialized extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    public function __construct(
        protected DropboxClientFactory $dropboxClientFactory,
        protected DropboxService $dropboxService,
        protected RemoteCursorRepositoryInterface $remoteCursorRepository,
        protected RemoteCursorInterfaceFactory $remoteCursorInterfaceFactory,
        protected RemoteStorageManagementInterface $remoteStorageManagement,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    /**
     * @return ApiKeySerialized
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function beforeSave()
    {
        $values = $this->getValue();
        if (is_array($values)) {
            unset($values['__empty']);
        }
        foreach ($values as $key => $row) {
            if (isset($row["access_code"]) && $row["access_code"] !== "") {
                $dropbox = $this->dropboxClientFactory->create(
                    ['accessTokenOrAppCredentials' => [$row["app_key"], $row["app_secret"]]]
                );
                $authData = $dropbox->apiEndpointRequest('oauth2/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $row['access_code'],
                ]);
                unset($values[$key]['access_code']);
                $values[$key]['refresh_token'] = $authData['refresh_token'];

                try {
                    $remoteCursor = $this->remoteCursorRepository->getByStorageId($row["app_key"]);
                } catch (NoSuchEntityException $e) {
                    $remoteCursor = $this->remoteCursorInterfaceFactory->create();
                    $remoteCursor->setData("storage_id", $row["app_key"]);
                }
                $latestCursor = $this->dropboxService->getLatestCursor(
                    [],
                    $row["app_key"],
                    $row["app_secret"],
                    $authData['refresh_token']
                );
                if (isset($latestCursor["cursor"])) {
                    $remoteCursor->setData(
                        "latest_cursor",
                        $latestCursor["cursor"]
                    );
                }
                $this->remoteCursorRepository->save($remoteCursor);
                $this->remoteStorageManagement->updateRemoteTemplatesInformations(true, $values[$key]);
            }
        }
        $this->setValue($values);
        return parent::beforeSave();
    }
}
