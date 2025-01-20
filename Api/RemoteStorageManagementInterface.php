<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;

interface RemoteStorageManagementInterface
{

    /**
     * @return void
     */
    public function updateRemoteTemplatesInformations(): void;

    /**
     * @return RemoteTemplateInterface[]
     */
    public function listRemoteTemplates(): array;
}
