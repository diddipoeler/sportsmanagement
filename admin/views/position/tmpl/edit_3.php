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

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.html.bootstrap');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

// Define tabs options for version of Joomla! 3.0
$tabsOptions = array(
            "active" => "tab1_id" // It is the ID of the active tab.
        );  
        
?>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
 
<!-- This is a list with tabs names. -->
    	<ul class="nav nav-tabs" id="ID-Tabs-Group">
        	<li class="active">
        		<a data-toggle="tab" href="#tab1_id"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></a>
        	</li>
        	<li>
        		<a data-toggle="tab" href="#tab2_id"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_EVENTS'); ?></a>
    		</li>
            <li>
        		<a data-toggle="tab" href="#tab3_id"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_STATISTICS'); ?></a>
    		</li>

            
        </ul>	
 

<?php
echo JHtml::_('bootstrap.startPane', 'ID-Tabs-Group', $tabsOptions);

echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab1_id'); 
echo $this->loadTemplate('details');
echo JHtml::_('bootstrap.endPanel');

echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab2_id'); 
echo $this->loadTemplate('events');
echo JHtml::_('bootstrap.endPanel');

echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab3_id'); 
echo $this->loadTemplate('statistics');
echo JHtml::_('bootstrap.endPanel');

echo JHtml::_('bootstrap.endPane', 'ID-Tabs-Group');

?>	

	<div>
		<input type="hidden" name="task" value="position.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   