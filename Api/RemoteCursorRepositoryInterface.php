<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteCursorInterface;

interface RemoteCursorRepositoryInterface
{
    /**
     * @param RemoteCursorInterface $template
     * @return RemoteCursorInterface
     * @throws CouldNotSaveException
     */
    public function save(RemoteCursorInterface $template): RemoteCursorInterface;

    /**
     * @param string $storageId
     * @return RemoteCursorInterface
     * @throws NoSuchEntityException
     */
    public function getByStorageId(string $storageId): RemoteCursorInterface;

    /**
     * @param RemoteCursorInterface $cursor
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemoteCursorInterface $cursor): bool;
}
