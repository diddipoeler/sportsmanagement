<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextfederations
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;


$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20" style="vertical-align: top; ">
                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);"/>
            </th>

            <th class="title" nowrap="nowrap" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_NAME', 'objassoc.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" nowrap="nowrap" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_SHORT_NAME', 'objassoc.short_name', $this->sortDirection, $this->sortColumn);
				?>
            </th>


            <th width="5"
                style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_FLAG'); ?></th>
            <th width="5" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ICON'); ?></th>

            <th width="" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'objassoc.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th width="85" nowrap="nowrap" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'objassoc.ordering', $this->sortDirection, $this->sortColumn);
				echo '<br />';
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'jlextfederations.saveorder');
				?>
            </th>
            <th width="20" style="vertical-align: top; ">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'objassoc.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="100%" class="center">
				<?php echo $this->pagination->getListFooter(); ?>
				<?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        =& $this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=jlextfederation.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'jlextfederations.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.jlextfederation.' . $row->id) && $canCheckin;
			?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="center">
					<?php
					echo $this->pagination->getRowOffset($i);
					?>
                </td>
                <td class="center">
					<?php
					echo HTMLHelper::_('grid.id', $i, $row->id);
					?>
                </td>
				<?php

				$inputappend = '';
				?>
                <td style="text-align:center; ">
					<?php if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'jlextfederations.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=jlextfederation.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>



					<?php //echo $checked;
					?>

					<?php //echo $row->name;
					?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></p>
                </td>
				<?php

				?>

                <td><?php echo $row->short_name; ?></td>

                <td style="text-align:center; ">
					<?php
					//            $path = Uri::root().$row->assocflag;
					//          $attributes='';
					//		      $html .= 'title="'.$row->name.'" '.$attributes.' />';
					//					echo $html;
					if (empty($row->assocflag) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $row->assocflag))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $row->assocflag;
						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/delete.png',
							$imageTitle, 'title= "' . $imageTitle . '"'
						);
					}
					else
					{
						?>
                        <a href="<?php echo Uri::root() . $row->assocflag; ?>" title="<?php echo $row->name; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $row->assocflag; ?>" alt="<?php echo $row->name; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>
                <td style="text-align:center; ">
					<?php
					//            $path = Uri::root().$row->assocflag;
					//          $attributes='';
					//		      $html .= 'title="'.$row->name.'" '.$attributes.' />';
					//					echo $html;
					if (empty($row->picture) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $row->picture))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $row->picture;
						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/delete.png',
							$imageTitle, 'title= "' . $imageTitle . '"'
						);
					}
					else
					{
						?>
                        <a href="<?php echo Uri::root() . $row->picture; ?>" title="<?php echo $row->name; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $row->picture; ?>" alt="<?php echo $row->name; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'jlextfederations.', $canChange, 'cb'); ?>
						<?php // Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'jlextfederations');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'jlextfederations');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>
                </td>
                <td class="order">
                            <span>
                                <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'jlextfederations.orderup', 'JLIB_HTML_MOVE_UP', 'objassoc.ordering'); ?>
                            </span>
                    <span>
                                <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'jlextfederations.orderdown', 'JLIB_HTML_MOVE_DOWN', 'objassoc.ordering'); ?>
                                <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                            </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                </td>
                <td style="text-align:center; "><?php echo $row->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
  
