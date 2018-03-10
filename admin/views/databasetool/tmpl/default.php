<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

//echo 'task -> '.$this->task.'<br>';
//echo 'sm_tables -><pre>'.print_r(sportsmanagementModeldatabasetool::$jsmtables,true).'</pre><br>';

/*
<div class="progress-label">
*/
?>


  
      
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<p class="nowarning"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>
<div class="joomlaupdate_spinner" ></div>

<?PHP
if(version_compare(JVERSION,'3.0.0','ge')) 
{

if ( $this->bar_value < 100 )
{    
$div_class = 'progress progress-info progress-striped'; 
}
else
{
$div_class = 'progress progress-success progress-striped';     
}   
?>
<div class="<?php echo $div_class; ?>">
<div class="bar" style="width: <?php echo $this->bar_value; ?>%;"></div>

</div>
<?PHP
echo 'step -> '.$this->work_table.'<br>';

}
else 
{
?>
<div id="progressbar">
<div class="progress-label">
<?php echo $this->task.' - '.$this->work_table; ?>
</div>
</div>
<?PHP
//echo 'step -> '.$this->work_table.'<br>';
}
?>

<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
<input type="hidden" name="totals" value="<?php echo $this->totals; ?>" />


<?PHP


if ( $this->bar_value < 100)
{
echo '<meta http-equiv="refresh" content="1; URL='.$this->request_url.'">';
}
?>

</form>  