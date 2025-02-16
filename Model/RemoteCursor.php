<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\Model\AbstractModel;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteCursorInterface;

class RemoteCursor extends AbstractModel implements RemoteCursorInterface
{
    protected function _construct(): void
    {
        $this->_init(
            \MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteCursor::class
        );
    }

    /**
     * @return string
     */
    public function getStorageId(): string
    {
        return $this->getData(self::STORAGE_ID);
    }

    /**
     * @param string $storageId
     * @return RemoteCursor
     */
    public function setStorageId(string $storageId): RemoteCursor
    {
        return $this->setData(self::STORAGE_ID, $storageId);
    }

    /**
     * @return string
     */
    public function getLatestCursor(): string
    {
        return $this->getData(self::LATEST_CURSOR);
    }

    /**
     * @param string $lastestCursor
     * @return RemoteCursor
     */
    public function setLatestCursor(string $lastestCursor): RemoteCursor
    {
        return $this->setData(self::LATEST_CURSOR, $lastestCursor);
    }
}
