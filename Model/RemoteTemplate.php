<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\Model\AbstractModel;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;

class RemoteTemplate extends AbstractModel implements RemoteTemplateInterface
{
    protected function _construct(): void
    {
        $this->_init(
            \MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate::class
        );
    }

    /**
     * @return string
     */
    public function getTemplateId(): string
    {
        return $this->getData(self::TEMPLATE_ID);
    }

    /**
     * @param string $templateId
     * @return RemoteTemplate
     */
    public function setTemplateId(string $templateId): RemoteTemplate
    {
        return $this->setData(self::TEMPLATE_ID, $templateId);
    }

    /**
     * @return string
     */
    public function getRemoteStorageId(): string
    {
        return $this->getData(self::REMOTE_STORAGE_ID);
    }

    /**
     * @param string $remoteStorageId
     * @return RemoteTemplate
     */
    public function setRemoteStorageId(string $remoteStorageId): RemoteTemplate
    {
        return $this->setData(self::REMOTE_STORAGE_ID, $remoteStorageId);
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->getData(self::TEMPLATE_NAME);
    }

    /**
     * @param string $templateName
     * @return RemoteTemplate
     */
    public function setTemplateName(string $templateName): RemoteTemplate
    {
        return $this->setData(self::TEMPLATE_NAME, $templateName);
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->getData(self::FILE_PATH);
    }

    /**
     * @param string $filePath
     * @return RemoteTemplate
     */
    public function setFilePath(string $filePath): RemoteTemplate
    {
        return $this->setData(self::FILE_PATH, $filePath);
    }

    /**
     * @return string
     */
    public function getPreview(): string
    {
        return $this->getData(self::PREVIEW);
    }

    /**
     * @param string $preview
     * @return RemoteTemplate
     */
    public function setPreview(string $preview): RemoteTemplate
    {
        return $this->setData(self::PREVIEW, $preview);
    }

    /**
     * @return string
     */
    public function getLastUpdate(): string
    {
        return $this->getData(self::LAST_UPDATE);
    }

    /**
     * @param string $lastUpdate
     * @return RemoteTemplate
     */
    public function setLastUpdate(string $lastUpdate): RemoteTemplate
    {
        return $this->setData(self::LAST_UPDATE, $lastUpdate);
    }
}
