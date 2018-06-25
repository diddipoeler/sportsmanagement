<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      form_dfcday_row.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage results
 */

defined('_JEXEC') or die('Restricted access'); 

?>

<?php 
		$match = $this->game;
		$i = $this->i;
		$thismatch = JTable::getInstance('Match','sportsmanagementTable');
		$thismatch->bind(get_object_vars($match));

		list($datum,$uhrzeit) = explode(' ',$thismatch->match_date);

		if ( isset($this->teams[$thismatch->projectteam1_id]) )
        {
            $team1 = $this->teams[$thismatch->projectteam1_id];
            }
		if ( isset($this->teams[$thismatch->projectteam2_id]) )
        {
            $team2 = $this->teams[$thismatch->projectteam2_id];
            }
		
		$user = JFactory::getUser();

		if (isset($team1) && isset($team2))
		{
			$userIsTeamAdmin = ( $user->id == $team1->admin || $user->id == $team2->admin );
		}
		else
		{
			$userIsTeamAdmin = $this->isAllowed;
		}
		$teams = $this->teams;
		$teamsoptions[] = JHtml::_('select.option','0','- '.JText::_('Select Team').' -');
		foreach ($teams AS $team)
        {
            $teamsoptions[] = JHtml::_('select.option',$team->projectteamid,$team->name,'value','text');
            }
		
        $user = JFactory::getUser();
        $canEdit = $user->authorise('core.edit','com_sportsmanagement');
        $canCheckin = $user->authorise('core.manage','com_checkin') || $thismatch->checked_out == $user->get ('id') || $thismatch->checked_out == 0;
        $checked = JHtml::_('jgrid.checkedout', $i, $user->get ('id'), $thismatch->checked_out_time, 'matches.', $canCheckin);
        
        //$checked	= JHtml::_('grid.checkedout',$match,$i,'id');
		$published	= JHtml::_('grid.published',$match,$i);

		list($date,$time) = explode(" ",$match->match_date);
		$time = strftime("%H:%M",strtotime($time));
		?>
