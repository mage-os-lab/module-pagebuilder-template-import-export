<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RemoteTemplate extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('remote_pagebuilder_template', 'entity_id');
    }
}
