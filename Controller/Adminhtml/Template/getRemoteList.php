<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use Psr\Log\LoggerInterface;

class getRemoteList extends Action implements HttpPostActionInterface
{

    public const ADMIN_RESOURCE = 'MageOS_PageBuilderTemplateImportExport::pagebuilder_template_import';

    /**
     * @param LoggerInterface $logger
     * @param RemoteStorageManagementInterface $remoteStorageManagement
     * @param Context $context
     */
    public function __construct(
        private LoggerInterface $logger,
        private RemoteStorageManagementInterface $remoteStorageManagement,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Import template
     *
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $templates = $this->remoteStorageManagement->listRemoteTemplates();
            $result = [
                'success' => true,
                'templates' => $templates
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
            $this->logger->error($e);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
