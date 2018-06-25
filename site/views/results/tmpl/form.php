<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      form.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage results
 */

// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

if (version_compare(JSM_JVERSION, '4', 'eq')) {
    $uri = JUri::getInstance();   
} else {
    $uri = JFactory::getURI();
}

if ( $this->overallconfig['use_jquery_modal'] )
{
?>
<!--
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>
-->
<?php 
}

if ( $this->overallconfig['use_squeezebox_modal'] ) 
{
	
}	

//echo ' matches'.'<pre>'.print_r($this->matches,true).'</pre>';

if ( !$this->showediticon )
{
	JFactory::getApplication()->redirect( str_ireplace('layout=form','',$uri->toString()), JText::_('ALERTNOTAUTH') );
}

// load javascripts
$document = JFactory::getDocument();
// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
//JHtml::_('behavior.framework', true);
//load data for editor
//require ( JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'html' . DS . 'editor.php' );
}
else
{
//JHtml::_( 'behavior.mootools' );  
//load data for editor
//require ( JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'html' . DS . 'editor.php' );    
  
}
//$version = urlencode(JoomleagueHelper::getVersion());
//$document->addScript(JURI::root().'components/com_sportsmanagement/assets/js/editmatch.js');
?>
<script>
//var str_delete = '<?php echo JText::_('JACTION_DELETE'); ?>';
//var baseajaxurl = '<?php echo JRoute::_(JURI::base().'index.php?option=com_sportsmanagement') ;?>';	
</script>
<div class="row-fluid" style="overflow:auto;">
	<!-- edit results start -->
	<table class="table table-responsive">
		<tr>
			<td class="contentheading">
				<?php
				if ( $this->roundid > 0 )
				{
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
			</td>
			<td>
            <?php 
            echo sportsmanagementHelperHtml::getRoundSelectNavigation(TRUE,sportsmanagementModelProject::$cfg_which_database); 
            ?>
            </td>
		</tr>
	</table>
	<form name="adminForm" id="adminForm" method="post" action="<?php echo $uri->toString(); ?>">
		<table class="<?php echo $this->config['table_class']; ?>  table-responsive" >
			<!-- Main START -->
			<?php
			if ( count( $this->matches ) > 0 )
			{
				$colspan=($this->project->allow_add_time) ? 15 : 14;
			?>
			<thead>
				<tr class="">
					<th width="20" style="vertical-align: top; ">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->matches); ?>);" />
					</th>
					<th width="" style="vertical-align: top; "></th>
                    <th style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></th>
					<?php 
						if($this->project->project_type=='DIVISIONS_LEAGUE') 
                        {
							$colspan++;
					?>
					<th style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DIVISION'); ?></th>
					<?php 
						}
					?>
					
					<?php
				if ( $this->config['show_edit_match_number'] )
				{
					?>
					<th width="20" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_MATCHNR'); ?></th>
					<?php
				}
				if ( $this->config['show_edit_match_date'] )
				{	
					?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DATE'); ?></th>
					<?php
				}
				if ( $this->config['show_edit_match_time'] )
				{
				?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_TIME'); ?></th>
					<?php
				}
				if ( $this->config['show_edit_match_time_present'] )
				{
				?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PRESENT'); ?></th>
					<?php
				}
				?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></th>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></th>
					<th style="text-align: center; vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT'); ?></th>
					<?php
					if ($this->project->allow_add_time)
					{
						?>
						<th style="text-align:center; vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT_TYPE'); ?></th>
						<?php
					}
					?>
					
					<?php
				if ( $this->config['show_edit_match_events'] )
				{
				?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS'); ?></th>
				<?php
				}
				if ( $this->config['show_edit_match_statistic'] )
				{
					?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS'); ?></th>
					<?php
				}
				if ( $this->config['show_edit_match_referees'] )
				{
					?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE'); ?></th>
					<?php
				}
					?>
					<th width="1%" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PUBLISHED'); ?></th>
				</tr>
			</thead>
			<!-- Start of the matches for the selected round -->
		<!--	<tbody> -->
			<?php
				$k = 0;
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
					echo $this->loadTemplate('row');
					}
					$k = 1 - $k;
					$i++;
				}
			}
			?>
		<!--	</tbody> -->
		</table>
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
		<!--
		<input type='submit' name='save' value='<?php echo JText::_('JSAVE' );?>' onclick="$('checkmycontainers').value=0; " />
		-->
		<input type='submit' name='save' value='<?php echo JText::_('JSAVE' );?>' />
		<?php echo JHtml::_('form.token'); ?>
		<!-- Main END -->
	</form>
</div>
<!-- matchdays pageNav -->
<br />
<div class='pagenav'>
<table width='96%' align='center' cellpadding='0' cellspacing='0'>
	<tr>
		<?php 
        //echo sportsmanagementPagination::pagenav($this->project); 
        ?>
	</tr>
</table>
<!-- matchdays pageNav END -->
</div>

<table class="not-playing" width='96%' align='center' cellpadding='0' cellspacing='0'>
	<tr>
		<td style='text-align:center; '>
			<?php echo $this->showNotPlayingTeams($this->matches, $this->teams, $this->config, $this->favteams, $this->project ); ?>
		</td>
	</tr>
</table>
