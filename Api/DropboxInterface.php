<?php

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Exception\FileSystemException;

interface DropboxInterface
{
    /**
     * @param $filename
     * @return void
     */
    public function upload($filename): void;

    /**
     * @param $filename
     * @return void
     * @throws FileSystemException
     */
    public function download($filename): void;

    /**
     * @return array
     */
    public function listTemplates(): array;
}
