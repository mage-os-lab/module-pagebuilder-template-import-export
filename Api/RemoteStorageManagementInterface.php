<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;

interface RemoteStorageManagementInterface
{

    /**
     * @param bool $fullSync
     * @param array $credentials
     * @return void
     */
    public function updateRemoteTemplatesInformations(bool $fullSync = false, array $credentials = []): void;

    /**
     * @param array $credentials
     * @return void
     */
    public function deleteRemoteTemplates(array $credentials): void;

    /**
     * @return RemoteTemplateInterface[]
     */
    public function listRemoteTemplates(): array;
}
