<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectteams
 * @file       changeteams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<button type="button" onclick="Joomla.submitform('projectteam.storechangeteams', this.form)">
				<?php echo Text::_('JSAVE');?></button>
<button id="cancel" type="button" onclick="Joomla.submitform('projectteam.cancelmodal', this.form)">
<?php echo Text::_('JCANCEL');?></button>  

		<legend>
	<?php
	echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGEASSIGN_TEAMS');
	?>
		</legend>
		<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th class="title"><?PHP echo Text::_(''); ?>
					</th>
					<th class="title"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGE'); ?>
					</th>
					<th class="title"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_OLD_TEAM'); ?>
					</th>
					<th class="title"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_NEW_TEAM'); ?>
					</th>
				</tr>
			</thead>

	<?PHP

	// $lfdnummer = 1;
	$k = 0;
	$i = 0;

	foreach ($this->projectteam as $row)
{
		$checked = HTMLHelper::_('grid.id', 'oldteamid' . $i, $row->id, $row->checked_out, 'oldteamid');
		$append = ' style="background-color:#bbffff"';
		$inputappend    = '';
		$selectedvalue = 0;
		?>
	 <tr class="<?php echo "row$k"; ?>">
	 <td class="center"><?php
				echo $i;
				?>
				</td>
				<td class="center"><?php
				echo $checked;
				?>
				</td>
				<td><?php
				echo $row->name;
				?>
				</td>
				<td class="nowrap" class="center"><?php
				echo HTMLHelper::_('select.genericlist', $this->lists['all_teams'], 'newteamid[' . $row->id . ']', $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cboldteamid' . $i . '\').checked=true"' . $append, 'value', 'text', $selectedvalue);
				?>
				</td>
	</tr>
	<?php
	$i++;
	$k = (1 - $k);
	}
	?>
		</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option"                value="com_sportsmanagement" />
	<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
