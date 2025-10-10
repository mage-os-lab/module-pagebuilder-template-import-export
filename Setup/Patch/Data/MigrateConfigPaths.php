<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MigrateConfigPaths
 * @package MageOS\AutomaticTranslation\Setup\Patch\Data
 */
class MigrateConfigPaths implements DataPatchInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;
    /**
     * @var WriterInterface
     */
    protected WriterInterface $configWriter;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $mappings = [
            'pagebuilder_template_importexport/general/enable' => 'cms/pagebuilder_template_importexport/enable',
            'pagebuilder_template_importexport/general/sync_templates_by_cron' => 'cms/pagebuilder_template_importexport/sync_templates_by_cron',
            'pagebuilder_template_importexport/general/dropbox_credentials' => 'cms/pagebuilder_template_importexport/dropbox_credentials'
        ];

        // Default scope
        foreach ($mappings as $oldPath => $newPath) {
            $this->migratePath($oldPath, $newPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        }
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    protected function migratePath(string $oldPath, string $newPath, string $scope, int $scopeId): void
    {
        $oldValue = $this->scopeConfig->getValue($oldPath, $scope, $scopeId);
        $newValue = $this->scopeConfig->getValue($newPath, $scope, $scopeId);

        if ($oldValue !== null && $newValue === null) {
            $this->configWriter->save($newPath, $oldValue, $scope, $scopeId);
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
