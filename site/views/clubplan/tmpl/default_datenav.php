<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_datenav.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage clubplan
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="<?php echo $this->divclassrow;?>" id="clubplandatenav">
<form name="adminForm" id="adminForm" method="post">
<?php $dateformat="%d-%m-%Y"; ?>
<table class="table" >
<tr>
<td>
<?php
echo "".HTMLHelper::_('select.genericlist', $this->lists['fromteamart'], 'teamartsel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamartsel )."";
?>
</td>
<td>
<?PHP
echo "".HTMLHelper::_('select.genericlist', $this->lists['fromteamseasons'], 'teamseasonssel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamseasonssel )."";                
?>
</td>
</tr>
<tr>
<td>
<?php
echo HTMLHelper::calendar(sportsmanagementHelper::convertDate($this->startdate,1),'startdate','startdate',$dateformat);
?>
</td>
<td>
<?PHP
echo HTMLHelper::calendar(sportsmanagementHelper::convertDate($this->enddate,1),'enddate','enddate',$dateformat);
?>
</td>
<td>
<?PHP
echo "".HTMLHelper::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1" onchange="" ', 'value', 'text', $this->type )."";
?>
</td>
<td>
<input type="submit" class="<?PHP echo $this->config['button_style']; ?>" name="reload View" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FILTER'); ?>" />
</td>
<td>
<?php
if ( $this->club )
{
$picture = $this->club->logo_big;
if ( !sportsmanagementHelper::existPicture($picture) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder('logo_big');    
}
echo sportsmanagementHelperHtml::getBootstrapModalImage('clplan'.$this->club->id,
$picture,
$this->club->name,
'50',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
}
?>
</td>
</tr>
</table>
<?php echo HTMLHelper::_('form.token')."\n"; ?>
</form>
</div>
