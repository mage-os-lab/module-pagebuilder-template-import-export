<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\PageBuilder\Api\Data\TemplateInterface;

interface TemplateManagementInterface
{
    /**
     * Export template
     *
     * @param string $exportFile
     * @param string $exportPath
     * @param TemplateInterface $template
     * @param array $config
     * @return string
     */
    public function exportTemplate(
        string $exportFile,
        string $exportPath,
        TemplateInterface $template,
        array $config
    ): string;

    /**
     * Import template from archive
     *
     * @param string $importPath
     * @return int
     */
    public function importTemplateFromArchive(string $importPath): int;
}
