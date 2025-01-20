<?php

namespace MageOS\PageBuilderTemplateImportExport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Helper\Context;

class ModuleConfig extends AbstractHelper
{
    const SECTION = 'pagebuilder_template_importexport/';
    const GENERAL_GROUP = self::SECTION . 'general/';
    const ENABLE = self::GENERAL_GROUP . 'enable';
    const DROPBOX_CREDENTIALS = self::GENERAL_GROUP . 'dropbox_credentials';

    /**
     * @param SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        protected SerializerInterface $serializer,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::ENABLE);
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getDropboxCredentials()
    {
        $dropboxCredentials = $this->scopeConfig->getValue(self::DROPBOX_CREDENTIALS);

        if (!is_array($dropboxCredentials)) {
            $dropboxCredentials = $this->serializer->unserialize($dropboxCredentials);
        }

        return $dropboxCredentials;
    }
}
