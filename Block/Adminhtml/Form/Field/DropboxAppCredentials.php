<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class DropboxAppCredentials extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('app_key', ['label' => __('App Key'), 'class' => 'required-entry']);
        $this->addColumn('app_secret', ['label' => __('App Secret'), 'class' => 'required-entry']);
        $this->addColumn('app_token', ['label' => __('App Token'), 'class' => 'required-entry']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Credentials');
    }
    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $row->setData('option_extra_attrs', $options);
    }
}