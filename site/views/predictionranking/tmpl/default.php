<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionranking
 */

defined('_JEXEC') or die('Restricted access');
?>

<?php
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews','predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

//$kmlpath = JURI::root().'tmp'.DS.$this->predictionGame->id.'-prediction.kml';

$this->kmlpath = JURI::root().'tmp'.DS.$this->predictionGame->id.'-prediction.kml';
$this->kmlfile = $this->predictionGame->id.'-prediction.kml';

?>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<?php

echo $this->loadTemplate('predictionheading');
echo $this->loadTemplate('sectionheader');

echo $this->loadTemplate('ranking');

if ($this->config['show_all_user_google_map'])
{
echo $this->loadTemplate('googlemap');
}

if ($this->config['show_help'])
{
    echo $this->loadTemplate('show_help');
}

?>
<div>
<?PHP
//backbutton
echo $this->loadTemplate('backbutton');
// footer
echo $this->loadTemplate('footer');
?>
</div>
<?PHP

?>
</div>
