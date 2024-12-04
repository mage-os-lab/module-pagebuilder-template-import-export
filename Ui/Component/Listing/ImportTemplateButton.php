<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Ui\Component\Listing;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ImportTemplateButton implements ButtonProviderInterface
{

    private const ACL_PAGEBUILDER_IMPORT_TEMPLATES = 'MageOS_PageBuilderTemplateImportExport::pagebuilder_template_import';

    /**
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        private readonly AuthorizationInterface $authorization
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        if (!$this->isAllowed()) {
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
