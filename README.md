Hakkında
====================

Sahibinden.com için @tayfunerbilen 'in eskiden hazırlamış olduğu bot'u güncel hale getirdim. Ve yeni özellikler eklemeye devam edeceğim. 
Şuan güzel bir şekilde; kategorileri, alt kategorileri, kategori listelerini ve detayları çekmektedir.

3 formattan dilediğinizi döndürebilirsiniz.

-json
-array
-xml


*default olarak json değer dönmektedir.



Kullanımı da oldukça basit, aşağıdan bakabilirsiniz.



Kullanımı
====================

```php
<?php

header('Content-type: text/html; charset=utf8');
require 'class/sahibinden.class.php';

//Ana Kategoriler
//@return xml,json,array

echo Sahibinden::Kategori();
echo Sahibinden::Kategori("xml");



//Alt Kategoriler
//@return JSON,Array,XML

echo Sahibinden::Kategori("json","ozel-ders-verenler");
echo Sahibinden::Kategori("json","kiralik");



//Listeler
//@return JSON,Array,XML
 $filters = array(
     "date" => "1days", //1,3,7,15,30  //1 günlük ilanlar
     "address_city" => "34", //il plaka kodu
     "address_town" => "71",  //ilçe kodu
     "price_currency" => "1", //1=TL, 2=USD, 3=EUR, 4=GBP  //para birimi
     "price_min" => "0", //minimum fiyat
     "price_max" => "12000", //maximum fiyat
     "hasVideo" => "false", //videolu ilanlar
     "hasPhoto" => "true",  //fotoğrafı olan ilanlar
     "hasMegaPhoto" => "false", // büyük fotoğrafı olan ilanlar
     "sorting" => "price_asc" //sıralama   price_asc, price_desc, date_asc, date_desc, address_desc, address_asc
 );

//print_r(Sahibinden::Liste('kiralik',40,$filters,"array")); // Kiralık Ev Kategorisinden filtrelere uygun 40 kaydı array formatında döndürür
echo Sahibinden::Liste('emlak'); //Emlak Kategorisinden 20 Kaydı JSON formatında döndürür.



//İl ve İlçe Kodları (Filtreleme için)
//@return JSON,Array,XML
echo Sahibinden::TownCodes(NULL, "xml"); //Tüm il ve ilçeleri XML formatında döndürür
echo Sahibinden::TownCodes(34); // İstanbul ilçelerini JSON formatında döndürür




//İlan Detayı
//@return JSON,Array,XML

echo Sahibinden::Detay("/ilan/vasita-otomobil-lotus-lotus-cars-turkey-elise-20th-edition-398612300/detay","json");

