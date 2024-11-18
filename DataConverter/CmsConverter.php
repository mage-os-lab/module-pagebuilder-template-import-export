<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\DataConverter;

use Magento\Framework\Data\Wysiwyg\Normalizer;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\Filter\Template\Tokenizer\Parameter;
use Magento\Framework\Filter\Template\Tokenizer\ParameterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use MageOS\PageBuilderTemplateImportExport\Helper\Aliases as TemplateAliasHelper;

class CmsConverter extends SerializedToJson
{

    /**
     * @var array
     */
    private $assets = [];

    /**
     * @param Normalizer $normalizer
     * @param ParameterFactory $parameterFactory
     * @param Json $json
     * @param Serialize $serialize
     */
    public function __construct(
        private readonly Normalizer       $normalizer,
        private readonly ParameterFactory $parameterFactory,
        private readonly Json             $json,
        Serialize                         $serialize
    )
    {
        parent::__construct($serialize, $json);
    }

    /**
     * @param $value
     * @return array|string
     * @throws DataConversionException
     */
    public function convert($value)
    {
        //Convert and extract widgets media
        preg_match_all('/(.*?){{widget(.*?)}}/si', $value, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return $value;
        }
        $convertedValue = '';
        foreach ($matches as $match) {
            $convertedValue .= $match[1] . '{{widget' . $this->convertWidgetParams($match[2]) . '}}';
        }
        preg_match_all('/(.*?{{widget.*?}})*(?<ending>.*?)$/si', $value, $matchesTwo, PREG_SET_ORDER);
        if (isset($matchesTwo[0])) {
            $convertedValue .= $matchesTwo[0]['ending'];
        }

        //Convert and extract pageBuilder media
        preg_match_all('/(.*?){{media(.*?)}}/si', $value, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (isset($match[2])) {
                $url = explode("=", $match[2])[1];
                if (!in_array("/media/" . $url, $this->assets)) {
                    $this->assets[] = "/media/" . $url;
                }
            }
        }

        //TODO iterate CMS Block for transposition to html template or other html files generation?
        return ["value" => $convertedValue, "assets" => $this->assets];
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isValidJsonValue($value)
    {
        return parent::isValidJsonValue($this->normalizer->restoreReservedCharacters($value));
    }

    /**
     * @param $paramsString
     * @return string
     * @throws DataConversionException
     */
    private function convertWidgetParams($paramsString)
    {
        /** @var Parameter $tokenizer */
        $tokenizer = $this->parameterFactory->create();
        $tokenizer->setString($paramsString);
        $widgetParameters = $tokenizer->tokenize();
        if (isset($widgetParameters['conditions_encoded'])) {
            if ($this->isValidJsonValue($widgetParameters['conditions_encoded'])) {
                $widgetConditionsEncoded = $this->json->unserialize($this->normalizer->restoreReservedCharacters($widgetParameters['conditions_encoded']));
                foreach ($widgetConditionsEncoded as &$item) {
                    foreach ($item as $label => $value) {
                        if (filter_var($value, FILTER_VALIDATE_URL) && $url = parse_url($value)) {
                            $item[$label] = str_replace($url["scheme"] . "://" . $url["host"], TemplateAliasHelper::CMS_WIDGET_URL_PLACEHOLDER, $value);
                            if (!in_array($url["path"], $this->assets)) {
                                $this->assets[] = $url["path"];
                            }
                        }
                    }
                }
                $widgetParameters['conditions_encoded'] = $this->json->serialize($widgetConditionsEncoded);
            }
            $widgetParameters['conditions_encoded'] = $this->normalizer->replaceReservedCharacters(
                parent::convert($widgetParameters['conditions_encoded'])
            );
            $newParamsString = '';
            foreach ($widgetParameters as $key => $parameter) {
                $newParamsString .= ' ' . $key . '="' . $parameter . '"';
            }
            return $newParamsString;
        } else {
            return $paramsString;
        }
    }
}
