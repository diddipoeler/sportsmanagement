<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smquotes
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

?>
<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>
            <th width="20">&nbsp;</th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JAUTHOR', 'obj.author', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JGLOBAL_ARTICLES', 'obj.author', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JCATEGORY', 'obj.catid', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'smquotes.saveorder');
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'pl.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="20">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="6"><?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan="3"><?php echo $this->pagination->getResultsCounter(); ?></td>
        </tr>
        </tfoot>
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        =& $this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=smquote.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'smquotes.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.smquote.' . $row->id) && $canCheckin;
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
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'smquotes.', $canCheckin); ?>
					<?php endif;

					if ($canEdit && !$row->checked_out) :
						?>
                        <a href="<?php echo $link; ?>">
							<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/edit.png',$imageTitle,$image_attributes);
							?>
                        </a>
					<?php
					endif;

					?>
                </td>
				<?php

				?>
                <td><?php echo $row->author; ?></td>

                <td>
					<?php
					if (empty($row->picture))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture;
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/delete.png',$imageTitle,$image_attributes);
					}
                    elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/information.png',$imageTitle,$image_attributes);
					}
					else
					{
						//$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
						//echo sportsmanagementHelper::getPictureThumb($row->picture, $playerName, 0, 21, 4);
						?>
                        <a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture; ?>"
                           title="<?php echo $row->name; ?>" class="modal">
                            <img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture; ?>"
                                 alt="<?php echo $row->name; ?>" width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>


                <td><?php echo $row->quote; ?></td>
                <td><?php echo $this->escape($row->category_title); ?></td>

                <td class="order">
                            <span>
                                <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'smquotes.orderup', 'JLIB_HTML_MOVE_UP', 'obj.ordering'); ?>
                            </span>
                    <span>
                                <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'smquotes.orderdown', 'JLIB_HTML_MOVE_DOWN', 'obj.ordering'); ?>
                                <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                            </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'smquotes.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'smquotes');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'smquotes');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>


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
  
