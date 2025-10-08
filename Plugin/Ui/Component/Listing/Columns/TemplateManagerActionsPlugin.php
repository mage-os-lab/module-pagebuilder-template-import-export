<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Plugin\Ui\Component\Listing\Columns;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\PageBuilder\Ui\Component\Listing\Columns\TemplateManagerActions;
use MageOS\PageBuilderTemplateImportExport\Helper\ModuleConfig;

class TemplateManagerActionsPlugin
{

    /**
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        protected UrlInterface $urlBuilder,
        protected AuthorizationInterface $authorization,
        protected ModuleConfig $moduleConfig
    ) {
    }

    /**
     * @param TemplateManagerActions $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareDataSource(
        TemplateManagerActions $subject,
        array $result
    ) {
        if ($this->moduleConfig->isEnabled()) {
            if (isset($result['data']['items'])) {
                foreach ($result['data']['items'] as &$item) {
                    $name = $subject->getData('name');
                    $indexField = $subject->getData('config/indexField') ?: 'template_id';

                    if (isset($item[$indexField])) {
                        if ($this->authorization->isAllowed(
                            'Magento_Cms::config_cms'
                        )) {
                            $item[$name]['export'] = [
                                'label' => __('Export'),
                                'href' => $this->urlBuilder->getUrl(
                                    'pagebuildertemplateie/template/export',
                                    [
                                        'template_id' => $item[$indexField],
                                    ]
                                )
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }
}
