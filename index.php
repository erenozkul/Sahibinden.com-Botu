<?php
/**
 * User: ErenOzkul
 * Date: 23.02.2017
 * Time: 01:14
 */


header('Content-type: text/html; charset=utf8');
require 'class/sahibinden.class.php';

// ana kategoriler
//echo Sahibinden::Kategori();

// alt kategoriler

//print_r (Sahibinden::Kategori("array","vasita"));
//echo (Sahibinden::Kategori("json","kiralik"));


// kategori içerikleri

print_r(Sahibinden::Liste('kiralik-daire',"0"));
//print_r(Sahibinden::Liste('emlak', 20)); // 2. sayfa


// içerik detayı (henüz tamamlanmadı)

//print_r(Sahibinden::Detay('https://www.sahibinden.com/ilan/vasita-arazi-suv-pick-up-land-rover-borusan%2Csahibinden%2C4.4tdv8-vogue-elektrikli-basamak%2Cmultimedia-395082176/detay'));

