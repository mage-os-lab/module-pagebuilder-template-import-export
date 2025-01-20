<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageOS\PageBuilderTemplateImportExport\Model\RemoteTemplate;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(RemoteTemplate::class, ResourceModel::class);
    }
}
