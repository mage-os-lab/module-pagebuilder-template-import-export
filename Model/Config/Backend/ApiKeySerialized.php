<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model\Config\Backend;

use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\ClientFactory as DropboxClientFactory;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\Client as DropboxClient;
use Magento\Framework\Serialize\Serializer\Json;

class ApiKeySerialized extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    public function __construct(
        protected DropboxClientFactory $dropboxClientFactory,
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
     * Unset array element with '__empty' key
     *
     * @return $this
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
            }
        }
        $this->setValue($values);
        return parent::beforeSave();
    }
}
