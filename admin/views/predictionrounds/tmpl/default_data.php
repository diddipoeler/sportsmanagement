<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage predictionrounds
 * @file       default_data.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

?>
<div class="table-responsive">
    <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_LEGEND', '<i>' . $this->pred_project->name . '</i>'); ?></legend>
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="10">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="5">&nbsp;</th>

            <th width="10"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR', 'roundcode', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="10"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE', 'roundname', $this->sortDirection, $this->sortColumn); ?></th>

            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS', 's.rien_ne_va_plus', $this->sortDirection, $this->sortColumn); ?></th>

            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_WRONG_PREDICTION', 's.points_tipp', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_CORRECT_PREDICTION', 's.points_correct_result', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_CORRECT_MARGIN', 's.points_correct_diff', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_DRAW_DIFFERENCE', 's.points_correct_draw', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_CORRECT_TENDENCY', 's.points_correct_tendence', $this->sortDirection, $this->sortColumn); ?></th>

            <th width="10">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan="3"><?php echo $this->pagination->getResultsCounter(); ?></td>
        </tr>
        </tfoot>
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        =& $this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=predictionround.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'predictionrounds.', $canCheckin);
			?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="center"><?php echo HTMLHelper::_('grid.id', $i, $row->id); ?></td>
				<?php

				$inputappend = '';
				?>
                <td class="center">
					<?php
					if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'predictionrounds.', $canCheckin); ?>
					<?php endif; ?>
                    <a href="<?php echo $link; ?>">
						<?php
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_EDIT_DETAILS');
						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
							$imageTitle, 'title= "' . $imageTitle . '"'
						);
						?>
                    </a>
                </td>
				<?php

				?>

                <td class="center"><?php echo $row->roundcode; ?></td>
                <td class="center"><?php echo $row->roundname; ?></td>

                <td class="center"><?php
					$append = ' style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist',
						$this->lists['rien_ne_va_plus'],
						'rien_ne_va_plus' . $row->id,
						'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true"' . $append,
						'value', 'text', $row->rien_ne_va_plus
					);
                ?>
                </td>

                <td class="center">
                    <input tabindex="1" type="text" style="text-align: center" size="5"
                           class="form-control form-control-inline" name="points_tipp<?php echo $row->id; ?>"
                           value="<?php echo $row->points_tipp; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"/>
                </td>
                <td class="center">
                    <input tabindex="1" type="text" style="text-align: center" size="5"
                           class="form-control form-control-inline" name="points_correct_result<?php echo $row->id; ?>"
                           value="<?php echo $row->points_correct_result; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"/>
                </td>                
                <td class="center">
                    <input tabindex="1" type="text" style="text-align: center" size="5"
                           class="form-control form-control-inline" name="points_correct_diff<?php echo $row->id; ?>"
                           value="<?php echo $row->points_correct_diff; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"/>
                </td>                
                <td class="center">
                    <input tabindex="1" type="text" style="text-align: center" size="5"
                           class="form-control form-control-inline" name="points_correct_draw<?php echo $row->id; ?>"
                           value="<?php echo $row->points_correct_draw; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"/>
                </td>                
                <td class="center">
                    <input tabindex="1" type="text" style="text-align: center" size="5"
                           class="form-control form-control-inline" name="points_correct_tendence<?php echo $row->id; ?>"
                           value="<?php echo $row->points_correct_tendence; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"/>
                </td>

                <td class="center"><?php echo $row->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
