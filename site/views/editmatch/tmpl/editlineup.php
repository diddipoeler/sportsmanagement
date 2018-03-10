<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editlineup.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');

$params = $this->form->getFieldsets('params');
//$document = JFactory::getDocument();
//$document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/diddioeler.js');
?>
<script type="text/javascript">

</script>

<?php

?>
<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='editlineup' method='post' style='display:inline' name='editlineup' >
<fieldset>
<div class="fltrt">
<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('editmatch.save', this.form);">
<?php echo JText::_('JSAVE');?></button>

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
?>    
<ul class="nav nav-tabs">
<li class="active"><a data-toggle="tab" href="#player"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS');?></a></li>
<li><a data-toggle="tab" href="#subst"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_SUBST');?></a></li>
<li><a data-toggle="tab" href="#staff"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_STAFF');?></a></li>
<li><a data-toggle="tab" href="#trikotnumber"><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS');?></a></li>
</ul>    

<div class="tab-content">
<div id="player" class="tab-pane fade in active"> 
<?PHP
echo $this->loadTemplate('players');
?>
</div>
<div id="subst" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('substitutions');
?>
</div>
<div id="staff" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('staff');
?>
</div>
<div id="trikotnumber" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('players_trikot_numbers');
?>
</div>
    
<?PHP    

    }
        else
    {
		// focus on players tab 
		$startOffset = 1;
		echo JHtml::_('tabs.start','tabs', array('startOffset'=>$startOffset));
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_SUBST'), 'panel1');
		echo $this->loadTemplate('substitutions');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'), 'panel2');
		echo $this->loadTemplate('players');
		
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
	<input type="hidden" name="p" value="<?php echo sportsmanagementModelEditMatch::$projectid; ?>" />
	
	<input type="hidden" name="r" value="<?php echo sportsmanagementModelEditMatch::$roundid; ?>" />
	<input type="hidden" name="s" value="<?php echo sportsmanagementModelEditMatch::$seasonid; ?>" />
	<input type="hidden" name="division" value="<?php echo sportsmanagementModelEditMatch::$divisionid; ?>" />
	<input type="hidden" name="cfg_which_database" value="<?php echo sportsmanagementModelEditMatch::$cfg_which_database; ?>" />
	
		<input type="hidden" name="close" id="close" value="0" />
		<input type="hidden" name="id" value="<?php echo $this->match->id; ?>" />
		<input type="hidden" name="changes_check" value="0" id="changes_check" />
		<input type="hidden" name="team" value="<?php echo $this->tid; ?>" id="team" />
		<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount"	/>
                
		<?php //echo JHtml::_('form.token')."\n"; ?>
        
<input type="hidden" id="token" name="token" value="
<?php 
//if(version_compare(JVERSION,'3.0.0','ge')) 
//{
echo JSession::getFormToken();    
//}
//else
//{    
//echo JUtility::getToken(); 
//}


?>" />	
        
	</div>
</form>
