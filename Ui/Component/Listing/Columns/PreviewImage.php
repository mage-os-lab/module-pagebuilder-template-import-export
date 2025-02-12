<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class PreviewImage extends Column
{
    const NAME = 'preview';

    const ALT_FIELD = 'template_name';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $previewImage = $item[$fieldName];
                $imageSrc = 'data:image/jpg;base64, ' . $previewImage;
                $item[$fieldName . '_src'] = $imageSrc;
                $item[$fieldName . '_alt'] = $this->getAlt($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    private function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}
