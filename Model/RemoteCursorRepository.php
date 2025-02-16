<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageOS\PageBuilderTemplateImportExport\Api\RemoteCursorRepositoryInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteCursorInterface;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteCursorInterfaceFactory;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteCursor as ResourceModel;
use MageOS\PageBuilderTemplateImportExport\Model\ResourceModel\RemoteCursor\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;

class RemoteCursorRepository implements RemoteCursorRepositoryInterface
{

    /**
     * @param ResourceModel $resource
     * @param RemoteCursorInterfaceFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        protected ResourceModel $resource,
        protected RemoteCursorInterfaceFactory $modelFactory,
        protected CollectionFactory $collectionFactory,
        protected SearchResultsInterfaceFactory $searchResultsFactory,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        protected CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @param RemoteCursorInterface $cursor
     * @return RemoteCursorInterface
     * @throws CouldNotSaveException
     */
    public function save(RemoteCursorInterface $cursor): RemoteCursorInterface
    {
        try {
            $this->resource->save($cursor);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Unable to save storage cursor: %1', $e->getMessage())
            );
        }
        return $cursor;
    }

    /**
     * @param string $storageId
     * @return RemoteCursorInterface
     * @throws NoSuchEntityException
     */
    public function getByStorageId(string $storageId): RemoteCursorInterface
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('storage_id', $storageId)
            ->create();

        $cursorList = $this->getList($searchCriteria);
        if ($cursorList->getTotalCount() === 0) {
            throw new NoSuchEntityException(
                __('Entity with storage ID "%2" not found.', $storageId)
            );
        }
        $cursorListResult = $cursorList->getItems();
        return reset($cursorListResult);
    }

    /**
     * @param RemoteCursorInterface $cursor
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemoteCursorInterface $cursor): bool
    {
        try {
            $this->resource->delete($cursor);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to delete storage cursor: %1', $e->getMessage())
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
