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
            if(count($items) > 0) {
                foreach ($items as $element) {
                    self::$data[] = array("title" => trim($element->plaintext),
                        "uri" => trim($element->href),
                        "url" => self::$baseUrl . trim($element->href)
                    );

                }
            }else{
                self::$data[] = array("error" => true, "url" => self::$baseUrl,"message" => "Sonuç Bulunamadı.");

            }
        } else {
            $link = self::$baseUrl . '/kategori/' . $url;
            $open = self::Curl($link);
            $items = str_get_html($open)->find("ul.categoryList", 0)->find("li a");
            if (count($items) > 0) {
                foreach ($items as $element) {
                    self::$data[] = array("title" => trim($element->plaintext),
                        "uri" => trim($element->href),
                        "url" => self::$baseUrl . trim($element->href)
                    );
                }
            } else {
                self::$data[] = array("error" => true, "url" => $link,"message" => "Sonuç Bulunamadı.");

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
    static function Liste($kategoriLink, $itemCount = 20, $filters, $type = "json")
    {

        $filterText = "";
        foreach ($filters as $key => $val) {
            $filterText .= "&" . $key . "=" . $val;
        }


        if ($itemCount > 20) {
            $pageCount = ceil($itemCount / 20);
        } else {
            $pageCount = 1;
        }
        for ($p = 0; $p <= $pageCount - 1; $p++) {
            $page = $p * 20;

            $pageFilter = '?pagingOffset=' . $page;
            $url = self::$baseUrl . "/" . $kategoriLink . $pageFilter . $filterText;
            $open = self::Curl($url);

            $links = str_get_html($open)->find("td.searchResultsSmallThumbnail a");
            $images = str_get_html($open)->find("td.searchResultsSmallThumbnail a img");
            $prices = @str_get_html($open)->find("td.searchResultsPriceValue div");
            $dates = str_get_html($open)->find("td.searchResultsDateValue");
            $addresses = str_get_html($open)->find("td.searchResultsLocationValue");
            $resultText = str_get_html($open)->find("div.infoSearchResults div.result-text", 0)->plaintext;
            $resultCount = str_get_html($open)->find("div.infoSearchResults div.result-text span", 1)->plaintext;

            foreach ($links as $link) {
                $linkArray[] = array("link" => self::$baseUrl . trim($link->href));
                $uriArray[] = array("uri" => trim($link->href));
            }
            foreach ($images as $image) {
                $thumbArray[] = array("thumb" => trim($image->src));
                $imageArray[] = array("image" => str_replace("thmb_", "", trim($image->src)));
                $titleArray[] = array("title" => trim(explode("#",$image->alt)[0]));
                $idArray[] = array("id" => trim(explode("#",$image->alt)[1]));
            }
            foreach (@$prices as $price) {
                $priceArray[] = array("price" => trim($price->plaintext));
            }
            foreach ($dates as $date) {
                $dateArray[] = array("date" => str_replace("<br>", "", str_replace("</span>", "", str_replace("<span>", "", trim($date->plaintext)))));
            }
            foreach ($addresses as $address) {
                $addressArray[] = array("address" => str_replace("<br>", "", trim($address->plaintext)));
            }


        }

        if (count(@$linkArray) > 0) {
            self::$data["properties"] = array("count" => count($linkArray),
                "resultText" => str_replace('"', "'", $resultText),
                "resultCount" => intval(str_replace(".", "", str_replace(' ilan ', "", $resultCount))),
                "filters" => $filters,
                "url" => str_replace("pagingOffset=" . $page, "", $url));
            for ($i = 0; $i <= $itemCount - 1; $i++) {
                self::$data["results"][] = @array_merge($idArray[$i], $linkArray[$i], $uriArray[$i], $titleArray[$i], $thumbArray[$i], $imageArray[$i], $priceArray[$i], $dateArray[$i], $addressArray[$i]);
            }

        } else {
            self::$data[] = array("error" => true, "url" => $url,"message" => "Sonuç Bulunamadı.");
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
    static function Detay($uri = NULL,$type = "json")
    {
        $url = self::$baseUrl.$uri;
        if($uri != NULL){
            $open = self::Curl($url);

            $title = str_get_html($open)->find("div.classifiedDetailTitle h1",0);
            $breadCrumb = str_get_html($open)->find("div.classifiedBreadCrumb ul li");
            $address = str_get_html($open)->find("div.classifiedInfo h2",0)->find("a");
            $images = str_get_html($open)->find("ul.classifiedDetailThumbListPages img");
            $movies = str_get_html($open)->find("source#mp4");
            $infoListFields = str_get_html($open)->find("ul.classifiedInfoList li strong");
            $infoListTexts = str_get_html($open)->find("ul.classifiedInfoList li span");
            $price = str_get_html($open)->find("div.classifiedInfo h3",0);
            $priceTrim = str_get_html($open)->find("div.classifiedInfo h3 a",0);
            $description = str_get_html($open)->find("div.classifiedDescription",0);
            $sellerName = str_get_html($open)->find("div.classifiedUserContent h5",0);
            $sellerStore= str_get_html($open)->find("a.userClassifieds",0);
            $sellerImg = str_get_html($open)->find("div.classifiedUserContent a img",0);
            $sellerPhoneFields = str_get_html($open)->find("ul#phoneInfoPart li strong");
            $sellerPhoneText = str_get_html($open)->find("ul#phoneInfoPart li span.pretty-phone-part");
            $map = str_get_html($open)->find("div#gmap",0);
            $propertyTitles = str_get_html($open)->find("div#classified-detail",0)->find("div.uiBox",1)->find("div.classifiedDescription",0)->find("h3");
            $propertyCount = str_get_html($open)->find("div#classified-detail",0)->find("div.uiBox",1)->find("div.classifiedDescription",0)->find("ul");

            if(count($propertyCount)>0){
                for($p=0; $p<=count($propertyCount)-1; $p++){
                    $propertyDetails = str_get_html($open)->find("div#classified-detail",0)->find("div.uiBox",1)->find("div.classifiedDescription",0)->find("ul",$p)->find("li.selected");
                    $ppDetails = array();
                    if(count($propertyDetails)>0){
                        for($d=0; $d<=count($propertyDetails)-1; $d++){
                            $ppDetails[] = trim($propertyDetails[$d]->plaintext);
                        }
                    }

                    $propertyArray[] = array(trim($propertyTitles[$p]->plaintext) => $ppDetails);
                }
            }
            if(count($sellerPhoneFields)>0){
                for($f=0; $f<=count($sellerPhoneFields)-1; $f++){
                    $phoneArray[] = array("title"=>trim($sellerPhoneFields[$f]->plaintext),"text" => trim($sellerPhoneText[$f]->plaintext));
                }
            }
            if(count($infoListFields)>0){
                for($f=0; $f<=count($infoListFields)-1; $f++){
                    $infoArray[] = array("title" => trim($infoListFields[$f]->plaintext),"text" => trim($infoListTexts[$f]->plaintext));
                }
            }
            if(count($breadCrumb)>0){
                foreach ($breadCrumb as $bc){
                    $breadArray[] = trim($bc->plaintext);
                }
            }
            if(count($images) > 0) {
                foreach ($images as $img) {
                    $thumbArray[]=$img->src;
                    $imageArray[]=str_replace("thmb_","",$img->src);
                    $megaArray[]=str_replace("thmb_","x16_",$img->src);

                }
            }
            if(count($movies)>0){
                foreach ($movies as $movie) {
                    $movieArray[] = $movie->src;
                    }
            }

            self::$data = array(
                "url" => $url,
                "title" => $title->plaintext,
                "breadCrumb" => $breadArray,
                "address" => array("city" => trim($address[0]->plaintext),"town" => trim($address[1]->plaintext),"district" => trim($address[2]->plaintext)),
                "price" => trim(str_replace($priceTrim->plaintext,"",$price->plaintext)),
                "seller" => array("name"=>trim(@$sellerName->plaintext),"store_link" =>self::$baseUrl.@trim(str_replace(self::$baseUrl,"",$sellerStore->href)),"image"=>@$sellerImg->src,"phones" => @$phoneArray),
                "coordinates" => array("latitude"=>trim(@$map->attr["data-lat"]),"longitude" =>trim(@$map->attr["data-lon"]) ),
                "info" => $infoArray,
                "properties" =>$propertyArray,
                "description"=> $description->plaintext,
                "description_base64"=> base64_encode($description->plaintext),
                "thumbs" => $thumbArray,
                "images" => $imageArray,
                "mega_images"=> @$megaArray,
                "movies"=>@$movieArray);


        }else{
            self::$data[] = array("error" => true, "url" => $url,"message" => "Sonuç Bulunamadı.");
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
     * Sahibinden.com Filtrelemelerine uygun il içe isimleri ve kodlarını döndürür
     *
     * @param $il //Plaka Kodu
     * @param $type //Dönecek veri formatı
     * @return  JSON,XML,Array
     */
    static function TownCodes($il = NULL, $type = "json")
    {
        /* ilce.html den gelen veri ilce.json a bu şekilde aktarıldı.
        $data = file_get_html("ilce.html")->find("li");
        foreach ($data as $e) {
            self::$data[$e->attr["data-parentid"]][] = array(
                "il-id" => $e->attr["data-parentid"],
                "il-adi" => $e->attr["data-parentlabel"],
                "ilce-id" => $e->attr["data-id"],
                "ilce-adi" => $e->plaintext);
        }
        */
        $ilceJson = json_decode(file_get_contents("ilce.json"), true);
        if ($type == "json") {
            if ($il != NULL) {
                return json_encode($ilceJson[$il]);
            } else {
                return json_encode($ilceJson);
            }
        } else if ($type == "array") {
            if ($il != NULL) {
                return $ilceJson[$il];
            } else {
                return $ilceJson;
            }
        } else if ($type == "xml") {
            if ($il != NULL) {
                $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
                self::array_to_xml($ilceJson[$il], $xml);
                return $xml->asXML();
            } else {
                $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
                self::array_to_xml($ilceJson, $xml);
                return $xml->asXML();
            }
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
