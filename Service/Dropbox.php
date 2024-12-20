<?php

namespace MageOS\PageBuilderTemplateImportExport\Service;

use MageOS\PageBuilderTemplateImportExport\Api\DropboxInterface;
use Spatie\Dropbox\ClientFactory as DropboxFactory;
use Spatie\Dropbox\Client as DropboxClient;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @param string $appKey
     * @param string $appSecret
     * @param string $token
     * @return void
     */
    protected function initClient(string $appKey, string $appSecret, string $token = ""): void
    {
        if (!empty($token)) {
            $this->dropbox = $this->dropboxClient->create(
                ['accessTokenOrAppCredentials' => $token]
            );
        } else {
            $this->dropbox = $this->dropboxClient->create(
                ['accessTokenOrAppCredentials' => [$appKey, $appSecret]]
            );
        }
    }

    /**
     * @param string $filename
     * @param string $appKey
     * @param string $appSecret
     * @return void
     */
    public function upload(string $filename, string $appKey = "", string $appSecret = ""): void
    {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret);
        }

        $this->dropbox->upload(basename($filename), $this->file->read($filename), 'overwrite');
    }

    /**
     * @param string $filename
     * @param string $appKey
     * @param string $appSecret
     * @return void
     */
    public function download(string $filename, string $appKey = "", string $appSecret = ""): void
    {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret);
        }

        $stream = $this->dropbox->download(basename($filename));

        $this->file->write($filename . $filename, $stream, 'w');
    }

    /**
     * @param string $appKey
     * @param string $appSecret
     * @param string $appToken
     * @param string $path
     * @param bool $recursive
     * @return array
     */
    public function listTemplates(
        string $path = "",
        bool $recursive = false,
        string $appKey = "",
        string $appSecret = "",
        string $appToken = ""
    ): array {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $appToken);
        }

        return $this->dropbox->listFolder($path, $recursive);
    }

    /**
     * @param string $path
     * @param string $format
     * @param string $size
     * @param string $appKey
     * @param string $appSecret
     * @param string $appToken
     * @return string
     */
    public function getThumbnail(
        string $path,
        string $format = "jpeg",
        string $size = "w64h64",
        string $appKey = "",
        string $appSecret = "",
        string $appToken = ""
    ): string {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $appToken);
        }

        return $this->dropbox->getThumbnail($path, $format, $size);
    }
}
