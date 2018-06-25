<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_playerstaffcareer.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage player
 */

defined('_JEXEC') or die('Restricted access');

// player staff start
if (count($this->historyPlayerStaff) > 0)
{
	?>
	<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER'); ?></h2>
	<table class="<?PHP echo $this->config['history_table_class']; ?> table-responsive" >
		<tr>
			<td>
				<table id="playerhistory" class="<?PHP echo $this->config['history_table_class']; ?> table-responsive" >
					<tr class="sectiontableheader">
						<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
						<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
						<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
						<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
					</tr>
					<?php
					$k=0;
					foreach ($this->historyPlayerStaff AS $station)
					{

 $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $station->project_slug;
       $routeparameter['tid'] = $station->team_slug;
       $routeparameter['pid'] = $station->person_slug;
            
                    $link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
						
						$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $station->project_slug;
       $routeparameter['tid'] = $station->team_slug;
       $routeparameter['ptid'] = 0;
       		$link2 = sportsmanagementHelperRoute::getSportsmanagementRoute('roster',$routeparameter);
						
						?>
						<tr class="">
							<td class="td_l"><?php echo JHtml::link($link1,$station->project_name); ?></td>
							<td class="td_l"><?php echo $station->season_name; ?></td>
							<td class="td_l">
							<?php 
							if ($this->config['show_staffcareer_teamlink'] == 1) {
								echo JHtml::link($link2,$station->team_name); 
							} else {
								echo $station->team_name;
							}
							?></td>
							<td class="td_l"><?php echo JText::_($station->position_name); ?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</table>
			</td>
		</tr>
	</table>

	<!-- PlayerStaff history END -->
	<?php
}
?>
