<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_homeaway_goals.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage teamstats
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>

<div class="<?php echo $this->divclassrow;?> table-responsive" id="homegoals">
			<table class="table">
			<thead>
				<tr class="sectiontableheader">
					<th colspan="2">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_HOME_STATS');
		?>
					</th>
				</tr>
			</thead>
			<tbody>                  
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_HOME_GAME_PERCENTAGE');
		?>:
					</td>
					<td class="statvalue">
		<?php
		if (!empty($this->totalrounds))
		{
			echo round(( $this->totalshome->totalmatches / $this->totalrounds ), 2);
		}
		else
		{
			echo "0";
		}
		?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_OVERALL');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalshome->totalmatches;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_PLAYED');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalshome->playedmatches;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalshome->totalgoals;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL_PER_MATCH');
		?>:
					</td>
					<td class="statvalue">
		<?php
		if (!empty($this->totalshome->playedmatches))
		{
			echo round(( $this->totalshome->totalgoals / $this->totalshome->playedmatches ), 2);
		}
		else
		{
			echo '0';
		}
		?>
					</td>
				</tr>
				<?php
				if ($this->config['home_away_stats'])
				{
		?>
					<tr class="sectiontableentry2">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR');
		?>
						</td>
						<td class="statvalue">
		<?php
		echo $this->totalshome->goalsfor;
		?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
		<?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR_PER_MATCH');?>:
						</td>
						<td class="statvalue">
		<?php
		if (!empty($this->totalshome->playedmatches))
		{
			echo round(( $this->totalshome->goalsfor / $this->totalshome->playedmatches ), 2);
		}
		else
		{
			echo '0';
		}
		?>
						</td>
					</tr>
					<tr class="sectiontableentry2">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST');
		?>
						</td>
						<td class="statvalue">
		<?php
		echo $this->totalshome->goalsagainst;
		?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST_PER_MATCH');
		?>:
						</td>
						<td class="statvalue">
		<?php
		if (!empty($this->totalshome->playedmatches))
		{
			echo round(( $this->totalshome->goalsagainst / $this->totalshome->playedmatches ), 2);
		}
		else
		{
			echo '0';
		}
		?>
						</td>
					</tr>
		<?php
				}
				?>
			</tbody>
			</table>
		</div>
		<div class="<?php echo $this->divclassrow;?> table-responsive" id="awaygoals">
			<table class="table">
			<thead>
				<tr class="sectiontableheader">
					<th colspan="2">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_AWAY_STATS');
		?>
					</th>
				</tr>
			</thead>
			<tbody>  
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_AWAY_GAME_PERCENTAGE');
		?>:
					</td>
					<td class="statvalue">
		<?php
		if (!empty($this->totalrounds))
		{
			echo round(( $this->totalsaway->totalmatches / $this->totalrounds ), 2);
		}
		else
		{
			echo "0";
		}
		?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_OVERALL');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalsaway->totalmatches;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_PLAYED');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalsaway->playedmatches;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalsaway->totalgoals;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL_PER_MATCH');
		?>:
					</td>
					<td class="statvalue">
		<?php
		if (!empty($this->totalsaway->playedmatches))
		{
			echo round(($this->totalsaway->totalgoals / $this->totalsaway->playedmatches), 2);
		}
		else
		{
			echo '0';
		}
		?>
					</td>
				</tr>

					<tr class="sectiontableentry2">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR');
		?>
						</td>
						<td class="statvalue">
		<?php
		echo $this->totalsaway->goalsfor;
		?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR_PER_MATCH');
		?>:
						</td>
						<td class="statvalue">
		<?php
		if (!empty($this->totalsaway->playedmatches))
		{
			echo round(( $this->totalsaway->goalsfor / $this->totalsaway->playedmatches ), 2);
		}
		else
		{
			echo '0';
		}
		?>
						</td>
					</tr>
					<tr class="sectiontableentry2">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST');
		?>
						</td>
						<td class="statvalue">
		<?php
		echo $this->totalsaway->goalsagainst;
		?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST_PER_MATCH');
		?>:
						</td>
						<td class="statvalue">
		<?php
		if (!empty($this->totalsaway->playedmatches))
		{
			echo round(( $this->totalsaway->goalsagainst / $this->totalsaway->playedmatches ), 2);
		}
		else
		{
			echo '0';
		}
		?>
						</td>
					</tr>
			</tbody>  
			</table>
		</div>

  
