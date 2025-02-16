<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Controller\Template\Remote;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteStorageManagementInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteTemplateRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\TemplateManagementInterface;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteCursorRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Helper\ModuleConfig;
use MageOS\PageBuilderTemplateImportExport\Service\Dropbox;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Sync implements CsrfAwareActionInterface, HttpGetActionInterface, HttpPostActionInterface
{

    /**
     * @param LoggerInterface $logger
     * @param TemplateManagementInterface $templateManagement
     * @param Filesystem $filesystem
     * @param RemoteTemplateRepositoryInterface $remoteTemplateRepository
     * @param Dropbox $dropbox
     * @param ModuleConfig $moduleConfig
     * @param RemoteAddress $remoteAddress
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected TemplateManagementInterface $templateManagement,
        protected RemoteStorageManagementInterface $remoteStorageManagement,
        protected Filesystem $filesystem,
        protected RemoteTemplateRepositoryInterface $remoteTemplateRepository,
        protected RemoteCursorRepositoryInterface $remoteCursorRepository,
        protected Dropbox $dropbox,
        protected ModuleConfig $moduleConfig,
        protected RemoteAddress $remoteAddress,
        protected ResultFactory $resultFactory,
        protected RequestInterface $request
    ) {
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function execute(): mixed
    {
        // Initial Dropbox webhook challenge management
        if ($this->request->getMethod() === "GET" && $this->request->getParam("challenge")) {
            $rawResult = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $rawResult->setContents($this->request->getParam("challenge"));
            $rawResult->setHeader("Content-Type", "text/plain");
            $rawResult->setHeader("X-Content-Type-Options", "nosniff");
            return $rawResult;
        }

        // Dropbox webhook verification
        $dropboxSignature = $this->request->getHeader("X-Dropbox-Signature");
        $credentials = $this->dropbox->verifyWebhook($dropboxSignature, $this->request->getContent());
        if (!is_array($credentials)) {
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

        try {
            $this->remoteStorageManagement->updateRemoteTemplatesInformations(false, $credentials);
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    "An error occurred making remote dropbox storage sync through webhook for app %1: %2",
                    $credentials["app_key"],
                    $e->getMessage()
                ),
            );
        }
        $rawResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $rawResult->setData(["success" => true]);
        return $rawResult;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
