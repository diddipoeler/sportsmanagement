<?php
//defined( '_JEXEC' ) or die( 'Restricted access' );
//$text = JRequest::getInt('text');
$text = intval( $_GET[ 'text' ] );
//echo dirname(__FILE__).'<br>';
$image_file = dirname(__FILE__).'/shirt.png';

if(is_file($image_file)) {
	$data = getimagesize ( $image_file );
	$image = imagecreatefrompng ( $image_file );
	$textcolor = imagecolorallocate ( $image, 0, 0, 0 );
	$xpos = ( $text > 9 )?9:12;
	if ( function_exists ( 'imagesavealpha' ) )
	{
		imageAlphaBlending ( $image, false );
		imageSaveAlpha ( $image, true );
	}
	imagestring ( $image, 2, $xpos, 1, $text, $textcolor);
	header ( "Content-Type: image/png" );
	imagepng ( $image );
	imagedestroy ( $image );
} else {
	echo 'cannot find template picture in ' . $image_file;
}
