<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteTemplateRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterfaceFactory;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate as ResourceModel;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteTemplate\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;

class RemoteTemplateRepository implements RemoteTemplateRepositoryInterface
{

    /**
     * @param ResourceModel $resource
     * @param RemoteTemplateInterfaceFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        protected readonly ResourceModel $resource,
        protected readonly RemoteTemplateInterfaceFactory $modelFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly SearchResultsInterfaceFactory $searchResultsFactory,
        protected readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        protected readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @param RemoteTemplateInterface $template
     * @return RemoteTemplateInterface
     * @throws CouldNotSaveException
     */
    public function save(RemoteTemplateInterface $template): RemoteTemplateInterface
    {
        try {
            $this->resource->save($template);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Unable to save template: %1', $e->getMessage())
            );
        }
        return $template;
    }

    /**
     * @param string $templateId
     * @param string $remoteStorageId
     * @return RemoteTemplateInterface
     * @throws NoSuchEntityException
     */
    public function getByTemplateAndStorageId(string $templateId, string $remoteStorageId): RemoteTemplateInterface
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('template_id', $templateId)
            ->addFilter('remote_storage_id', $remoteStorageId)
            ->create();

        $templateList = $this->getList($searchCriteria);
        if ($templateList->getTotalCount() === 0) {
            throw new NoSuchEntityException(
                __('Entity with template ID "%1" and storage ID "%2" not found.', $templateId, $remoteStorageId)
            );
        }
        $templateListResult = $templateList->getItems();
        return reset($templateListResult);
    }

    /**
     * @param int $id
     * @return RemoteTemplateInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): RemoteTemplateInterface
    {
        $template = $this->modelFactory->create();
        $this->resource->load($template, $id);
        if (!$template->getId()) {
            throw new NoSuchEntityException(
                __('Entity with ID "%1" not found.', $id)
            );
        }
        return $template;
    }

    /**
     * @param RemoteTemplateInterface $template
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemoteTemplateInterface $template): bool
    {
        try {
            $this->resource->delete($template);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to delete template: %1', $e->getMessage())
            );
        }
        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
