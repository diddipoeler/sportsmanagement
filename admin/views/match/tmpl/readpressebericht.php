<?PHP
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       readpressebericht.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

if ($this->matchnumber)
{
	$lfdnummer = 0;
?>
<div class="container">
	<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

		<h2>Schiedsrichter</h2>
		<table id="csvplayers" class="table table-hover table-responsive" width="" border="" cellspacing="" cellpadding="" bgcolor="">
			<thead>
			<tr>
				<th class="">Schiedsrichter Position</th>
				<th class="">Vorname</th>
				<th class="">Nachname</th>
				<th class="">In der Datenbank?</th>
				<th class="">Projekt zugeordnet?</th>
				<th class="">Projektposition</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->csvreferees as $value)
			:
	?>
				<tr>
					<td>
						<?php echo $value->csv_position; ?>
						<input type='hidden' name='referee[<?php echo $lfdnummer; ?>]' value='<?php echo $value->csv_position; ?>'/>
						<input type='hidden' name='refereepersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->person_id; ?>'/>
						<input type='hidden' name='refereeprojectrefereeid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_referee_id; ?>'/>
					</td>
					<td>
						<?php echo $value->firstname; ?>
						<input type='hidden' name='refereefirstname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->firstname; ?>'/>
					</td>
					<td>
						<?php echo $value->lastname; ?>
						<input type='hidden' name='refereelastname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->lastname; ?>'/>
					</td>
					<td>
						<?php
						if ($value->person_id)
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', '', 'title= "' . '' . '"');
						}
						else
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', '', 'title= "' . '' . '"');
						}
						?>
					</td>
					<td>
						<?php
						if ($value->project_referee_id)
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', '', 'title= "' . '' . '"');
						}
						else
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', '', 'title= "' . '' . '"');
						}
						?>
					</td>
					<td>
						<?php
						$selectedvalue = 0;

						if ($value->project_position_id != 0)
						{
							$selectedvalue = $value->project_position_id;
						}

						echo HTMLHelper::_('select.genericlist', $this->lists['referee_project_position_id'], 'referee_project_position_id[' . $lfdnummer . ']', 'class="inputbox" size="1" ', 'value', 'text', $selectedvalue);
						?>
					</td>
				</tr>
				<?php
				$lfdnummer++;
			endforeach;
			?>
			</tbody>
		</table>

		<h2>Spieler</h2>
		<table id="csvplayers" class="table table-hover table-responsive" width="" border="" cellspacing="" cellpadding="" bgcolor="">
			<thead>
			<tr>
				<th class="">Spielnummer</th>
				<th class="">Vorname</th>
				<th class="">Nachname</th>
				<th class="">In der Datenbank?</th>
				<th class="">Projektteam zugeordnet?</th>
				<th class="">Projektposition</th>
				<th class="">Startaufstellung</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->csvplayers as $value)
			:
	?>
				<tr>
					<td><?php echo $value->nummer; ?>
						<input type='hidden' name='player[<?php echo $lfdnummer; ?>]' value='<?php echo $value->nummer; ?>'/>
						<input type='hidden' name='playerpersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->person_id; ?>'/>
						<input type='hidden' name='playerprojectpersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_person_id; ?>'/>
						<input type='hidden' name='playerprojectpositionid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_position_id; ?>'/>
						<input type='hidden' name='playerhinweis[<?php echo $lfdnummer; ?>]' value='<?php echo $value->hinweis; ?>'/>
					</td>
					<td><?php echo $value->firstname; ?>
						<input type='hidden' name='playerfirstname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->firstname; ?>'/>
					</td>
					<td><?php echo $value->lastname; ?>
						<input type='hidden' name='playerlastname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->lastname; ?>'/>
					</td>
					<td>
						<?php
						if ($value->person_id)
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', '', 'title= "' . '' . '"');
						}
						else
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', '', 'title= "' . '' . '"');
						}
						?>
					</td>
					<td>
						<?PHP
						if ($value->project_person_id)
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', '', 'title= "' . '' . '"');
						}
						else
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', '', 'title= "' . '' . '"');
						}
						?>
					</td>
					<td>
						<?php
						$selectedvalue = 0;

						if ($value->project_position_id != 0)
						{
							$selectedvalue = $value->project_position_id;
						}

						echo HTMLHelper::_('select.genericlist', $this->lists['player_project_position_id'], 'player_project_position_id[' . $lfdnummer . ']', 'class="inputbox" size="1" ', 'value', 'text', $selectedvalue);
						?>
					</td>
					<td>
						<?php
						echo HTMLHelper::_('select.genericlist', $this->lists['startaufstellung'], 'startaufstellung[' . $lfdnummer . ']', 'class="inputbox" size="1" ', 'value', 'text', $value->startaufstellung);
						?>
					</td>
				</tr>
				<?php $lfdnummer++;
			endforeach; ?>
			</tbody>
		</table>

		<h2>Mitarbeiterstab</h2>
		<table id="csvstaff" class="table table-hover table-responsive" width="" border="" cellspacing="" cellpadding="" bgcolor="">
			<thead>
			<tr>
				<th class="">Staff Position</th>
				<th class="">Vorname</th>
				<th class="">Nachname</th>
				<th class="">In der Datenbank ?</th>
				<th class="">Projektteam zugeordnet ?</th>
				<th class="">Projektposition</th>
			</tr>
			</thead>
			<tbody>

			<?php foreach ($this->csvstaff as $value)
			:
	?>
				<tr>
					<td>
						<?php echo $value->position; ?>
						<input type='hidden' name='staffprojectpersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_person_id; ?>'/>
						<input type='hidden' name='staffprojectpositionid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_position_id; ?>'/>
					</td>
					<td>
						<?php echo $value->firstname; ?>
						<input type='hidden' name='stafffirstname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->firstname; ?>'/>
					</td>
					<td>
						<?php echo $value->lastname; ?>
						<input type='hidden' name='stafflastname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->lastname; ?>'/>
					</td>
					<td>
						<?php
						if ($value->person_id)
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', '', 'title= "' . '' . '"');
						}
						else
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', '', 'title= "' . '' . '"');
						}
						?>
					</td>
					<td>
						<?PHP
						if ($value->project_person_id)
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', '', 'title= "' . '' . '"');
						}
						else
						{
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', '', 'title= "' . '' . '"');
						}
						?>
					</td>
					<td>
						<?php
						$selectedvalue = 0;

						if ($value->project_position_id != 0)
						{
							$selectedvalue = $value->project_position_id;
						}

						echo HTMLHelper::_('select.genericlist', $this->lists['staff_project_position_id'], 'staff_project_position_id[' . $lfdnummer . ']', 'class="inputbox" size="1" ', 'value', 'text', $selectedvalue);
						?>
					</td>
				</tr>
				<?php $lfdnummer++;
			endforeach; ?>
			</tbody>
		</table>

		<h2>Auswechslungen</h2>
		<table id="csvinout" class="table table-hover table-responsive" width="" border="" cellspacing="" cellpadding="" bgcolor="">
			<thead>
			<tr>
				<th class="">Spieler</th>
				<th class="">Minute</th>
				<th class="">Rückennummer</th>
				<th class="">für Nummer</th>
				<th class="">für Spieler</th>
				<th class="">Projektposition</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->csvinout as $value)
			:
	?>
				<tr>
					<td><?php echo $value->spieler; ?></td>
					<td><?php echo $value->in_out_time; ?></td>
					<td><?php echo $value->in; ?></td>
					<td><?php echo $value->out; ?></td>
					<td><?php echo $value->spielerout; ?></td>
					<td>
						<?php
						$selectedvalue = 0;
						echo HTMLHelper::_('select.genericlist', $this->lists['player_inout_project_position_id'], 'player_inout_project_position_id[' . $value->in . ']', 'class="inputbox" size="1" ', 'value', 'text', $selectedvalue);
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2>Karten</h2>
		<table id="csvcards" class="table table-hover table-responsive" width="" border="" cellspacing="" cellpadding="" bgcolor="">
			<thead>
			<tr>
				<th class="">Spieler</th>
				<th class="">Minute</th>
				<th class="">Karte</th>
				<th class="">Rückennummer</th>
				<th class="">Grund</th>
				<th class="">Event</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->csvcards as $value)
			:
	?>
			<tr>
				<td><?php echo $value->spieler; ?></td>
				<td><?php echo $value->event_time; ?></td>
				<td><?php echo ($value->event_name == Text::_($value->event_name)) ? $value->event_name : Text::_($value->event_name); ?></td>
				<td><?php echo $value->spielernummer; ?></td>
				<td><?php echo $value->notice; ?></td>
				<td>
					<?php
					$selectedvalue = 0;

					if ($value->event_type_id != 0)
					{
						$selectedvalue = $value->event_type_id;
					}

					echo HTMLHelper::_('select.genericlist', $this->lists['events'], 'project_events_id[' . $value->project_person_id . ']', 'class="inputbox" size="1" ', 'value', 'text', $selectedvalue);
					?>
				</td>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?PHP
}

		?>
		<input type='submit' class="btn btn-block btn-large" value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVE_PRESSEBERICHT'); ?>' onclick=''/>
		<input type="hidden" name="layout" value="savepressebericht"/>
		<input type="hidden" name="view" value="match"/>
		<input type="hidden" name="tmpl" value="component"/>
		<input type="hidden" name="match_id" value="<?php echo Factory::getApplication()->input->getInt('match_id', 0); ?>"/>
		<input type="hidden" name="season_id" value="<?php echo $this->projectws->season_id; ?>"/>
		<input type="hidden" name="fav_team" value="<?php echo $this->projectws->fav_team; ?>"/>
		<input type="hidden" name="projectteamid" value="<?php echo $this->projectteamid; ?>"/>
	</form>
</div>
