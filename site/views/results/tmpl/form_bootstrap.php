<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      form_bootstrap.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage results
 */
// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

$this->divclass = '';
$this->columns = 12;

?>
<div class="container-fluid">
<div class="row-fluid" style="">

<?php

/** 
 * welche bootstrap version
 */
if ( $this->overallconfig['use_bootstrap_version'] )
{
$this->divclass = "col-xs-".round((12 / 2));	
$this->divclass .= " col-sm-".round((12 / 2));	
$this->divclass .= " col-md-".round((12 / 2));	
$this->divclass .= " col-lg-".round((12 / 2));	
}
else
{
$this->divclass = "span".round((12 / 2));	
}	

				if ( $this->roundid > 0 )
				{
?>
<div class="<?php echo $this->divclass; ?>" style="">
<?PHP

					sportsmanagementHelperHtml::showMatchdaysTitle(JText::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS'), $this->roundid, $this->config );
					if ( $this->showediticon ) //Needed to check if the user is still allowed to get into the match edit
					{
					   $routeparameter = array();
$routeparameter['cfg_which_database'] = sportsmanagementModelProject::$cfg_which_database;
$routeparameter['s'] = sportsmanagementModelProject::$seasonid;
$routeparameter['p'] = sportsmanagementModelProject::$projectslug;
$routeparameter['r'] = sportsmanagementModelProject::$roundslug;
$routeparameter['division'] = sportsmanagementModelResults::$divisionid;
$routeparameter['mode'] = sportsmanagementModelResults::$mode;
$routeparameter['order'] = sportsmanagementModelResults::$order;
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
						$imgTitle = JText::_('COM_SPORTSMANAGEMENT_RESULTS_CLOSE_EDIT_RESULTS');
						$desc = JHtml::image('media/com_sportsmanagement/jl_images/edit_exit.png', $imgTitle, array(' title' => $imgTitle));
						echo '&nbsp;';
						echo JHtml::link($link, $desc);
					}
				}

				?>
</div>


<div class="<?php echo $this->divclass; ?>" style="">
<?php 
echo sportsmanagementHelperHtml::getRoundSelectNavigation(TRUE,sportsmanagementModelProject::$cfg_which_database); 
?>
</div>
</div>


<form name="adminForm" id="adminForm" method="post" action="<?php echo JFactory::getURI()->toString(); ?>">
<div class="row-fluid" style="">
<?php
/** 
 * welche bootstrap version
 */
if ( $this->overallconfig['use_bootstrap_version'] )
{
$this->divclass = "col-xs-".round((12 / $this->columns));	
$this->divclass .= " col-sm-".round((12 / $this->columns));	
$this->divclass .= " col-md-".round((12 / $this->columns));	
$this->divclass .= " col-lg-".round((12 / $this->columns));	
}
else
{
$this->divclass = "span".round((12 / $this->columns));	
}	
?>
<div class="<?php echo $this->divclass; ?>" style="">
<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->matches); ?>);" />
</div>
<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></div>

<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_MATCHNR'); ?></div>
<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DATE'); ?></div>
<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_TIME'); ?></div>
<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></div>
<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></div>
<div class="<?php echo $this->divclass; ?>" style=""><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT'); ?></div>

<!-- Start of the matches for the selected round -->
			<?php
				$i = 0;
				foreach( $this->matches as $match )
				{
					if ((isset($match->allowed)) && ($match->allowed))
					{
						$this->game = $match;
						$this->i = $i;
/**
 * eingabe laden
 */                        
					echo $this->loadTemplate('row_bootstrap');
					}

					$i++;
				}
			?>

</div>
<br/>
<input type='hidden' name='option' value='com_sportsmanagement' />
<input type='hidden' name='view' value='results' />
<input type='hidden' name='cfg_which_database' value='<?php echo sportsmanagementModelProject::$cfg_which_database; ?>' />
<input type='hidden' name='s' value='<?php echo sportsmanagementModelProject::$seasonid; ?>' />
<input type='hidden' name='p' value='<?php echo sportsmanagementModelResults::$projectid; ?>' />
<input type='hidden' name='r' value='<?php echo sportsmanagementModelProject::$roundslug; ?>' />
<input type='hidden' name='divisionid' value='<?php echo sportsmanagementModelResults::$divisionid; ?>' />
<input type='hidden' name='mode' value='<?php echo sportsmanagementModelResults::$mode; ?>' />
<input type='hidden' name='order' value='<?php echo sportsmanagementModelResults::$order; ?>' />
<input type='hidden' name='layout' value='form' />
<input type='hidden' name='task' value='results.saveshort' />
<input type='hidden' name='sel_r' value='<?php echo sportsmanagementModelProject::$roundslug; ?>' />
<input type='hidden' name='Itemid' value='<?php echo JFactory::getApplication()->input->getInt('Itemid', 1, 'get'); ?>' />
<input type='hidden' name='boxchecked' value='0' id='boxchecked' />
<input type='hidden' name='checkmycontainers' value='0' id='checkmycontainers' />
<input type='hidden' name='save_data' value='1' class='button' />
<input type='submit' name='save' value='<?php echo JText::_('JSAVE' );?>' />
<?php echo JHtml::_('form.token'); ?>
<!-- Main END -->
</form>


</div>
<?php



?>
