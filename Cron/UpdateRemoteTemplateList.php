<?php

namespace MageOS\PageBuilderTemplateImportExport\Cron;

use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use Psr\Log\LoggerInterface;

class UpdateRemoteTemplateList
{
    /**
     * @param RemoteStorageManagementInterface $remoteStorageManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected RemoteStorageManagementInterface $remoteStorageManagement,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Execute the cron job
     */
    public function execute()
    {
        try {
            $this->logger->info('Cron: Start remote templates information update.');
            $this->remoteStorageManagement->updateRemoteTemplatesInformations();
            $this->logger->info('Cron: Remote templates information updated successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Error updating remote templates information: ' . $e->getMessage());
        }
    }
}
