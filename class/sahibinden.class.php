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
    public $baseUrl = "https://www.sahibinden.com";
    public $storeEndUrl = ".sahibinden.com";
    public $data = array();


    /**
     * Ana ve Alt Kategorileri listelemek için kullanılır
     *
     * @param null $url
     * @param array ,json,xml $type
     * @param $proxy false|true
     * @return json,array,xml
     * @return default = json
     */
    public function Kategori($type = "json", $url = NULL, $proxy = false)
    {
        $this->data = array();

        if (empty($url)) {
            if ($proxy == true) {
                $open = $this->Curl($this->baseUrl, true);
            } else {
                $open = $this->Curl($this->baseUrl);
            }
            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {


                    $items = str_get_html($open)->find("ul.categories-left-menu", 0)->find("li a[title]");
                    if (count($items) > 0) {
                        foreach ($items as $element) {
                            $this->data[] = array("title" => trim($element->plaintext),
                                "uri" => trim($element->href),
                                "url" => $this->baseUrl . trim($element->href)
                            );

                        }
                    } else {
                        $this->data[] = array("error" => true, "url" => $this->baseUrl, "message" => "Sonuç Bulunamadı.");

                    }

                } else {
                    $this->data[] = array("error" => true, "url" => $url, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "url" => $url, "message" => $open["error"]);
            }

        } else {
            $url = $this->baseUrl . '/kategori/' . $url;
            if ($proxy == true) {
                $open = $this->Curl($url, true);
            } else {
                $open = $this->Curl($url);
            }
            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {


                    $items = str_get_html($open)->find("ul.categoryList", 0)->find("li a");
                    if (count($items) > 0) {
                        foreach ($items as $element) {
                            $this->data[] = array("title" => trim($element->plaintext),
                                "uri" => trim($element->href),
                                "url" => $this->baseUrl . trim($element->href)
                            );
                        }
                    } else {
                        $this->data[] = array("error" => true, "url" => $url, "message" => "Sonuç Bulunamadı.");

                    }
                } else {
                    $this->data[] = array("error" => true, "url" => $url, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "url" => $url, "message" => $open["error"]);
            }

        }


        return $this->ReturnWithTypes($type);

    }

    /**
     * Kategoriye ait ilanları listeler.
     *
     * @param string $kategoriLink
     * @param int $itemCount
     * @param array $filters
     * @param string $type
     * @param $proxy false|true
     * @return json,array,xml
     */
    public function Liste($kategoriLink, $itemCount = 20, $filters = NULL, $type = "json", $proxy = false)
    {
        $this->data = array();
        $filterText = "";
        if (is_array($filters)) {
            foreach ($filters as $key => $val) {
                if (is_array($filters[$key])) {
                    foreach ($filters[$key] as $v) {
                        $filterText .= "&" . $key . "=" . $v;
                    }
                } else {
                    $filterText .= "&" . $key . "=" . $val;
                }

            }
        }


        if ($itemCount > 20) {
            $pageCount = ceil($itemCount / 20);
        } else {
            $pageCount = 1;
        }

        for ($p = 0; $p <= $pageCount - 1; $p++) {
            $page = $p * 20;

            $pageFilter = '?pagingOffset=' . $page;
            $url = $this->baseUrl . "/" . $kategoriLink . $pageFilter . $filterText;
            if ($proxy == true) {
                $open = $this->Curl($url, true);
            } else {
                $open = $this->Curl($url);
            }
            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {


                    $links = str_get_html($open)->find("td.searchResultsLargeThumbnail a");
                    $images = str_get_html($open)->find("td.searchResultsLargeThumbnail a img");
                    $prices = @str_get_html($open)->find("td.searchResultsPriceValue div");
                    $dates = str_get_html($open)->find("td.searchResultsDateValue");
                    $addresses = str_get_html($open)->find("td.searchResultsLocationValue");
                    $resultText = str_get_html($open)->find("div.infoSearchResults div.result-text", 0)->plaintext;
                    $resultCount = str_get_html($open)->find("div.infoSearchResults div.result-text span", 1)->plaintext;

                    foreach ($links as $link) {
                        $linkArray[] = array("link" => $this->baseUrl . trim($link->href));
                        $uriArray[] = array("uri" => trim($link->href));
                    }
                    foreach ($images as $image) {
                        $thumbArray[] = array("thumb" => trim($image->src));
                        $imageArray[] = array("image" => str_replace("thmb_", "", trim($image->src)));
                        $titleArray[] = array("title" => trim(explode("#", $image->alt)[0]));
                        $idArray[] = array("id" => trim(explode("#", $image->alt)[1]));
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


                } else {
                    $this->data[] = array("error" => true, "url" => $url, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "url" => $url, "message" => $open["error"]);
            }


        }

        if (count(@$linkArray) > 0) {
            $this->data["properties"] = array("count" => $itemCount,
                "resultText" => str_replace('"', "'", $resultText),
                "resultCount" => intval(str_replace(".", "", str_replace(' ilan ', "", $resultCount))),
                "filters" => $filters,
                "url" => str_replace("pagingOffset=" . $page, "", $url));
            if(count($linkArray) < ($itemCount-1)){
                for ($i = 0; $i <= count($linkArray)- 1; $i++) {
                    $this->data["results"][] = @array_merge($idArray[$i], $linkArray[$i], $uriArray[$i], $titleArray[$i], $thumbArray[$i], $imageArray[$i], $priceArray[$i], $dateArray[$i], $addressArray[$i]);
                }
            }else{
                for ($i = 0; $i <= $itemCount - 1; $i++) {
                    $this->data["results"][] = @array_merge($idArray[$i], $linkArray[$i], $uriArray[$i], $titleArray[$i], $thumbArray[$i], $imageArray[$i], $priceArray[$i], $dateArray[$i], $addressArray[$i]);
                }
            }


        } else {
            $this->data[] = array("error" => true, "url" => $url, "message" => "Sonuç Bulunamadı.");
        }

        return $this->ReturnWithTypes($type);

    }


    /**
     * İlan detaylarını listeler.
     *
     * @param null $url
     * @param json $type
     * @param $proxy false|true
     * @return JSON,XML,Array
     */
    public function Detay($uri = NULL, $type = "json", $proxy = false)
    {
        $this->data = array();
        $url = $this->baseUrl . $uri;
        if ($uri != NULL) {
            if ($proxy == true) {
                $open = $this->Curl($url, true);
            } else {
                $open = $this->Curl($url);
            }

            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {

                    $title = str_get_html($open)->find("div.classifiedDetailTitle h1", 0);
                    $this->data = array(
                        "url" => $url,
                        "title" => $title->plaintext,
                        "breadCrumb" => $this->getDetailBreadcrumb($open),
                        "address" => $this->getDetailAddress($open),
                        "price" => $this->getDetailPrice($open),
                        "seller" => $this->getDetailSeller($open),
                        "coordinates" => $this->getDetailCoordinates($open),
                        "info" => $this->getDetailInfo($open),
                        "properties" => $this->getDetailProperties($open),
                        "description" => $this->getDetailDescription($open),
                        "media" => $this->getDetailMedia($open)
                    );


                } else {
                    $this->data[] = array("error" => true, "url" => $url, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "url" => $url, "message" => $open["error"]);
            }


        } else {
            $this->data[] = array("error" => true, "url" => $url, "message" => "Sonuç Bulunamadı.");
        }


        return $this->ReturnWithTypes($type);

    }


    /**
     * Mağazaya bilgilerini getirir
     *
     * @param string $storeName
     * @param array ,json,xml $type
     * @param $proxy false|true
     * @return json,array,xml
     * @return default = json
     */
    public function Magaza($storeName, $type = "json", $proxy = false)
    {
        $this->data = array();
        if (is_array($storeName)) {
            foreach ($storeName as $sn) {
                if (!empty($sn)) {
                    $url = "https://" . $sn . $this->storeEndUrl;
                    if ($proxy == true) {
                        $open = $this->Curl($url, true);
                    } else {
                        $open = $this->Curl($url);
                    }
                    if (!empty(@$open["error"])) {
                        if (!str_get_html($open)->find("div.errorPage404")) {
                            $storeImage = str_get_html($open)->find("div.information-area div", 0)->find("a img", 0);
                            $storePhone = str_get_html($open)->find("div.information-area div", 0)->find("p", 0);
                            $storeCover = str_get_html($open)->find("div.theme", 0)->find("img", 0);
                            $storeIlanSayisi = str_get_html($open)->find("div.classified-count", 0)->find("strong", 0);
                            $storeAbout = str_get_html($open)->find("div.about", 0)->find("h2", 0);
                            $this->data[] = array(
                                "store_name" => $sn,
                                "title" => trim($storeImage->alt),
                                "about" => trim($storeAbout->plaintext),
                                "profile-img" => $storeImage->src,
                                "cover-img" => $storeCover->src,
                                "phone" => trim(@$storePhone->plaintext),
                                "ad-count" => intval(trim($storeIlanSayisi->plaintext)),
                            );
                        } else {
                            $this->data[] = array("error" => true, "store_name" => $sn, "message" => "Sayfa Bulunamadı.");
                        }
                    } else {
                        $this->data[] = array("error" => true, "store_name" => $sn, "message" => $open["error"]);
                    }
                } else {
                    $this->data[] = array("error" => true, "store_name" => $sn, "message" => "Mağaza adı boş olamaz");
                }

            }


        } else {
            $this->data = array("error" => true, "store_name" => $storeName, "message" => "Mağaza ad(lar)ı dizi olarak giriniz.");
        }

        return $this->ReturnWithTypes($type);

    }


    /**
     * Mağazaya ait ana ve alt Kategorileri listelemek için kullanılır
     *
     * @param string $storeName
     * @param null $kategori
     * @param array ,json,xml $type
     * @param $proxy false|true
     * @return json,array,xml
     * @return default = json
     */
    public function MagazaKategori($storeName, $kategori = NULL, $type = "json", $proxy = false)
    {
        if (!empty($storeName)) {
            if ($kategori == NULL) {
                $url = "https://" . $storeName . $this->storeEndUrl;
            } else {
                $url = "https://" . $storeName . $this->storeEndUrl . "/" . $kategori;
            }


            if ($proxy == true) {
                $open = $this->Curl($url, true);
            } else {
                $open = $this->Curl($url);
            }
            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {

                    for ($x = 0; $x <= 10; $x++) {
                        $ul = str_get_html($open)->find("div.categories ul li.level" . $x);
                        foreach ($ul as $u) {
                            $categories = $u->find("a");
                            foreach ($categories as $c) {
                                $uri = explode("?", str_replace("/", "", $c->href));
                                $cats = array(
                                    "title" => trim($c->plaintext),
                                    "uri" => $uri[0],
                                    "is_current_category" => $uri[0] == $kategori ? true : false,
                                    "url" => "https://" . $storeName . $this->storeEndUrl . "/" . $uri[0],
                                    "sub_categories" => NULL
                                );
                            }
                            $level = str_replace("level", "", $u->class);
                            if ($level == 0) {
                                $this->data[] = $cats;
                            } else if ($level == 1) {
                                $this->data[$x - $level]["sub_categories"][] = $cats;
                            } else if ($level == 2) {
                                $this->data[$x - $level]["sub_categories"][$x - $level]["sub_categories"][] = $cats;
                            } else if ($level == 3) {
                                $this->data[$x - $level]["sub_categories"][$x - $level]["sub_categories"][$x - $level]["sub_categories"][] = $cats;
                            } else if ($level == 4) {
                                $this->data[$x - $level]["sub_categories"][$x - $level]["sub_categories"][$x - $level]["sub_categories"][$x - $level]["sub_categories"][] = $cats;
                            }


                        }
                    }


                } else {
                    $this->data[] = array("error" => true, "store_name" => $storeName, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "store_name" => $storeName, "message" => $open["error"]);
            }

        } else {
            $this->data[] = array("error" => true, "store_name" => $storeName, "message" => "Mağaza adı giriniz.");
        }

        return $this->ReturnWithTypes($type);

    }


    /**
     * Mağazaya ait ilanları listeler.
     *
     * @param string $storeName
     * @param int $itemCount
     * @param array $filters
     * @param string $type
     * @param $proxy false|true
     * @return JSON,XML,Array
     */
    public function MagazaListe($storeName, $itemCount = 20, $filters = NULL, $type = "json", $proxy = false)
    {

        $this->data = array();
        $filterText = "";
        if (is_array($filters)) {
            foreach ($filters as $key => $val) {
                if (is_array($filters[$key])) {
                    foreach ($filters[$key] as $v) {
                        $filterText .= "&" . $key . "=" . $v;
                    }
                } else {
                    $filterText .= "&" . $key . "=" . $val;
                }

            }
        }


        if ($itemCount > 20) {
            $pageCount = ceil($itemCount / 20);
        } else {
            $pageCount = 1;
        }
        $ic = 0;
        for ($p = 0; $p <= $pageCount - 1; $p++) {
            $page = $p * 20;
            $pageFilter = '?pagingOffset=' . $page;
            $url = "https://" . $storeName . $this->storeEndUrl . $pageFilter . $filterText;
            if ($proxy == true) {
                $open = $this->Curl($url, true);
            } else {
                $open = $this->Curl($url);
            }

            $columns = str_get_html($open)->find("div.classified-list table thead th");
            $tr = str_get_html($open)->find("div.classified-list table tbody tr");
            $colCount = count($columns);
            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {

                    if (count($tr) > 0) {

                        for ($j = 1; $j <= count($tr) - 1; $j++) {
                            if ($ic == $itemCount) {
                                continue;
                            } else {
                                $d = array();

                                $href = str_get_html($open)->find("div.classified-list table tbody tr", $j)->find("td", 0)->find("a", 0);
                                $img = str_get_html($open)->find("div.classified-list table tbody tr", $j)->find("td", 0)->find("a", 0)->find("img", 0);
                                $baslik = explode("#", $img->alt);
                                $d["id"] = $baslik[1];
                                $d["title"] = trim($baslik[0]);
                                $d["link"] = $href->href;
                                $d["image"] = $img->src;

                                $imgExp = explode("/", $img->src);
                                $thmb = "thmb_" . end($imgExp);
                                array_pop($imgExp);
                                array_push($imgExp, $thmb);
                                $thumb = implode("/", $imgExp);
                                $d["thumb"] = $thumb;

                                for ($x = 0; $x <= $colCount - 1; $x++) {
                                    $row = str_get_html($open)->find("div.classified-list table tbody tr", $j)->find("td", $x);
                                    if (!empty(trim($columns[$x]->plaintext))) {
                                        $title = $this->turkishChars(strtolower(trim($columns[$x]->plaintext)));
                                        $d[$title] = trim($row->plaintext);
                                    }
                                }


                                $this->data[] = $d;
                                $ic++;
                            }
                        }
                    } else {
                        $this->data[] = array("error" => true, "url" => $url, "message" => "Sonuç Bulunamadı.");
                    }

                } else {
                    $this->data[] = array("error" => true, "store_name" => $storeName, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "store_name" => $storeName, "message" => $open["error"]);
            }


        }


        return $this->ReturnWithTypes($type);

    }


    /**
     * Belirtilen mağazanın danışman listesini döndürür
     *
     * @param $store_name string
     * @param $type string
     * @param $proxy false|true
     * @return JSON,XML,Array
     */
    public function MagazaDanismanlari($storeName, $type = "json", $proxy = false)
    {

        $this->data = array();
        if (!empty($storeName)) {
            $url = "https://" . $storeName . $this->storeEndUrl;
            if ($proxy == true) {
                $open = $this->Curl($url, true);
            } else {
                $open = $this->Curl($url);
            }

            if (!empty(@$open["error"])) {
                if (!str_get_html($open)->find("div.errorPage404")) {


                    $agentsLink = str_get_html($open)->find("div.oc-select-list ul li a");
                    $agentsName = str_get_html($open)->find("div.oc-select-list ul li a p");
                    $agentsImg = str_get_html($open)->find("div.oc-select-list ul li a img");
                    $agentsPhone = str_get_html($open)->find("div.oc-select-list ul li a span");

                    for ($a = 0; $a <= count($agentsLink) - 1; $a++) {
                        $agentID = explode("userId=", $agentsLink[$a]->href);

                        $this->data[] = array(
                            "name" => trim($agentsName[$a]->plaintext),
                            "userId" => $agentID[1],
                            "image_200" => $agentsImg[$a]->src,
                            "image_400" => str_replace("p200", "p400", $agentsImg[$a]->src),
                            "phone" => trim($agentsPhone[$a]->plaintext)
                        );
                    }

                } else {
                    $this->data[] = array("error" => true, "store_name" => $storeName, "message" => "Sayfa Bulunamadı.");
                }
            } else {
                $this->data[] = array("error" => true, "store_name" => $storeName, "message" => $open["error"]);
            }

        } else {
            $this->data = array("error" => true, "store_name" => $storeName, "message" => "Mağaza adı bulunamadı.");
        }

        return $this->ReturnWithTypes($type);

    }

    /**
     * Sahibinden.com Filtrelemelerine uygun il içe isimleri ve kodlarını döndürür
     *
     * @param $il //Plaka Kodu
     * @param $type //Dönecek veri formatı
     * @param $proxy false|true
     * @return  JSON,XML,Array
     */
    public function TownCodes($il = NULL, $type = "json")
    {
        /* ilce.html den gelen veri ilce.json a bu şekilde aktarıldı.
        $data = file_get_html("ilce.html")->find("li");
        foreach ($data as $e) {
            $this->data[$e->attr["data-parentid"]][] = array(
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
                $this->array_to_xml($ilceJson[$il], $xml);
                return $xml->asXML();
            } else {
                $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
                $this->array_to_xml($ilceJson, $xml);
                return $xml->asXML();
            }
        }


    }


    /**
     * Detay methodu için ilanın video ve fotoğraflarını getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailMedia($html)
    {
        $images = str_get_html($html)->find("ul.classifiedDetailThumbListPages img");
        $movies = str_get_html($html)->find("source#mp4");

        $thumbArray = array();
        $imageArray = array();
        $megaArray = array();
        $movieArray = array();
        if (count($images) > 0) {
            foreach ($images as $img) {
                $thumbArray[] = $img->src;
                $imageArray[] = str_replace("thmb_", "", $img->src);
                $megaArray[] = str_replace("thmb_", "x16_", $img->src);

            }
        }
        if (count($movies) > 0) {
            foreach ($movies as $movie) {
                $movieArray[] = $movie->src;
            }
        }


        $return = array("thumb_images" => $thumbArray,
            "standart_images" => $imageArray,
            "mega_images" => $megaArray,
            "movies" => $movieArray);

        return $return;


    }

    /**
     * Detay methodu için ilanın özelliklerini getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailProperties($html)
    {
        $propertyTitles = str_get_html($html)->find("div#classified-detail", 0)->find("div.uiBox", 1)->find("div.classifiedDescription", 0)->find("h3");
        $propertyCount = str_get_html($html)->find("div#classified-detail", 0)->find("div.uiBox", 1)->find("div.classifiedDescription", 0)->find("ul");
        $propertyArray = array();
        if (count($propertyCount) > 0) {

            for ($p = 0; $p <= count($propertyCount) - 1; $p++) {
                $propertyDetails = str_get_html($html)->find("div#classified-detail", 0)->find("div.uiBox", 1)->find("div.classifiedDescription", 0)->find("ul", $p)->find("li.selected");
                $ppDetails = array();
                if (count($propertyDetails) > 0) {
                    for ($d = 0; $d <= count($propertyDetails) - 1; $d++) {
                        $ppDetails[] = trim($propertyDetails[$d]->plaintext);
                    }
                }

                $propertyArray[] = array(trim($propertyTitles[$p]->plaintext) => $ppDetails);
            }
            return $propertyArray;
        } else {
            return $propertyArray;

        }


    }

    /**
     * Detay methodu için ilanın breadcrumb'ını getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailBreadcrumb($html)
    {
        $breadCrumb = str_get_html($html)->find("div.classifiedBreadCrumb ul li");
        $breadArray = array();
        if (count($breadCrumb) > 0) {
            foreach ($breadCrumb as $bc) {
                $breadArray[] = trim($bc->plaintext);
            }
        }

        return $breadArray;


    }


    /**
     * Detay methodu için ilanın adresini getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailAddress($html)
    {
        $address = str_get_html($html)->find("div.classifiedInfo h2", 0)->find("a");


        $return = array(
            "city" => trim($address[0]->plaintext),
            "town" => trim($address[1]->plaintext),
            "district" => trim($address[2]->plaintext)
        );

        return $return;

    }


    /**
     * Detay methodu için ilanın koordinatlarını getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailCoordinates($html)
    {

        $map = str_get_html($html)->find("div#gmap", 0);
        $return = array(
            "latitude" => trim(@$map->attr["data-lat"]),
            "longitude" => trim(@$map->attr["data-lon"])
        );

        return $return;


    }


    /**
     * Detay methodu için ilanın fiyatını getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailPrice($html)
    {

        $price = str_get_html($html)->find("div.classifiedInfo h3", 0);
        $priceTrim = str_get_html($html)->find("div.classifiedInfo h3 a", 0);


        return trim(str_replace($priceTrim->plaintext, "", $price->plaintext));

    }

    /**
     * Detay methodu için ilan sahibinin bilgilerini getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailSeller($html)
    {

        $sellerName = str_get_html($html)->find("div.classifiedUserContent h5", 0);
        $sellerStore = str_get_html($html)->find("a.userClassifieds", 0);
        $sellerImg = str_get_html($html)->find("div.classifiedUserContent a img", 0);
        $sellerPhoneFields = str_get_html($html)->find("ul#phoneInfoPart li strong");
        $sellerPhoneText = str_get_html($html)->find("ul#phoneInfoPart li span.pretty-phone-part");

        if (count($sellerPhoneFields) > 0) {
            for ($f = 0; $f <= count($sellerPhoneFields) - 1; $f++) {
                $phoneArray[] = array("title" => trim($sellerPhoneFields[$f]->plaintext), "text" => trim($sellerPhoneText[$f]->plaintext));
            }
        }

        $return = array(
            "name" => trim(@$sellerName->plaintext),
            "store_link" => $sellerStore->href,
            "image" => @$sellerImg->src,
            "phones" => @$phoneArray
        );

        return $return;

    }

    /**
     * Detay methodu için ilanın açıklamasını getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailDescription($html)
    {

        $description = str_get_html($html)->find("div.classifiedDescription", 0);

        $return = array("text" => trim($description->plaintext),
            "base64" => base64_encode($description->innertext));

        return $return;
    }

    /**
     * Detay methodu için ilanın detaylarını getirir
     *
     * @param $html //Detay sayfası html
     * @return  Array
     */
    private function getDetailInfo($html)
    {

        $infoListFields = str_get_html($html)->find("ul.classifiedInfoList li strong");
        $infoListTexts = str_get_html($html)->find("ul.classifiedInfoList li span");
        $infoArray = array();
        if (count($infoListFields) > 0) {
            for ($f = 0; $f <= count($infoListFields) - 1; $f++) {
                $infoArray[] = array("title" => trim($infoListFields[$f]->plaintext), "text" => trim($infoListTexts[$f]->plaintext));
            }
        }

        return $infoArray;


    }

    /**
     * Array formatından XML veya JSON formatı oluşturur
     *
     * @param $type //Dönecek veri formatı
     * @return  JSON,XML,Array
     */
    private function ReturnWithTypes($type = "json")
    {

        if ($type == "json" or empty($type)) {
            return json_encode($this->data);
        } else if ($type == "array") {
            return $this->data;
        } else if ($type == "xml") {
            $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
            $this->array_to_xml($this->data, $xml);
            return $xml->asXML();
        }
    }


    /**
     * Türkçe Karakterleri İngilizce karaktere çevirir boşlukları "-" tireye çevirir
     *
     * @param $string
     * @return string
     */
    private function turkishChars($s)
    {
        $tr = array('ş', 'Ş', 'ı', 'I', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'Ç', 'ç', '(', ')', '/', ':', ',', '&', '"', "“", "”");
        $eng = array('s', 's', 'i', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c', '', '', '-', '-', '', "", "", "");
        $s = str_replace($tr, $eng, $s);
        $s = strtolower($s);
        $s = preg_replace('/&amp;amp;amp;amp;amp;amp;amp;amp;amp;.+?;/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('|-+|', '-', $s);
        $s = preg_replace('/#/', '', $s);
        $s = trim($s, '-');
        return $s;
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
    private function Curl($url, $proxy = false)
    {

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,

        );
        if ($proxy == true) {
            $proxyList = json_decode(file_get_contents("proxy.json"),true);
            $p = rand(0, count($proxyList) - 1);
            array_push($options, array(CURLOPT_PROXY => $proxyList[$p]));
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['redirect'] = $redirectURL;
        $header['content'] = $content;
        if (empty($errmsg)) {

            return str_replace(array("\n", "\r", "\t"), NULL, $header['content']);
        } else {
            return array("error" => "code: " . $err . " message:" . $errmsg);
        }
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
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }


}
