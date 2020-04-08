<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
jimport('joomla.html.pane');

// HTMLHelper::_('behavior.tooltip');
// HTMLHelper::_('behavior.formvalidation');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<script type="text/javascript">
//	Joomla.submitbutton = function(task)
//	{
//		if (document.formvalidator.isValid(document.id('editperson'))) {
//			Joomla.submitform(task, document.getElementById('editperson'));
//		}
//	}
</script>

<script type="text/javascript">
<!--
//window.addEvent('domready', function() {
jQuery(document).ready(function() {  
	// altered decision fields management
	toggle_altdecision();
	//jQuery('alt_decision').addEvent('change', toggle_altdecision);
  
	jQuery('#alt_decision').change(function() {
  toggle_altdecision() ;
});
  
});

function toggle_altdecision()
{
	if ( jQuery('#alt_decision').val() == 0)
	{
	//jQuery('alt_decision_enter').style.display='none';
	jQuery("#alt_decision_enter").css("display", "none");
	jQuery('#team1_result_decision').disabled = true;
	jQuery('#team2_result_decision').disabled = true;
	jQuery('#decision_info').disabled = true;
	}
	else
	{
	//jQuery('alt_decision_enter').style.display='block';
	jQuery("#alt_decision_enter").css("display", "block");
	jQuery('#team1_result_decision').disabled = false;
	jQuery('#team2_result_decision').disabled = false;
	jQuery('#decision_info').disabled = false;
	}
}

//-->
</script>

<form name="editmatch" id="editmatch" method="post" action="<?php echo $this->uri->toString(); ?>">
<?php

?>
<fieldset class="adminform">
<div class="fltrt">
<button type="button" onclick="Joomla.submitform('editmatch.saveshort', this.form);">
<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE');?></button>
<!--
<button type="button" onclick="Joomla.submitform('editmatch.save', this.form);">
<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE');?></button>
-->
<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
<?php echo Text::_('JCANCEL');?></button>
</div>
<legend>
<?php

?>
</legend>
</fieldset>
<?php
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'home'));
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'home', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS', true));
echo $this->loadTemplate('matchdetails');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu1', Text::_('COM_SPORTSMANAGEMENT_TABS_ALTDECISION', true));
echo $this->loadTemplate('altdecision');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu2', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW', true));
echo $this->loadTemplate('matchpreview');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu3', Text::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS', true));
echo $this->loadTemplate('scoredetails');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu4', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT', true));
echo $this->loadTemplate('matchreport');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu5', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION', true));
echo $this->loadTemplate('matchrelation');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu6', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED', true));
echo $this->loadTemplate('matchextended');
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.endTabSet');
?>
<div class="clr"></div>

<input type='hidden' name='option' value='com_sportsmanagement' />
<input type='hidden' name='view' value='editmatch' />
<input type='hidden' name='layout' value='edit' />
<input type='hidden' name='oldlayout' value='<?php echo sportsmanagementModelEditMatch::$oldlayout; ?>' />
<input type='hidden' name='tmpl' value='component' />
<input type='hidden' name='cfg_which_database' value='<?php echo sportsmanagementModelEditMatch::$cfg_which_database; ?>' />
<input type='hidden' name='s' value='<?php echo sportsmanagementModelEditMatch::$seasonid; ?>' />
<input type='hidden' name='p' value='<?php echo sportsmanagementModelEditMatch::$projectid; ?>' />
<input type='hidden' name='r' value='<?php echo sportsmanagementModelEditMatch::$roundid; ?>' />
<input type='hidden' name='divisionid' value='<?php echo sportsmanagementModelEditMatch::$divisionid; ?>' />
<input type='hidden' name='mode' value='<?php echo sportsmanagementModelEditMatch::$mode; ?>' />
<input type='hidden' name='order' value='<?php echo sportsmanagementModelEditMatch::$order; ?>' />
<input type='hidden' name='task' value='editmatch.saveshort' />
<input type='hidden' name='matchid' value='<?php echo $this->match->id; ?> ' />  
<input type='hidden' name='sel_r' value='<?php echo sportsmanagementModelEditMatch::$roundid; ?>' />
<input type='hidden' name='Itemid' value='<?php echo Factory::getApplication()->input->getInt('Itemid', 1, 'get'); ?>' />
<input type='hidden' name='boxchecked' value='0' id='boxchecked' />
<input type='hidden' name='checkmycontainers' value='0' id='checkmycontainers' />
<input type='hidden' name='save_data' value='1' class='button' />
<?php echo HTMLHelper::_('form.token'); ?>
</form>
