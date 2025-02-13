<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Controller\Adminhtml\Template\Remote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox;
use Magento\Framework\Filesystem;
use MageOS\PageBuilderTemplateImportExport\Api\TemplateManagementInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteTemplateRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Helper\ModuleConfig;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Sync extends Action implements HttpPostActionInterface
{

    public const ADMIN_RESOURCE = 'MageOS_PageBuilderTemplateImportExport::pagebuilder_template_import';

    /**
     * @param LoggerInterface $logger
     * @param TemplateManagementInterface $templateManagement
     * @param Filesystem $filesystem
     * @param Context $context
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected TemplateManagementInterface $templateManagement,
        protected Filesystem $filesystem,
        protected RemoteTemplateRepositoryInterface $remoteTemplateRepository,
        protected Dropbox $dropbox,
        protected ModuleConfig $moduleConfig,
        protected RemoteAddress $remoteAddress,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Sync remote template data from webhook
     * @return mixed
     */
    public function execute(): mixed
    {
        $request = $this->getRequest();

        // Initial Dropbox webhook challenge management
        if ($request->getMethod() === "GET" && $request->getParam("challenge")) {
            $rawResult = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $rawResult->setContents($request->getParam("challenge"));
            $rawResult->setHeader("Content-Type", "text/plain");
            $rawResult->setHeader("X-Content-Type-Options", "nosniff");
            return $rawResult;
        }

        // Dropbox webhook verification
        $dropboxSignature = $request->getHeader("X-Dropbox-Signature");
        $appKey = $this->dropbox->verifyWebhook($dropboxSignature, $request->getContent());
        if ($appKey === false) {
            $address = $this->remoteAddress->getRemoteAddress();
            $host = $this->remoteAddress->getRemoteHost();
            $this->logger->error(
                __(
                    "Not valid webhook signature registered on webhook call from %1 with ip %2",
                    $address,
                    $host
                )
            );
            throw new UnauthorizedHttpException("Not Authorized");
        }

        //TODO Manage webhook

        $rawResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $rawResult->setData(["success" => true]);
        return $rawResult;
    }
}
