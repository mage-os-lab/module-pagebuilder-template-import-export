<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api\Data;

interface RemoteCursorInterface
{
    const ENTITY_ID = 'entity_id';
    const STORAGE_ID = 'storage_id';
    const LATEST_CURSOR = 'latest_cursor';

    /**
     * @return string
     */
    public function getStorageId(): string;

    /**
     * @param string $storageId
     * @return RemoteCursorInterface
     */
    public function setStorageId(string $storageId): RemoteCursorInterface;

    /**
     * @return string
     */
    public function getLatestCursor(): string;

    /**
     * @param string $lastestCursor
     * @return RemoteCursorInterface
     */
    public function setLatestCursor(string $lastestCursor): RemoteCursorInterface;

}
