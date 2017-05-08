Hakkında
====================


Sahibinden.com için @tayfunerbilen 'in eskiden hazırlamış olduğu bot'u güncel hale getirdim. Ve yeni özellikler eklemeye devam edeceğim. 
Şuan güzel bir şekilde; kategorileri, alt kategorileri, kategori listelerini ve detayları çekmektedir.

3 formattan dilediğinizi döndürebilirsiniz.
* JSON
* Array
* XML

Kullanımına aşağıdan bakabilirsiniz.

* Default olarak json değer dönmektedir.
* Proxy kullanımı istek sürenizi uzatabilir

Kullanımı
=

<h4>Tanımlama</h4>

```php
require 'class/sahibinden.class.php';
$Sahibinden = new Sahibinden();
```

<br><br>
<h4>Ana Kategoriler</h4>
@return xml,json,array
```php
echo $Sahibinden->Kategori();
echo $Sahibinden->Kategori("xml","emlak",true); //Emlak Kategorisindekiler proxy kullanara xml formatında döndürür
```
<br><br>
<h4>Alt Kategoriler</h4>
@return xml,json,array
```php
echo $Sahibinden->Kategori("json","ozel-ders-verenler");
echo $Sahibinden->Kategori("json","kiralik");
```
<br><br>
<h4>Listeler</h4>
Sahibinden'de ilan içerisinde kullanılan tüm GET parametrelerini "filters" dizisine key=>value şeklinde ekleyerek filtremeleri yapabilirsiniz<br>
@return xml,json,array


```php
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

print_r($Sahibinden->Liste('kiralik',40,$filters,"array")); // Kiralık Ev Kategorisinden filtrelere uygun 40 kaydı array formatında döndürür
echo $Sahibinden->Liste('emlak'); //Emlak Kategorisinden 20 Kaydı JSON formatında döndürür.
```

<br><br>
<h4>İl ve İlçe Kodları (Filtreleme için)</h4>
@return xml,json,array
```php
echo $Sahibinden->TownCodes(NULL, "xml"); //Tüm il ve ilçeleri XML formatında döndürür
echo $Sahibinden->TownCodes(34); // İstanbul ilçelerini JSON formatında döndürür
```
<br><br>
<h4>İlan Detayı</h4>
@return xml,json,array
```php
echo $Sahibinden->Detay("/ilan/vasita-otomobil-lotus-lotus-cars-turkey-elise-20th-edition-398612300/detay","json");
```

<br><br>

Mağazalar
-
<h4>Mağaza Bilgileri</h4>
```php
$stores = array("remaxpiramit",
                "vatanotomobil",
                "blackmotors");
echo $Sahibinden->Magaza($stores);
```

<h4>Mağaza Kategorileri (Alt Kategorileri ile birlikte)</h4>
@return xml,json,array
```php
print_r($Sahibinden->MagazaKategori("remaxpiramit",NULL,"array"));
echo $Sahibinden->MagazaKategori("remaxpiramit",NULL,"json",true);// Mağaza Kategorilerini proxy ile json formatında getirir
```

<br><br>
<h4>Mağaza İlan Listesi</h4>
@return xml,json,array
```php
$filters = array(
    "userId" => "57127"
);
echo $Sahibinden->MagazaListe("remaxpiramit",20,$filters);
```

<br><br>
<h4>Mağaza Danışman Listesi</h4>
@return xml,json,array
```php
echo $Sahibinden->MagazaDanismanlari("remaxpiramit","json");
```




