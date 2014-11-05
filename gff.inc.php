<?php
// Google Font Flicker
//
// Ben Kennish
// Nov 2014

class GoogleFontFlicker
{
    public $fontList;
    public $fontFamilyOptionsHTML = '';
    private $apiKey;

    // -----------------------------------------------------------------------
    public function __construct($apiKey)
    {
        if (empty($apiKey))
            throw new InvalidArgumentException('$apiKey cannot be blank');

        $this->apiKey = $apiKey;

    }

    // -----------------------------------------------------------------------
    public function generateFontList($sort = 'alpha', $filter = null)
    {
        $params = array();
        if (!empty($sort))
        {
            switch($sort)
            {
                case 'alpha':       // Sort the list alphabetically
                case 'date':        // Sort the list by date added (most recent font added or updated first)
                case 'popularity':  // Sort the list by popularity (most popular family first)
                case 'style':       // Sort the list by number of styles available (family with most styles first)
                case 'trending':    // Sort the list by families seeing growth in usage (family seeing the most growth first)
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


        //echo '<pre>'; var_dump($ret->items); echo '</pre>'; exit;

        // option to filter by category at least
        // serif, sans-serif, display, handwriting, monospace
        // then later allow filtering on thickness, slant, and width!
        if (!empty($filter))
        {
            if (!is_array($filter))
                throw new InvalidArgumentException('$filter is not an array');

            if (!empty($filter['categories']) && is_array($filter['categories']))
            {
                // this should be an enum array of categories that we wish to include
                // e.g.  [sans-serif]=>true, [display]=>true

                $filteredFontList = array();

                foreach($ret->items as $font)
                {
                    if (!empty($filter['categories'][$font->category]))
                    {
                        $filteredFontList[] = $font;
                    }
                }
                $this->fontList = $filteredFontList;
            }
            else
            {
                throw new InvalidArgumentException('$filter[categories] is empty or not an array');
            }
        }
        else
        {
            $this->fontList = $ret->items;
        }

        $this->htmlFontFamilyOptions = '    <option value="-">[ Choose a font ]</option>'.PHP_EOL;

        foreach($this->fontList as $font)
        {
            $this->htmlFontFamilyOptions .= '    <option>'.htmlspecialchars($font->family, ENT_QUOTES, 'UTF-8').'</option>'.PHP_EOL;
        }
        reset($this->fontList); // start from top of array again

    }

    // -----------------------------------------------------------------------
    public function genHTMLFontChooser($selector, $description)
    {
        //$randomColour = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        static $id = 0;
        $id++;

        /*
        $randomColourHex = '#'.base_convert((rand(0, 127) + 127), 10, 16).
                            base_convert((rand(0, 127) + 127), 10, 16).
                            base_convert((rand(0, 127) + 127), 10, 16);
        */

        $randomColour = 'rgba('.(rand(0, 127) + 127).','.
                                (rand(0, 127) + 127).','.
                                (rand(0, 127) + 127).','.
                                '0.7)';

        $ret = '<div style="display: inline-block; padding: 10px; border: 1px dashed black; font-family: sans-serif !important; background-color: '.$randomColour.';">'.PHP_EOL;
        // NB: the font family is marked !important so that it stays fixed as the user chooses fonts for the rest of the page

        $ret .= '    <label for="gff'.$id.'">'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'</label>';

        $selectID = 'gff'.$id;

        $ret .= '<button data-gff-select-id="'.$selectID.'" data-gff-action="prev">&lt;</button> '.PHP_EOL;
        $ret .= '    <select id="'.$selectID.'" data-gff-selector="'.htmlspecialchars($selector, ENT_QUOTES, 'UTF-8').'">'.PHP_EOL;
        $ret .= $this->htmlFontFamilyOptions;
        $ret .= '    </select></label>'.PHP_EOL;
        $ret .= '<button data-gff-select-id="'.$selectID.'" data-gff-action="next">&gt;</button>';

        $ret .= '</div>'.PHP_EOL;

        return $ret;
    }



    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
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

        return $json;
    }

}
