<?php 
/** SportsManagement ein Programm zur Verwaltung f�e Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�n es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�ren
* ver�ntlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�h sein wird, aber
* OHNE JEDE GEW�RLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�leistung der MARKTF�IGKEIT oder EIGNUNG F� EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�tere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$app = JFactory::getApplication();
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">


<?PHP

// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}
?>
<div>
<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_QUICKADD_DESCR'); ?> 
</div>
<div>
<script type="text/javascript">
var teampicture = new Array;
<?php
foreach ( $this->lists['country_teams_picture'] as $key => $value )
{
if ( !$value )
{
$value = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");    
}    
echo 'teampicture['.($key).']=\''.$value."';\n";
}
?>
</script>
<?PHP
// some CSS
$this->document->addStyleDeclaration('
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}');
// string $opt - second parameter of formbehavior2::select2
// for details http://ivaynberg.github.io/select2/
$opt = ' allowClear: true,
   width: "50%",

   formatResult: function format(state) 
   {  
   var originalOption = state.element;
   var picture;
   picture = teampicture[state.id];
   if (!state.id) 
   return state.text; 


   return "<img class=\'item car\' src=\''. JURI::root() .'" + picture + "\' />" + state.text; 

   },
   
   escapeMarkup: function(m) { return m; }
';

JHtml::_('formbehavior2.select2', '.test1', $opt);
echo JHtml::_('select.genericlist',$this->lists['country_teams'],'team_id',
'style="width:225px;" class="test1" size="6"'.$append,'value','text',0);

?>
<input class="btn" type="submit" name="addteam" id="addteam" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_ADD');?>" /> 
</div>
<?PHP
if ( $this->project_art_id != 3 )
{

if ( $this->projectteam )
{
//Ordering allowed ?
$ordering=($this->sortColumn == 't.name');
echo $this->loadTemplate('teams');    
}
else
{
echo '<div class="alert alert-no-items">';
echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
echo '</div>';    
}

}
else
{
    
if ( $this->projectteam )
{    
//Ordering allowed ?
$ordering=($this->sortColumn == 't.lastname');    
echo $this->loadTemplate('persons');  
}
else
{
echo '<div class="alert alert-no-items">';
echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
echo '</div>';    
}
  
}

?>
<input type="hidden" name="task" value="" />
    <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />
    <input type="hidden" name="season_id" value="<?php echo $this->project->season_id; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP



echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   