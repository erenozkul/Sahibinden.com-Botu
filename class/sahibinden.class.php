<?php

/**
 * Class Sahibinden
 * User: ErenOzkul
 * Date: 23.02.2017
 * Time: 01:14
 */
require_once "simple_html_dom.php";

class Sahibinden
{
    static $baseUrl = "https://www.sahibinden.com";
    static $data = array();


    /**
     * Ana ve Alt Kategorileri listelemek için kullanılır
     *
     * @param null $url
     * @param array ,json,xml $type
     * @return json,array,xml
     * @return default = json
     */
    static function Kategori($type = "json", $url = NULL)
    {

        if (empty($url)) {
            $open = self::Curl(self::$baseUrl);
            $items = str_get_html($open)->find("ul.categories-left-menu", 0)->find("li a[title]");
            foreach ($items as $element) {
                self::$data[] = array("title" => trim($element->plaintext),
                    "uri" => trim($element->href),
                    "url" => self::$baseUrl . trim($element->href)
                );

            }
        } else {
            $open = self::Curl(self::$baseUrl . '/kategori/' . $url);
            $items = str_get_html($open)->find("ul.categoryList", 0)->find("li a");
            foreach ($items as $element) {
                self::$data[] = array("title" => trim($element->plaintext),
                    "uri" => trim($element->href),
                    "url" => self::$baseUrl . trim($element->href)
                );
            }

        }
        if ($type == "json" or empty($type)) {
            return json_encode(self::$data);
        } else if ($type == "array") {
            return self::$data;
        } else if ($type == "xml") {
            $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
            self::array_to_xml(self::$data, $xml);
            return $xml->asXML();

        }

    }

    /**
     * Kategoriye ait ilanları listeler.
     *
     * @param $kategoriLink
     * @param string $sayfa
     * @return array
     */
    static function Liste($kategoriLink, $sayfa = '0', $type = "json")
    {

        $page = '?pagingOffset=' . $sayfa;
        $open = self::Curl(self::$baseUrl . "/" . $kategoriLink . $page);
        return $open;
        $links = str_get_html($open)->find("td.searchResultsSmallThumbnail a");
        $images = str_get_html($open)->find("td.searchResultsSmallThumbnail a img");
        $prices = str_get_html($open)->find("td.searchResultsPriceValue div");
        $dates = str_get_html($open)->find("td.searchResultsDateValue");
        $addresses = str_get_html($open)->find("td.searchResultsLocationValue");

        foreach ($links as $link) {
            $linkArray[] = array("link" => self::$baseUrl . trim($link->href));
        }
        foreach ($images as $image) {
            $imageArray[] = array("image" => trim($image->src));
            $titleArray[] = array("title" => trim($image->title));
        }
        foreach ($prices as $price) {
            $priceArray[] = array("price" => trim($price->plaintext));
        }
        foreach ($dates as $date) {
            $dateArray[] = array("date" => str_replace("<br>", "", str_replace("</span>", "", str_replace("<span>", "", trim($date->plaintext)))));
        }
        foreach ($addresses as $address) {
            $addressArray[] = array("address" => str_replace("<br>", "", trim($address->plaintext)));
        }
        if (count(@$linkArray) > 0) {
            for ($i = 0; $i <= count($linkArray); $i++) {
                self::$data[] = array_merge($linkArray[$i], $titleArray[$i], $imageArray[$i], $priceArray[$i], $dateArray[$i], $addressArray[$i]);
            }
        } else {
            self::$data = array("error"=> true,"message"=>"Sonuç Bulunamadı.");
        }


        if ($type == "json" or empty($type)) {
            return json_encode(self::$data);
        } else if ($type == "array") {
            return self::$data;
        } else if ($type == "xml") {
            $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
            self::array_to_xml(self::$data, $xml);
            return $xml->asXML();

        }

    }



    /**
     * Gereksiz boşlukları temizler.
     *
     * @param $string
     * @return string
     */
    private function replaceSpace($string)
    {
        $string = preg_replace("/\s+/", " ", $string);
        $string = trim($string);
        return $string;
    }

    /**
     * @param $url
     * @param null $proxy
     * @return mixed
     */
    private function Curl($url, $proxy = NULL)
    {
        $options = array(CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);

        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;

        return str_replace(array("\n", "\r", "\t"), NULL, $header['content']);
    }

    /**
     * PHP Array formatını XML formata çevirir
     *
     * @param $array
     * @return mixed
     */
    private function array_to_xml($array, &$xml_user_info)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml_user_info->addChild("$key");
                    self::array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item");
                    self::array_to_xml($value, $subnode);
                }
            } else {
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }


}
