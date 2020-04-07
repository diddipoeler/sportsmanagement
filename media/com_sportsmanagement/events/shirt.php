<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage media
 * @file       shirt.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
$text = Factory::getApplication()->input->getInt('text', 0);
$image_file = "shirt.png";
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
?>
