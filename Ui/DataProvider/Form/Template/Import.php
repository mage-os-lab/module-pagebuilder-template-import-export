<?php

namespace MageOS\PageBuilderTemplateImportExport\Ui\DataProvider\Form\Template;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class Import extends DataProvider
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }
}
