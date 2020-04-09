<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editeventsbb_away.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;

?>
<fieldset class="adminform">
    <legend>
		<?php
		echo Text::_($this->teams->team2);
		?>
    </legend>
    <table class='adminlist'>
        <thead>
        <tr>
            <th style="text-align: left; width: 10px;"></th>
            <th style="text-align: left; "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_PERSON'); ?></th>
			<?php
			foreach ($this->events as $ev)
			{
				?>
                <th style="text-align: center;">
					<?php
					if (File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $ev->icon))
					{
						$imageTitle   = Text::sprintf('%1$s', Text::_($ev->text));
						$iconFileName = $ev->icon;
						echo HTMLHelper::_('image', $iconFileName, $imageTitle, 'title= "' . $imageTitle . '"');
					}
					else
					{
						echo Text::_($ev->text);
					}
					?>
                </th>
				<?php
			}
			?>
        </tr>
        </thead>
        <tbody>
		<?php
		$teap  = 0;
		$model = $this->getModel();

		for ($i = 0, $n = count($this->awayRoster); $i < $n; $i++)
		{
			$row =& $this->awayRoster[$i];

			if ($row->value == 0)
			{
				continue;
			}
			?>
            <tr id="row<?php echo $i; ?>">
                <td style="text-align: left;">
                    <input type="hidden" name="player_id_a_<?php echo $i; ?>" value="<?php echo $row->value; ?>"/>
                    <input type="hidden" name="team_id_a_<?php echo $i; ?>"
                           value="<?php echo $row->projectteam_id; ?>"/>
                    <input type="checkbox" id="cb_a<?php echo $i; ?>" name="cid_a<?php echo $i; ?>" value="cb_a"
                           onclick="isChecked(this.checked);"/>
                </td>
                <td style="text-align: left;">
					<?php echo '(' . Text::_($row->positionname) . ') - ' . sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 14); ?>
                </td>
				<?php
				// Total events away player
				$teap = 0;

				foreach ($this->events as $ev)
				{
					$teap++;
					$this->evbb = $model->getPlayerEventsbb($row->value, $ev->value, $this->item->id);
					?>
                    <td style="text-align: center; ">
                        <input type="hidden" name="event_type_id_a_<?php echo $i . '_' . $teap; ?>"
                               value="<?php echo $ev->value; ?>"/>
                        <input type="hidden" name="event_id_a_<?php echo $i . '_' . $teap; ?>"
                               value="<?php echo $this->evbb[0]->id; ?>"/>

                        <input type="text" size="1" class="inputbox" name="event_sum_a_<?php echo $i . '_' . $teap; ?>"
                               value="<?php echo(($this->evbb[0]->event_sum > 0) ? $this->evbb[0]->event_sum : ''); ?>"
                               title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_VALUE_SUM') ?>"
                               onchange="document.getElementById('cb_a<?php echo $i; ?>').checked=true"/>

                        <input type="text" size="2" class="inputbox" name="event_time_a_<?php echo $i . '_' . $teap; ?>"
                               value="<?php echo(($this->evbb[0]->event_time > 0) ? $this->evbb[0]->event_time : ''); ?>"
                               title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME') ?>"
                               onchange="document.getElementById('cb_a<?php echo $i; ?>').checked=true"/>

                        <input type="text" size="2" class="inputbox" name="notice_a_<?php echo $i . '_' . $teap; ?>"
                               value="<?php echo((strlen($this->evbb[0]->notice) > 0) ? $this->evbb[0]->notice : ''); ?>"
                               title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_MATCH_NOTICE') ?>"
                               onchange="document.getElementById('cb_a<?php echo $i; ?>').checked=true"/>
                        &nbsp;&nbsp;
                    </td>

					<?php
				}
				?>
            </tr>
			<?php
		}
		?>
        <input type="hidden" name="total_a_players" value="<?php echo $i; ?>"/>
        <input type="hidden" name="teap" value="<?php echo $teap; ?>"/>
        </tbody>
    </table>
</fieldset>
