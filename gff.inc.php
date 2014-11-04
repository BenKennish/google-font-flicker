<?php

include_once 'config.inc.php';

class GoogleFontFlicker
{
    public $fontList;
    public $fontFamilyOptionsHTML = '';
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = GFF_GOOGLE_API_KEY;
    }

    public function generateFontList($sort = '')
    {
        $params = array();
        if (!empty($sort))
        {
            switch($sort)
            {
                case 'alpha': // Sort the list alphabetically
                case 'date': // Sort the list by date added (most recent font added or updated first)
                case 'popularity': // Sort the list by popularity (most popular family first)
                case 'style': // Sort the list by number of styles available (family with most styles first)
                case 'trending': // Sort the list by families seeing growth in usage (family seeing the most growth first)
                    $params['sort'] = $sort;
                    break;
                default:
                    throw new InvalidArgumentException('Unexpected value for $sort: '.$sort);
            }
        }

        $ret = $this->_makeAPICall('https://www.googleapis.com/webfonts/v1/webfonts', $params);
        if (empty($ret->kind) || $ret->kind != 'webfonts#webfontList')
            throw new RuntimeException('Unexpected $ret->kind value or not present');

        if (!isset($ret->items))
            throw new RuntimeException('$ret->items not present');
        if (!is_array($ret->items))
            throw new RuntimeException('$ret->items is not an array');

        $this->fontList = $ret->items;


        // TODO: make drop down appear with the text in that font family?

        $this->htmlFontFamilyOptions = '    <option></option>'.PHP_EOL;

        foreach($this->fontList as $font)
        {
            $this->htmlFontFamilyOptions .= '    <option>'.htmlspecialchars($font->family, ENT_QUOTES, 'UTF-8').'</option>'.PHP_EOL;
        }
        reset($this->fontList); // start from top of array again

    }

    public function genHTMLFontChooser($selector, $description)
    {

        $randomColour = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

        $ret = '<div style="display: inline-block; padding: 10px; border: 1px dashed black; background-color: '.$randomColour.';">'.PHP_EOL;
        $ret .= '    <p><b>'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'</b></p>'.PHP_EOL;
        // TODO: make drop down appear with the text in that font family!
        $ret .= '    <label>Font Family: <select data-gff-selector="'.htmlspecialchars($selector, ENT_QUOTES, 'UTF-8').'">'.PHP_EOL;
        $ret .= $this->htmlFontFamilyOptions;
        $ret .= '    </select></label>'.PHP_EOL;
        $ret .= '</div>'.PHP_EOL;

        return $ret;
    }

    // -------------------------------------------------------------

    private function _makeAPICall($url, $params)
    {
        if (!is_string($url)) throw new InvalidArgumentException('$url should be a string');
        if (!is_array($params)) throw new InvalidArgumentException('$params should be an array');

        // add our API key
        $params['key'] = $this->apiKey;
        if (empty($params['key'])) throw new RuntimeException('Missing API key');

        $query = '?'.http_build_query($params);
        //error_log('GoogleFontFlicker requesting: '.$url.$query);
        $ret = file_get_contents($url.$query);

        if ($ret === false)
            throw new RuntimeException('Failed to retrieve '.$url.$query);

        $json = json_decode($ret, false);
        if ($json === null)
            throw new RuntimeException('Failed to parse JSON from '.$url.$query.' : "'.$ret.'"');

        //var_dump($json);
        return $json;
    }

}
