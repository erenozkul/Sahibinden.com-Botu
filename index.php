<?php
/**
 * User: ErenOzkul
 * Date: 23.02.2017
 * Time: 01:14
 */
header('Content-type: text/html; charset=utf8');
require 'class/sahibinden.class.php';

ini_set('memory_limit', '512M');
set_time_limit(0);

//Ana Kategoriler
//@return xml,json,array

//echo Sahibinden::Kategori();
//echo Sahibinden::Kategori("xml","emlak",true); //Emlak Kategorisindekiler proxy kullanara xml formatında döndürür

//Alt Kategoriler
//@return xml,json,array

//echo Sahibinden::Kategori("json","ozel-ders-verenler");
//echo Sahibinden::Kategori("json","kiralik");


//Listeler
//@return xml,json,array
 $filters = array(
     "date" => "1days", //1,3,7,15,30  //1 günlük ilanlar
     "address_city" => array("34"), //il plaka kodu
     "address_town" => array("451","435"),  //ilçe kodu
     "price_currency" => "1", //1=TL, 2=USD, 3=EUR, 4=GBP  //para birimi
     "price_min" => "5000", //minimum fiyat
     "price_max" => "12000", //maximum fiyat
     "hasVideo" => "false", //videolu ilanlar
     "hasPhoto" => "true",  //fotoğrafı olan ilanlar
     "hasMegaPhoto" => "false", // büyük fotoğrafı olan ilanlar
     "sorting" => "price_asc" //sıralama   price_asc, price_desc, date_asc, date_desc, address_desc, address_asc
 );

//print_r(Sahibinden::Liste('kiralik',40,$filters,"array")); // Kiralık Ev Kategorisinden filtrelere uygun 40 kaydı array formatında döndürür
//echo Sahibinden::Liste('kiralik-daire'); //Emlak Kategorisinden 20 Kaydı JSON formatında döndürür.

//İl ve İlçe Kodları (Filtreleme için)
//@return xml,json,array
//echo Sahibinden::TownCodes(NULL, "xml"); //Tüm il ve ilçeleri XML formatında döndürür
//echo Sahibinden::TownCodes(34); // İstanbul ilçelerini JSON formatında döndürür



//İlan Detayı

//echo Sahibinden::Detay("/ilan/vasita-otomobil-lotus-lotus-cars-turkey-elise-20th-edition-398612300/detay","json");


//Mağaza Bilgileri
$stores = array("remaxpiramit",
                "vatanotomobil",
                "blackmotors");
//echo Sahibinden::Magaza($stores);

//Mağaza Kategorilerini Alt Kategoriyle birlikte
//print_r(Sahibinden::MagazaKategori("remaxpiramit",NULL,"array"));
//echo Sahibinden::MagazaKategori("remaxpiramit",NULL,"json",true);// Mağaza Kategorilerini proxy ile json formatında getirir



//Mağaza İlan Listesi
$filters = array(
    "userId" => "57127" //Birden fazla seçilemez
);
//echo Sahibinden::MagazaListe("remaxpiramit",21);

//Mağaza Danışman Listesi

//echo Sahibinden::MagazaDanismanlari("remaxpiramit","json");





