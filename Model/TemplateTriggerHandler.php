<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use Psr\Log\LoggerInterface;

class TemplateTriggerHandler
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected RemoteStorageManagementInterface $remoteStorageManagement,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @param string[] $credentials
     * @return void
     */
    public function process(array $credentials): void
    {
        try {
            $credentials = array_combine(
                ['app_key', 'app_secret', 'refresh_token'],
                array_values($credentials)
            );
            $this->remoteStorageManagement->updateRemoteTemplatesInformations(true, $credentials);
        } catch (\Exception $exception) {
            $this->logger->error(
                __(
                    "An error occurred synchronizing pagebuilder templates from remote %s",
                    $exception->getMessage()
                )
            );
        }
    }
}