<tr id="result-<?php echo $match->id; ?>" class="">
	<td valign="top"><?php
	
	if ($thismatch->checked_out && $thismatch->checked_out != $my->id)
	{
		$db= JFactory::getDBO();
		$query="	SELECT username
				FROM #__users
				WHERE id=".$match->checked_out;
		$db->setQuery($query);
		$username=$db->loadResult();
		?>
		<acronym title="CHECKED OUT BY <?php echo $username; ?>">X</acronym>';
		<?php
	}
	else
	{
		?>
		<input type='checkbox' id='cb<?php echo $i; ?>' name='cid[]' value='<?php echo $thismatch->id; ?>' />
	</td>
	<!-- Edit match details -->
	<td valign="top">
<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,'edit',$team1->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');				
?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('edit'.$thismatch->id,'administrator/components/com_sportsmanagement/assets/images/edit.png',JText::_('COM_SPORTSMANAGEMENT_EDIT_MATCH_DETAILS_BACKEND'),'20',$url);        
?>		

	</td>
		<?php 
		if($this->project->project_type=='DIVISIONS_LEAGUE') {
		?>
	<td style="text-align:center; " >
		<?php echo $match->divhome; ?>
	</td>
		<?php 
		} 
		?>
	
	
	
	
	<!-- Edit home team -->
	<td align="center" class="nowrap" valign="top">
		<!-- Edit home line-up -->
		<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,'editlineup',$team1->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');
echo sportsmanagementHelperHtml::getBootstrapModalImage('home_lineup'.$team1->projectteamid,'administrator/components/com_sportsmanagement/assets/images/players_add.png',JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_HOME'),'20',$url);		
        ?>
        
        <!-- Edit home team -->
			<?php
		$append=' class="inputbox" size="1" onchange="document.getElementById(\'cb'.$i.'\').checked=true; " style="font-size:9px;" ';
		if ((!$userIsTeamAdmin) and (!$match->allowed)){$append .= ' disabled="disabled"';}
		if (!isset($team1->projectteamid)){$team1->projectteamid=0;}
		echo JHtml::_('select.genericlist', $teamsoptions, 'projectteam1_id'.$thismatch->id, $append, 'value', 'text', $team1->projectteamid);
		if ($this->config['results_below'])
		{
			?><br />
			<?php
		}
		else
		{
			?>
	</td>
	<!-- Edit away team -->
	<td nowrap='nowrap' align='center' valign='top'>
		<!-- Edit away team -->
		<?php
		}
		if (!isset($team2->projectteamid)){$team2->projectteamid=0;}
		echo JHtml::_('select.genericlist', $teamsoptions, 'projectteam2_id'.$thismatch->id, $append, 'value', 'text', $team2->projectteamid);
		?>
		<!-- Edit away line-up -->
		<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,'editlineup',$team2->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');        
echo sportsmanagementHelperHtml::getBootstrapModalImage('away_lineup'.$team2->projectteamid,'administrator/components/com_sportsmanagement/assets/images/players_add.png',JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_AWAY'),'20',$url);
		?>
	</td>
	<!-- Edit match results -->
	<?php
		if ($this->config['results_below'])
		{
			$partresults1 = explode(';',$thismatch->team1_result_split);
			$partresults2 = explode(';',$thismatch->team2_result_split);

			for ($x=0; $x<($this->project->game_parts); $x++)
			{
			?>
	<td align='center' valign='top'>
		<input type='text' style='font-size: 9px;' name='team1_result_split<?php echo $thismatch->id; ?>[]' size='2' tabindex='1' class='inputbox'
			value='<?php echo (isset($partresults1[$x])) ? $partresults1[$x] : ''; ?>' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " /> <br />
		<input type='text' style='font-size: 9px;' name='team2_result_split<?php echo $thismatch->id; ?>[]' size='2' tabindex='1' class='inputbox'
			value='<?php echo (isset($partresults2[$x])) ? $partresults2[$x] : ''; ?>' onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>
			<?php
			}

			if ($this->project->allow_add_time)
			{
			?>
	<td valign='top' align='center'>
		<span	id="ot<?php echo $thismatch->id; ?>" style="visibility:<?php echo ($thismatch->match_result_type > 0) ? 'visible' : 'hidden'; ?>">
			<input type="text" style="font-size: 9px;" name="team1_result_ot<?php echo $thismatch->id; ?>"
				value="<?php echo (isset($thismatch->team1_result_ot)) ? $thismatch->team1_result_ot : ''; ?>"
				size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
			<br />
			<input type="text" style="font-size: 9px;" name="team1_result_ot<?php echo $thismatch->id; ?>"
				value="<?php echo (isset($thismatch->team2_result_ot)) ? $thismatch->team2_result_ot : ''; ?>"
				size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		</span>
	</td>
			<?php
			}
			?>
	<td class="nowrap" valign="top" align="center">
		<input type="text" style="font-size: 9px;" name="team1_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_result; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<br />
		<input type="text" style="font-size: 9px;" name="team2_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_result; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
	</td>

			<?php
			if ($this->project->use_legs)
			{
			?>
	<td valign="top" align="center">
		<input type="text" style="font-size: 9px;" name="team1_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_legs; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<br />
		<input type="text" style="font-size: 9px;" name="team2_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_legs; ?>" size="2" tabindex="1" class="inputbox" />
	</td>
			<?php
			}
		}
		else
		{
		?>
	<td class="nowrap" align="right" valign="top">
		<input type="text" style="font-size: 9px;" name="team1_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_result; ?>" size="1" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<b>:</b>
		<input type="text" style="font-size: 9px;" name="team2_result<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_result; ?>" size="1" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		&nbsp; <?php echo $this->editPartResults($i,$thismatch); ?>
	</td>
			<?php
			if ($this->project->use_legs)
			{
			?>
	<td valign="top" align="center">
		<input type="text" style="font-size: 9px;" name="team1_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team1_legs; ?>" size="2" tabindex="1" class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; " />
		<b>:</b>
		<input type="text" style="font-size: 9px;" name="team2_legs<?php echo $thismatch->id; ?>"
			value="<?php echo $thismatch->team2_legs; ?>" size="2" tabindex="1" class="inputbox" />
	</td>
			<?php
			}
			if ($this->project->allow_add_time)
			{
			?>
	<td align='center' valign='top'><?php
		$xrounds=array();
		$xrounds[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_RESULTS_REGULAR_TIME'));
		$xrounds[]=JHtml::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME2'));
		$xrounds[]=JHtml::_('select.option','2',JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT2'));

		echo JHtml::_(	'select.genericlist', $xrounds, 'match_result_type'.$thismatch->id, 'class="inputbox" size="1" style="font-size:9px;"
				onchange="document.getElementById(\'cb'.$i.'\').checked=true;if (this.selectedIndex==0) $(\'ot'.$thismatch->id .
				'\').style.visibility=\'hidden\';else $(\'ot'.$thismatch->id.'\').style.visibility=\'visible\';"',
				'value', 'text', $thismatch->match_result_type);
		?>
	</td>
		<?php
		}
if ( $this->config['show_edit_match_events'] )
		{				
		?>
	<!-- Edit match events -->
	<td valign="top">
		<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,'editevents',$team1->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');        
		?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_events'.$thismatch->id,'administrator/components/com_sportsmanagement/assets/images/events.png',JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_BACKEND'),'20',$url);        
?>
	
    </td>
	<?php
	}
	if ( $this->config['show_edit_match_statistic'] )
	{
	?>
	<!-- Edit match statistics -->
	<td valign="top">
		<?php
$url = sportsmanagementHelperRoute::getEditLineupRoute(sportsmanagementModelResults::$projectid,$thismatch->id,'editstats',$team1->projectteamid,$datum,null,sportsmanagementModelResults::$cfg_which_database,sportsmanagementModelProject::$seasonid,sportsmanagementModelProject::$roundslug,0,'form');        
		?>
<!-- Button HTML (to Trigger Modal) -->
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('edit_statistics'.$thismatch->id,'administrator/components/com_sportsmanagement/assets/images/calc16.png',JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS_BACKEND'),'20',$url);        
?>

    </td>
	<?php
	}
	?>
	<!-- Published -->
	<td valign='top' style='text-align: center;'>
		<input type='checkbox' name='published<?php echo $thismatch->id; ?>' id='cbp<?php echo $thismatch->id; ?>'
		value='<?php echo ((isset($thismatch->published)&&(!$thismatch->published)) ? 0 : 1); ?>'
		<?php if ($thismatch->published){echo ' checked="checked" '; } ?>
		onchange="document.getElementById('cb<?php echo $i; ?>').checked=true; if(document.adminForm.cbp<?php echo $thismatch->id; ?>.value==0){document.adminForm.cbp<?php echo $thismatch->id; ?>.value=1;}else{document.adminForm.cbp<?php echo $thismatch->id; ?>.value=0;}" />
	</td>
	
	<?php
	}
	?>
<?php
}
?>
</tr>
