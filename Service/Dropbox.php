<?php

namespace MageOS\PageBuilderTemplateImportExport\Service;

use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use MageOS\PageBuilderTemplateImportExport\Api\DropboxInterface;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\ClientFactory as DropboxFactory;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\Client as DropboxClient;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class Dropbox implements DropboxInterface
{

    const DROPBOX_AUTH_CODE_CACHE_KEY_PREFIX = 'dropbox_auth_';
    const DROPBOX_AUTH_CODE_LIFETIME = 1800;

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
        protected DirectoryList $directoryList,
        protected ConfigCache $cache,
    ) {
    }

    /**
     * @param $appKey
     * @param $appSecret
     * @param string $refreshToken
     * @return void
     */
    protected function initClient($appKey, $appSecret, string $refreshToken = ""): void
    {
        if (!empty($refreshToken)) {
            try {
                $cachedAuthCode = $this->cache->load(
                    self::DROPBOX_AUTH_CODE_CACHE_KEY_PREFIX . $refreshToken
                );
            } catch (\Exception $e) {
                $cachedAuthCode = false;
            }
            if (!$cachedAuthCode) {
                $dropbox = $this->dropboxClient->create();
                $authTokenRequest = $dropbox->apiEndpointRequest('oauth2/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $appKey,
                    'client_secret' => $appSecret
                ]);
                $authCode = $authTokenRequest["access_token"];
                $this->cache->save(
                    $authCode,
                    self::DROPBOX_AUTH_CODE_CACHE_KEY_PREFIX . $refreshToken,
                    [],
                    self::DROPBOX_AUTH_CODE_LIFETIME
                );
            } else {
                $authCode = $cachedAuthCode;
            }
            $this->dropbox = $this->dropboxClient->create(
                ['accessTokenOrAppCredentials' => $authCode]
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
     * @param string $path
     * @param bool $recursive
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return array
     */
    public function listFolder(
        string $path = "",
        bool $recursive = false,
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ): array {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        return $this->dropbox->listFolder($path, $recursive);
    }

    /**
     * @param string $path
     * @param string $format
     * @param string $size
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return string
     */
    public function getThumbnail(
        string $path,
        string $format = "jpeg",
        string $size = "w64h64",
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ):string {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        return $this->dropbox->getThumbnail($path, $format, $size);
    }
}
