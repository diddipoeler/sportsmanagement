<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_alloverevents.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

//echo 'alloverevents <pre>'.print_r($this->alloverevents,true).'</pre>';
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatchalloverevents">
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_ALLOVEREVENTS'); ?></h4>
<table class="<?php echo $this->config['hystory_table_class']; ?>">
<?php          
foreach ( $this->alloverevents as $alloverevents )
{
?>
<tr>  
<td>
<?php
echo $alloverevents->team_name;
?>
</td>
 
<td>
<?php
echo sportsmanagementHelper::formatName(null, $alloverevents->firstname1, $alloverevents->nickname1, $alloverevents->lastname1, $this->config["name_format"]);
?>
</td>
  
<td>
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage(
											'nextmatchalloverevents' . $alloverevents->playerid ,
											$alloverevents->tppicture1,
											$alloverevents->lastname1,
											'20',
											'',
											$this->modalwidth,
											$this->modalheight,
											$this->overallconfig['use_jquery_modal']
										);
?>
</td>
</tr>
<tr> 
<td>
<table class="<?php echo $this->config['hystory_table_class']; ?>">
<thead>
<tr>
<?php
foreach ( $this->overallevents as $overallevents )
{
$width    = 20;
$height   = 20;
$type     = 4;
$imgTitle = Text::_($overallevents->name);
$icon     = sportsmanagementHelper::getPictureThumb($overallevents->icon, $imgTitle, $width, $height, $type);
?>
<td>
<?php
echo $icon;
?>
</td>                
<?php
}
?>
</tr>
</thead>
<?php
foreach ( $alloverevents->events as $key => $value )
{
?>
<td>
<?php
echo $value->event_sum;
?>
</td>                
<?php    
}

?>
</table>
</td>
</tr>  
<?php  
}          
?>          
</table>          
</div>