<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      edit.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
jimport('joomla.html.pane');
// Load the tooltip behavior.
HTMLHelper::_('behavior.tooltip');
//HTMLHelper::_('behavior.formvalidation');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

//echo ' person<br><pre>'.print_r($this->item,true).'</pre>'

//if (version_compare(JSM_JVERSION, '4', 'eq')) {
//    $uri = Uri::getInstance();   
//} else {
//    $uri = Factory::getURI();
//}

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
					<button type="button" onclick="Joomla.submitform('editmatch.apply', this.form);">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE');?></button>
					<button type="button" onclick="Joomla.submitform('editmatch.save', this.form);">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE');?></button>
					<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
						<?php echo Text::_('JCANCEL');?></button>
</div>
	<legend>
  <?php 

  ?>
  </legend>
  </fieldset>

<ul class="nav nav-tabs">
<li class="active"><a data-toggle="tab" href="#home"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS');?></a></li>
<li><a data-toggle="tab" href="#menu1"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_ALTDECISION');?></a></li>
<li><a data-toggle="tab" href="#menu2"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW');?></a></li>
<li><a data-toggle="tab" href="#menu3"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS');?></a></li>
<li><a data-toggle="tab" href="#menu4"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT');?></a></li>
<li><a data-toggle="tab" href="#menu5"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION');?></a></li>
<li><a data-toggle="tab" href="#menu6"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED');?></a></li>
</ul>
     
<div class="tab-content">
<div id="home" class="tab-pane fade in active"> 
<?PHP
echo $this->loadTemplate('matchdetails');
?>
</div>
<div id="menu1" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('altdecision');
?>
</div>
<div id="menu2" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('matchpreview');
?>
</div>
<div id="menu3" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('scoredetails');
?>
</div>
<div id="menu4" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('matchreport');
?>
</div>
<div id="menu5" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('matchrelation');
?>
</div>
<div id="menu6" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('matchextended');
?>
</div>

</div>
<?PHP


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
	<?php //echo HTMLHelper::_('form.token')."\n"; ?>

<input type="hidden" id="token" name="token" value="
<?php 
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo JSession::getFormToken();    
}
else
{    
echo JUtility::getToken(); 
}


?>" />
	
</form>
