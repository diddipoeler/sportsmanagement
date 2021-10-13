<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editlineup_substitutions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<script type="text/javascript">

</script>
<!-- SUBSTITUTIONS START -->
<div id="io">
    <!-- Don't remove this "<div id"ajaxresponse"></div> as it is neede for ajax changings -->
    <div id="ajaxresponse">&nbsp;</div>
    <fieldset class="adminform">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_SUBST'); ?></legend>
        <table class='adminlist' id="table-substitutions">
            <thead>
            <tr>
                <th>
					<?php
					echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/out.png', Text::_('Out'));
					echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_OUT');
					?>
                </th>
                <th>
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_IN') . '&nbsp;';
					echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/in.png', Text::_('In'));
					?>
                </th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_POS'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_TIME'); ?></th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
			<?php
			$k = 0;
			if ($this->substitutions)
			{
				for ($i = 0; $i < count($this->substitutions); $i++)
				{
					$substitution = $this->substitutions[$i];
					?>
                    <tr id="sub-<?php echo $substitution->id; ?>" class="<?php echo "row$k"; ?>">
                        <td>
							<?php
							if ($substitution->came_in == 2)
							{
								echo sportsmanagementHelper::formatName(null, $substitution->firstname, $substitution->nickname, $substitution->lastname, 0);
							}
							else
							{
								echo sportsmanagementHelper::formatName(null, $substitution->out_firstname, $substitution->out_nickname, $substitution->out_lastname, 0);
							}
							?>
                        </td>
                        <td>
							<?php
							if ($substitution->came_in == 1)
							{
								echo sportsmanagementHelper::formatName(null, $substitution->firstname, $substitution->nickname, $substitution->lastname, 0);
							}
							?>
                        </td>
                        <td>
							<?php echo Text::_($substitution->in_position); ?>
                        </td>
                        <td>
							<?php
							$time = (!is_null($substitution->in_out_time) && $substitution->in_out_time > 0) ? $substitution->in_out_time : '--';
							echo $time;
							?>
                        </td>
                        <td>
                            <input id="deletesubst-<?php echo $substitution->id; ?>" type="button"
                                   class="inputbox button-delete-subst"
                                   onClick="deletesubst(<?php echo $substitution->id; ?>)"
                                   value="<?php echo Text::_('JTOOLBAR_REMOVE'); ?>"/>
                        </td>
                    </tr>
					<?php
					$k = (1 - $k);
				}
			}
			?>
            <tr id="row-new">
                <td><?php echo HTMLHelper::_('select.genericlist', $this->playersoptionsout, 'out', 'class="inputbox player-out"'); ?></td>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->playersoptionsin, 'in', 'class="inputbox player-in"'); ?></td>
                <td><?php echo $this->lists['projectpositions']; ?></td>
                <td><input type="text" size="3" id="in_out_time" name="in_out_time" class="inputbox"/></td>
                <td>
                    <input id="save-new-subst" type="button" class="inputbox button-save-subst"
                           value="<?php echo Text::_('JTOOLBAR_APPLY'); ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
<!-- SUBSTITUTIONS END -->
