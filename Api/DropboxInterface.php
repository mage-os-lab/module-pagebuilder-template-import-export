<?php

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Exception\FileSystemException;

interface DropboxInterface
{
    /**
     * @param string $filename
     * @param string $appKey
     * @param string $appSecret
     * @return void
     */
    public function upload(string $filename, string $appKey = "", string $appSecret = ""): void;

    /**
     * @param string $filename
     * @param string $appKey
     * @param string $appSecret
     * @return void
     */
    public function download(string $filename, string $appKey = "", string $appSecret = ""): void;

    /**
     * @param string $path
     * @param bool $recursive
     * @param string $appKey
     * @param string $appSecret
     * @param string $appToken
     * @return array
     */
    public function listTemplates(
        string $path = "",
        bool $recursive = false,
        string $appKey = "",
        string $appSecret = "",
        string $appToken = ""
    ): array;

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
        string $format = "jpg",
        string $size = "w64h64",
        string $appKey = "",
        string $appSecret = "",
        string $appToken = ""
    ): string;
}
