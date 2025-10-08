<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Ui\Component\Listing\Columns;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RemoteTemplateManagerActions extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private Escaper $escaper,
        private UrlInterface $urlBuilder,
        private AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $indexField = $this->getData('config/indexField') ?: 'entity_id';
                if (isset($item[$indexField])) {
                    $templateName = $this->escaper->escapeHtml($item['template_name']);
                    $item[$name]['import'] = [
                        'label' => __('Import'),
                        'href' => $this->urlBuilder->getUrl(
                            'pagebuildertemplateie/template_remote/import',
                            [
                                'entity_id' => $item[$indexField],
                            ]
                        ),
                        'confirm' => [
                            'title' => __('Import %1?', $templateName),
                            'message' => __(
                                'Are you sure you want to import template %1?
                                Children cms blocks and assets will be imported too.',
                                $templateName
                            ),
                            '__disableTmpl' => true,
                        ],
                        'post' => true,
                        '__disableTmpl' => true,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
