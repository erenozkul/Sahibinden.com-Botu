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
     * @param string $type
     * @return json,array,xml
     */
    static function Liste($kategoriLink, $sayfa = '0', $type = "json")
    {

        $page = '?pagingOffset=' . $sayfa;
        $open = self::Curl(self::$baseUrl . "/" . $kategoriLink . $page);

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
                self::$data[] = @array_merge($linkArray[$i], $titleArray[$i], $imageArray[$i], $priceArray[$i], $dateArray[$i], $addressArray[$i]);
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
     * İlan detaylarını listeler.
     *
     * @param null $url
     * @return array
     */
    static function Detay($url = NULL)
    {
        if ($url != NULL) {

            $open = self::Curl($url);

            // title
            preg_match_all('/<div class="classifiedDetailTitle">    <h1>(.*?)<\/h1>/', $open, $titles);
            $title = $titles[1][0];

            // images
            preg_match_all('/<li>                        <img src="(.*?)" data-source="(.*?)" alt="(.*?)"\/>                    <\/li>/', $open, $imgs);
            foreach ($imgs[1] as $index => $val) {
                $images[] = array(
                    'thumb' => $val,
                    'big' => $imgs[2][$index]
                );
            }

            // açıklama
            preg_match_all('/<div id="classifiedDescription" class="uiBoxContainer">(.*?)<\/div>/', $open, $desc);
            $description = array(
                'html' => self::replaceSpace($desc[1][0]),
                'no_html' => self::replaceSpace(strip_tags($desc[1][0]))
            );

            // genel özellikler
            preg_match_all('/<ul class="classifiedInfoList">(.*?)<\/ul>/', $open, $propertie);
            $prop = self::replaceSpace($propertie[1][0]);
            preg_match_all('/<li> <strong>(.*?)<\/strong>(.*?)<span(.*?)>(.*?)<\/span> <\/li>/', $prop, $p);
            foreach ($p[1] as $index => $val) {
                $properties[trim($val)] = str_replace('&nbsp;', '', trim($p[4][$index]));
            }

            // tüm özellikleri
            preg_match('/<div class="uiBoxContainer classifiedDescription" id="classifiedProperties">(.*?)<\/div>/', $open, $allProperties);
            $allPropertiesString = self::replaceSpace($allProperties[1]);
            preg_match_all('/<h3>(.*?)<\/h3>/', $allPropertiesString, $propertiesTitles);
            preg_match_all('/<ul>(.*?)<\/ul>/', $allPropertiesString, $propertiesResults);
            foreach ($propertiesResults[1] as $index => $val) {
                preg_match_all('/<li class="(.*?)">(.*?)<\/li>/', $val, $result);
                foreach ($result[1] as $index2 => $selected) {
                    $props[$propertiesTitles[1][$index]][] = array($result[2][$index2], $selected);
                }
            }

            // price
            preg_match('/<div class="classifiedInfo">(.*?)<\/div>/', $open, $extra);
            $extras = self::replaceSpace($extra[1]);
            preg_match('/<h3>(.*?)<\/h3>/', $extras, $price);
            $price = trim($price[1]);

            preg_match_all('/<a href="(.*?)">(.*?)<\/a>/', $extras, $addrs);
            $address = array(
                'il' => $addrs[2][0],
                'ilce' => $addrs[2][1],
                'mahalle' => $addrs[2][2]
            );

            // username
            preg_match('/<h5>(.*?)<\/h5>/', $open, $username);
            $username = $username[1];

            // contact info
            preg_match('/<ul class="userContactInfo">(.*?)<\/ul>/', $open, $contact_info);
            $contact_info = self::replaceSpace($contact_info[1]);
            preg_match_all('/<li> <strong>(.*?)<\/strong> <span>(.*?)<\/span> <\/li>/', $contact_info, $contact);

            foreach ($contact[2] as $index => $val) {
                $contacts[$contact[1][$index]] = $val;
            }
            $data = array(
                'title' => $title,
                'images' => $images,
                'address' => $address,
                'description' => $description,
                'properties' => $properties,
                'all_properties' => $props,
                'price' => $price,
                'user' => array(
                    'name' => $username,
                    'contact' => $contacts
                )
            );

            return $data;

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
     * @return XML
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
