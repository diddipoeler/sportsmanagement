<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}
?>
<style>
.search-item {
    font:normal 11px tahoma,arial,helvetica,sans-serif;
    padding:3px 10px 3px 10px;
    border:1px solid #fff;
    border-bottom:1px solid #eeeeee;
    white-space:normal;
    color:#555;
}
.search-item h3 {
    display:block;
    font:inherit;
    font-weight:bold;
    color:#222;
}

.search-item h3 span {
    float: right;
    font-weight:normal;
    margin:0 0 5px 5px;
    width:100px;
    display:block;
    clear:none;
}
</style>
<script>

	var quickaddsearchurl = '<?php echo JURI::root();?>administrator/index.php?option=com_sportsmanagement&task=quickadd.searchreferee';

	function searchPlayer(val)
	{
        var s= document.getElementById("search");
        s.value = val;
        Joomla.submitform('', this.form)
	}
</script>
<?php
$uri=JURI::root();
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}
?>
<!-- <fieldset class="adminform"> -->
	<legend>
	<?php
	echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTREFEREES_QUICKADD_REFEREE');
	?>
	</legend>
	<form id="quickaddForm" action="<?php echo JURI::root(); ?>administrator/index.php?option=com_sportsmanagement&task=quickadd.addreferee" method="post">
	<input type="hidden" id="cpersonid" name="cpersonid" value="">
	<table>
		<tr>
			<td><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTREFEREES_QUICKADD_DESCR');?>:</td>
			<td><input type="text" name="quickadd" id="quickadd"  size="50" /></td>
			<td><input type="submit" name="submit" id="submit" value="<?php echo JText::_('Add');?>" /></td>
		</tr>
	</table>
	<?php echo JHtml::_('form.token'); ?>
	</form>
<!-- </fieldset> -->

<?PHP
if ( $this->items )
{
echo $this->loadTemplate('data');
}
else
{
echo '<div class="alert alert-no-items">';
echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
echo '</div>';    
}

?>
	<input type="hidden" name="search_mode"			value="<?php echo $this->lists['search_mode'];?>" id="search_mode" />
    <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   