<?php

namespace MageOS\PageBuilderTemplateImportExport\Service;

use MageOS\PageBuilderTemplateImportExport\Api\DropboxInterface;
use Spatie\Dropbox\ClientFactory as DropboxFactory;
use Spatie\Dropbox\Client as DropboxClient;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

class Dropbox implements DropboxInterface
{
    /**
     * @var DropboxClient|null
     */
    protected ?DropboxClient $dropbox = null;

    /**
     * @param DropboxFactory $dropboxClient
     * @param File $file
     * @param DirectoryList $directoryList
     */
    public function __construct(
        protected DropboxFactory $dropboxClient,
        protected File $file,
        protected DirectoryList $directoryList
    ) {
    }

    /**
     * @return void
     */
    protected function initClient(): void
    {
        $this->dropbox = $this->dropboxClient->create(['accessTokenOrAppCredentials' => ['dbvqnssrg3xb4bf', 'evxnuuscs182fgw']]);
    }

    /**
     * @param $filename
     * @return void
     */
    public function upload($filename): void
    {
        if (empty($this->dropbox)) {
            $this->initClient();
        }

        $this->dropbox->upload(basename($filename), $this->file->read($filename), 'overwrite');
    }

    /**
     * @param $filename
     * @return void
     * @throws FileSystemException
     */
    public function download($filename): void
    {
        if (empty($this->dropbox)) {
            $this->initClient();
        }

        $stream = $this->dropbox->download(basename($filename));

        $this->file->write($filename . $filename, $stream, 'w');
    }

    /**
     * @return array
     */
    public function listTemplates(): array
    {
        if (empty($this->dropbox)) {
            $this->initClient();
        }

        return $this->dropbox->listFolder();
    }
}
