<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Plugin\Ui\Component\Listing\Columns;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\PageBuilder\Ui\Component\Listing\Columns\TemplateManagerActions;

class TemplateManagerActionsPlugin
{

    /**
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        private UrlInterface $urlBuilder,
        private AuthorizationInterface $authorization
    ) {
    }

    /**
     * Add export action to data source
     *
     * @param TemplateManagerActions $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareDataSource(
        TemplateManagerActions $subject,
        array $result
    ) {
        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as &$item) {
                $name = $subject->getData('name');
                $indexField = $subject->getData('config/indexField') ?: 'template_id';

                if (isset($item[$indexField])) {
                    if ($this->authorization->isAllowed(
                        'MageOS_PageBuilderTemplateImportExport::pagebuilder_template_export'
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

        return $result;
    }
}
