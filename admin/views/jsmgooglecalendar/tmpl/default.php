<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die();

JFactory::getDocument()->addStyleSheet('components/com_sportsmanagement/views/jsmgooglecalendar/tmpl/default.css');   
JHtml::_('behavior.tooltip');

//JHtml::_('behavior.modal');
JHtml::_('behavior.modal', 'a.modal');

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<div style="width:500px;">
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_WELCOME'); ?></h2>
<p>
<?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_INTRO'); ?>
</p> 
<br>

<div id="cpanel" style="float:left">
    <div style="float:left;margin-right: 20px">
            <div class="icon">
                <a href="index.php?option=com_sportsmanagement&view=jsmgcalendars" >
                <img src="<?php echo JURI::base(true);?>/../administrator/components/com_sportsmanagement/assets/images/48-calendar.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_GCALENDARS'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login" >
                <img src="<?php echo JURI::base(true);?>/../administrator/components/com_sportsmanagement/assets/images/admin/import.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_sportsmanagement&view=jsmgcalendar&layout=edit" >
                <img src="<?php echo JURI::base(true);?>/../administrator/components/com_sportsmanagement/assets/images/admin/add.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_ADD'); ?></span>
                </a>
            </div>
            
            
    </div>
</div>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 