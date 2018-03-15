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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$params = $this->form->getFieldsets('params');

//echo 'sportsmanagementViewMatch _display project_id<br><pre>'.print_r($this->project_id,true).'</pre>';

/**
 * Match Form
 *
 * @author diddipoeler
 * @package	 SportManagement
 * @since 0.1
 */
?>
<div id="matchdetails">
	
    <form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=match.edit&tmpl=component'); ?>" id="adminForm" method="post" name="adminForm" >
		<!-- Score Table START -->
		<?php
		//save and close 
		$close = JFactory::getApplication()->input->getInt('close',0);
		if($close == 1) {
			?><script>
			window.addEvent('domready', function() {
				$('cancel').onclick();	
			});
			</script>
			<?php 
		}
		?>
			<fieldset>
				<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('match.apply', this.form);">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('match.save', this.form);">
						<?php echo JText::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo JText::_('JCANCEL');?></button>
				</div>
				<div class="configuration" >
					<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_TITLE',$this->match->hometeam,$this->match->awayteam); ?>
				</div>
			</fieldset>
		<?php
		// focus matchreport tab when the match was already played
		$startOffset = 0;
		if (strtotime($this->match->match_date) < time() )
		{
			$startOffset = 4;
		}
        
        // welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Define tabs options for version of Joomla! 3.1
$tabsOptionsJ31 = array(
            "active" => "panel1" // It is the ID of the active tab.
        );

echo JHtml::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', $tabsOptionsJ31);
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel1', JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW'));
echo $this->loadTemplate('matchpreview');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel2', JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS'));
echo $this->loadTemplate('matchdetails');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel3', JText::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS'));
echo $this->loadTemplate('scoredetails');
echo JHtml::_('bootstrap.endTab');
//echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel4', JText::_('COM_SPORTSMANAGEMENT_TABS_ALTDECISION'));
//echo $this->loadTemplate('altdecision');
//echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel5', JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT'));
echo $this->loadTemplate('matchreport');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel6', JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION'));
echo $this->loadTemplate('matchrelation');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel7', JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'));
echo $this->loadTemplate('matchextended');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');    
    }
    else
    {
		echo JHtml::_('tabs.start','tabs', array('startOffset'=>$startOffset));
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW'), 'panel1');
		echo $this->loadTemplate('matchpreview');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS'), 'panel2');
		echo $this->loadTemplate('matchdetails');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS'), 'panel3');
		echo $this->loadTemplate('scoredetails');
		
		//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_ALTDECISION'), 'panel4');
		//echo $this->loadTemplate('altdecision');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT'), 'panel5');
		echo $this->loadTemplate('matchreport');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION'), 'panel6');
		echo $this->loadTemplate('matchrelation');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel7');
		echo $this->loadTemplate('matchextended');
		
// 		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHPICTURE'), 'panel8');
// 		echo $this->loadTemplate('matchpicture');
		
		echo JHtml::_('tabs.end');
	}	
		?>
		<!-- Additional Details Table END -->
		<div class="clr"></div>
		
		<input type="hidden" name="task" value="match.edit"/>
		<input type="hidden" name="close" id="close" value="0"/>
        <input type="hidden" name="id" id="close" value="<?php echo $this->item->id; ?>"/>
		<input type="hidden" name="component" value="" />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   