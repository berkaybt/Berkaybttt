<?php
// Örnek ürün resimleri oluşturmak için script

function createPlaceholderImage($width, $height, $text, $filename) {
    $image = imagecreate($width, $height);
    
    // Renkler
    $bg_color = imagecolorallocate($image, 248, 249, 250);
    $text_color = imagecolorallocate($image, 102, 126, 234);
    $border_color = imagecolorallocate($image, 221, 221, 221);
    
    // Arka plan
    imagefill($image, 0, 0, $bg_color);
    
    // Kenarlık
    imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);
    
    // Metin
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    // Kaydet
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
}

// Ürün resimleri
$products = [
    'iphone15.jpg' => 'iPhone 15 Pro',
    'samsung-s24.jpg' => 'Samsung Galaxy S24',
    'macbook-air.jpg' => 'MacBook Air M2',
    'nike-airmax.jpg' => 'Nike Air Max',
    'levis-jean.jpg' => 'Levi\'s 501 Jean',
    'kahve-makinesi.jpg' => 'Kahve Makinesi'
];

foreach ($products as $filename => $text) {
    createPlaceholderImage(400, 400, $text, "images/products/$filename");
    echo "Created: images/products/$filename\n";
}

// Kategori resimleri
$categories = [
    'elektronik.jpg' => 'Elektronik',
    'giyim.jpg' => 'Giyim',
    'ev-yasam.jpg' => 'Ev & Yasam',
    'spor.jpg' => 'Spor'
];

foreach ($categories as $filename => $text) {
    createPlaceholderImage(400, 300, $text, "images/categories/$filename");
    echo "Created: images/categories/$filename\n";
}

// Hero background
createPlaceholderImage(1200, 600, 'E-Ticaret Hero Background', 'images/hero-bg.jpg');
echo "Created: images/hero-bg.jpg\n";

echo "Tüm örnek resimler oluşturuldu!\n";
?>