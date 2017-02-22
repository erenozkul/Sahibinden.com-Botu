Hakkında
====================

Sahibinden.com için @tayfunerbilen 'in eskiden hazırlamış olduğu bot'u güncel hale getirdim. Ve yeni özellikler eklemeye devam edeceğim. 
Şuan güzel bir şekilde, kategorileri alt kategorileri vs. kategori listelerini  çekmektedir.

Dilediğiniz 3 formatta return alabilirsiniz;

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

// ana kategoriler
print_r( Sahibinden::Kategori() );

// alt kategoriler xml formatında
print_r( Sahibinden::Kategori('emlak',"xml") );
//alt kategoriler array formatında
print_r( Sahibinden::Kategori('emlak',"array") );

// kategori içerikleri
print_r( Sahibinden::Liste('vasita') );
// Sahibinden::Liste('emlak', 20); // 2. sayfa
