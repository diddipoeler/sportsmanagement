<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editlineup.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage match
 */

defined('_JEXEC') or die('Restricted access');

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('jquery.framework');
}

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

?>
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
<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='adminForm' method='post' style='display:inline' name='adminform' >
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('matches.saveroster', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="$('close').value=1; jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('matches.saveroster', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE',$this->teamname); ?>
		</div>
	</fieldset>
	<div class="clear"></div>
	<div id="lineup">
		<?php
        // welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Define tabs options for version of Joomla! 3.1
$tabsOptionsJ31 = array(
            "active" => "panel1" // It is the ID of the active tab.
        );

echo JHtml::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', $tabsOptionsJ31);
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel1', JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'));
echo $this->loadTemplate('players');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel2', JText::_('COM_SPORTSMANAGEMENT_TABS_SUBST'));
echo $this->loadTemplate('substitutions');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel3', JText::_('COM_SPORTSMANAGEMENT_TABS_STAFF'));
echo $this->loadTemplate('staff');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel4', JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS'));
echo $this->loadTemplate('players_trikot_numbers');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');    
    }
        else
    {
		// focus on players tab 
		$startOffset = 1;
		echo JHtml::_('tabs.start','tabs', array('startOffset'=>$startOffset));
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'), 'panel1');
		echo $this->loadTemplate('players');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_SUBST'), 'panel2');
		echo $this->loadTemplate('substitutions');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_STAFF'), 'panel3');
		echo $this->loadTemplate('staff');
		
        echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS'), 'panel4');
		echo $this->loadTemplate('players_trikot_numbers');
        
		echo JHtml::_('tabs.end');
        }
		?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="" />
        <input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
		<input type="hidden" name="close" id="close" value="0" />
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="changes_check" value="0" id="changes_check" />
		
		<input type="hidden" name="team" value="<?php echo $this->tid; ?>" id="team" />
		<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount"	/>
        
        
		<?php echo JHtml::_('form.token')."\n"; ?>
	</div>
</form>