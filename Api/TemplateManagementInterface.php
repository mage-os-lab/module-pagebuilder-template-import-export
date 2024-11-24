<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\PageBuilder\Api\Data\TemplateInterface;

interface TemplateManagementInterface
{
    /**
     * @param string $exportFile
     * @param string $exportPath
     * @param TemplateInterface $template
     * @param array $config
     * @return string
     */
    public function exportTemplateToArchive(
        string $exportFile,
        string $exportPath,
        TemplateInterface $template,
        array $config
    ): string;

    /**
     * @param string $importPath
     * @return int
     */
    public function importTemplateFromArchive(string $importPath): int;
}
