<?php
header ("Content-type: image/png");
$string = $_GET['text'];                                              
$font   = 2;
$width  = ImageFontWidth($font) * strlen($string);
$height = ImageFontHeight($font);


$image_file = dirname(__FILE__).'/shirt_php8.png';

$data = getimagesize($image_file);
$image = imagecreatefrompng($image_file);

 $xpos = ( $string > 9 ) ? 9 : 12;
    if (function_exists('imagesavealpha') ) {
        imageAlphaBlending($image, false);
        imageSaveAlpha($image, true);
    }


$text_color = imagecolorallocate ($image, 0, 0,0);//black text


//imagestring($image, 2, $xpos, 1, $text, $textcolor);
imagestring ($image, $font, $xpos , 1,  $string, $text_color);

//echo $image_file;
//$background_color = imagecolorallocate ($image, 255, 255, 255); //white background
//$text_color = imagecolorallocate ($image, 0, 0,0);//black text
//imagestring ($image, $font, 0, 0,  $string, $text_color);
imagepng ($image);



/**
$im = @imagecreate ($width,$height);
$background_color = imagecolorallocate ($im, 255, 255, 255); //white background
$text_color = imagecolorallocate ($im, 0, 0,0);//black text
imagestring ($im, $font, 0, 0,  $string, $text_color);
imagepng ($im);
*/




?>