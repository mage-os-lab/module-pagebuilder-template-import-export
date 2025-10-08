<?php

namespace MageOS\PageBuilderTemplateImportExport\Service;

use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use MageOS\PageBuilderTemplateImportExport\Api\DropboxInterface;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\ClientFactory as DropboxFactory;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox\Client as DropboxClient;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use MageOS\PageBuilderTemplateImportExport\Helper\ModuleConfig;

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
     * @param ConfigCache $cache
     * @param ModuleConfig $config
     */
    public function __construct(
        protected DropboxFactory $dropboxClient,
        protected File $file,
        protected DirectoryList $directoryList,
        protected ConfigCache $cache,
        protected ModuleConfig $config
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
     * @param string $refreshToken
     * @return void
     */
    public function upload(
        string $filename,
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ): void {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }

        $this->dropbox->upload(basename($filename), $this->file->read($filename), 'overwrite');
    }

    /**
     * @param string $filename
     * @param string $destination
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return void
     */
    public function download(
        string $filename,
        string $destination,
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ): void {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        $stream = $this->dropbox->download(basename($filename));
        $this->file->write($destination, $stream);
    }

    /**
     * @param string $path
     * @param string $destination
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return void
     */
    public function downloadZip(
        string $path,
        string $destination,
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ): void {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        $stream = $this->dropbox->downloadZip($path);
        $this->file->write($destination, $stream);
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
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return array
     */
    public function getMetadata(
        string $path = "",
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ): array {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        return $this->dropbox->getMetadata($path);
    }

    /**
     * @param string $cursor
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return array
     */
    public function listFolderContinue(
        string $cursor = "",
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ): array {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        return $this->dropbox->listFolderContinue($cursor);
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

    /**
     * @param array $options
     * @param string $appKey
     * @param string $appSecret
     * @param string $refreshToken
     * @return array
     * @throws \Exception
     */
    public function getLatestCursor(
        array $options = [],
        string $appKey = "",
        string $appSecret = "",
        string $refreshToken = ""
    ):array {
        if (empty($this->dropbox) || $appKey !== "") {
            $this->initClient($appKey, $appSecret, $refreshToken);
        }
        $parameters = [
            "include_deleted" => $options["include_deleted"] ?? false,
            "include_has_explicit_shared_members" => $options["include_has_explicit_shared_members"] ?? false,
            "include_media_info" => $options["include_media_info"] ?? false,
            "include_mounted_folders" => $options["include_mounted_folders"] ?? false,
            "include_non_downloadable_files" => $options["include_non_downloadable_files"] ?? false,
            "path" => $options["path"] ?? "",
            "recursive" => $options["recursive"] ?? false
        ];
        return $this->dropbox->rpcEndpointRequest('files/list_folder/get_latest_cursor', $parameters);
    }

    /**
     * @param string $signature
     * @param string $requestBody
     * @return mixed
     */
    public function verifyWebhook(string $signature, string $requestBody): mixed
    {
        $requestBody = file_get_contents("php://input");
        foreach ($this->config->getDropboxCredentials() as $credential) {
            $computedSignature = hash_hmac('sha256', $requestBody, $credential["app_secret"]);
            if (hash_equals($computedSignature, $signature)) {
                return $credential;
            }
        }
        return false;
    }
}
