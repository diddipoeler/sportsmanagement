<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       shirt.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage media
 */

//defined( '_JEXEC' ) or die( 'Restricted access' );
defined('_JEXEC');
//use Joomla\CMS\Factory;
//$text = Factory::getApplication()->input->getInt('text', 0);
$text = intval($_GET['text']);
$image_file = dirname(__FILE__).'/shirt.png';

if(is_file($image_file)) {
    $data = getimagesize($image_file);
    $image = imagecreatefrompng($image_file);
    $textcolor = imagecolorallocate($image, 0, 0, 0);
    $xpos = ( $text > 9 )?9:12;
    if (function_exists('imagesavealpha') ) {
        imageAlphaBlending($image, false);
        imageSaveAlpha($image, true);
    }
    imagestring($image, 2, $xpos, 1, $text, $textcolor);
    header("Content-Type: image/png");
    imagepng($image);
    imagedestroy($image);
} else {
    echo 'cannot find template picture in ' . $image_file;
}
