<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Aliases extends AbstractHelper
{
    const TEMPLATE_FILE = "template.html";
    const CONFIG_FILE = "config.xml";
    const PREVIEW_FILE = "preview.jpg";
    const ASSETS_FOLDER_NAME = "assets";
    const CHILDREN_FOLDER_NAME = "children";
    const DEFAULT_TEMPLATE_ARCHIVE_FILENAME = "template.zip";
    const CMS_WIDGET_URL_PLACEHOLDER = "__cms-widget-site-url__";
    const CHILD_NAME_PARAM_SEPARATOR = "___";
}
