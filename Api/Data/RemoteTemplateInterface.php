<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Api\Data;

interface RemoteTemplateInterface
{
    const ENTITY_ID = 'entity_id';
    const TEMPLATE_ID = 'template_id';
    const REMOTE_STORAGE_ID = 'remote_storage_id';
    const TEMPLATE_NAME = 'template_name';
    const FILE_PATH = 'file_path';
    const PREVIEW = 'preview';
    const LAST_UPDATE = 'last_update';

    /**
     * @return string
     */
    public function getTemplateId(): string;

    /**
     * @param string $templateId
     * @return RemoteTemplateInterface
     */
    public function setTemplateId(string $templateId): RemoteTemplateInterface;

    /**
     * @return string
     */
    public function getRemoteStorageId(): string;

    /**
     * @param string $remoteStorageId
     * @return RemoteTemplateInterface
     */
    public function setRemoteStorageId(string $remoteStorageId): RemoteTemplateInterface;

    /**
     * @return string
     */
    public function getTemplateName(): string;

    /**
     * @param string $templateName
     * @return RemoteTemplateInterface
     */
    public function setTemplateName(string $templateName): RemoteTemplateInterface;

    /**
     * @return string
     */
    public function getFilePath(): string;

    /**
     * @param string $filePath
     * @return RemoteTemplateInterface
     */
    public function setFilePath(string $filePath): RemoteTemplateInterface;

    /**
     * @return string
     */
    public function getPreview(): string;

    /**
     * @param string $preview
     * @return RemoteTemplateInterface
     */
    public function setPreview(string $preview): RemoteTemplateInterface;

}
