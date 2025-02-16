<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteCursor;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageOS\PageBuilderTemplateImportExport\Model\RemoteCursor;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteCursor as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(RemoteCursor::class, ResourceModel::class);
    }
}
