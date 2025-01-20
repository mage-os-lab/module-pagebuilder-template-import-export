<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageOS\PageBuilderTemplateImportExport\Api\Data\RemoteTemplateInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface RemoteTemplateRepositoryInterface
{
    /**
     * @param RemoteTemplateInterface $template
     * @return RemoteTemplateInterface
     * @throws CouldNotSaveException
     */
    public function save(RemoteTemplateInterface $template): RemoteTemplateInterface;

    /**
     * @param int $id
     * @return RemoteTemplateInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): RemoteTemplateInterface;

    /**
     * @param string $templateId
     * @param string $remoteStorageId
     * @return RemoteTemplateInterface
     * @throws NoSuchEntityException
     */
    public function getByTemplateAndStorageId(string $templateId, string $remoteStorageId): RemoteTemplateInterface;

    /**
     * @param RemoteTemplateInterface $template
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemoteTemplateInterface $template): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
