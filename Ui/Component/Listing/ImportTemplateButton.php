<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Ui\Component\Listing;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use MageOS\PageBuilderTemplateImportExport\Helper\ModuleConfig;

class ImportTemplateButton implements ButtonProviderInterface
{

    private const ACL_PAGEBUILDER_IMPORT_TEMPLATES =
        'MageOS_PageBuilderTemplateImportExport::pagebuilder_template_import';

    /**
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        protected AuthorizationInterface $authorization,
        protected ModuleConfig $moduleConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        if (!$this->isAllowed() || !$this->moduleConfig->isEnabled()) {
            return [];
        }

        return [
            'label' => __('Import Template'),
            'sort_order' => '100',
            'class' => 'primary',
            'on_click' => 'jQuery(".mage-os-pagebuilder-template-import-modal").trigger("openModal");'
        ];
    }

    /**
     * Verify if page builder import button allowed
     */
    private function isAllowed(): bool
    {
        return $this->authorization->isAllowed(self::ACL_PAGEBUILDER_IMPORT_TEMPLATES);
    }
}
