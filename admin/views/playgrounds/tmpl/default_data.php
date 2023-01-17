<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playgrounds
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="1%"
                class="nowrap center hidden-phone"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="1%" class="nowrap center">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="10%" class="nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_NAME', 'v.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="hidden-phone">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_S_NAME', 'v.short_name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="hidden-phone">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CLUBNAME', 'club', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="center hidden-phone">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CAPACITY', 'v.max_visitors', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="hidden-phone">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="nowrap hidden-phone">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CITY'); ?>
            </th>
            <!-- <th width="1%" class="hidden-phone">
                    <?php //echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_LATITUDE'); ?>
                </th>
                <th width="1%" class="hidden-phone">
                    <?php //echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_LONGITUDE'); ?>
                </th>-->
            <th width="1%" class="hidden-phone">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_COUNTRY', 'v.country', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="5%" class="nowrap center hidden-phone">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'v.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'playgrounds.saveorder');
				?>
            </th>
            <th width="1%" class="nowrap center hidden-phone">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'v.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="11" class="center">
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
			$row        = &$this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=playground.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'playgrounds.', $canCheckin);
			?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="center hidden-phone">
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
                <td class="">
					<?php if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'playgrounds.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=playground.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>
					<?php //echo $checked;  ?>
					<?php //echo $row->name;  ?>
                    <div class="small">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></div>
                </td>
                <td class="center hidden-phone"><?php echo $row->short_name; ?></td>
                <td class="hidden-phone"><?php echo $row->club; ?></td>
                <td class="center hidden-phone">
                    <div class="badge"><?php echo $row->max_visitors; ?></div>
                </td>
                <td width="5%" class="center hidden-phone">
					<?php
					if ($row->picture == '')
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_NO_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', Uri::base() . '/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
					}
                    elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_DEFAULT_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', Uri::base() . '/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
						?>
                        <a href="<?php echo Uri::root() . $row->picture; ?>" title="<?php echo $imageTitle; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $row->picture; ?>" alt="<?php echo $imageTitle; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
                    elseif ($row->picture !== '')
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CUSTOM_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', Uri::base() . '/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, $image_attributes);
						?>
                        <a href="<?php echo Uri::root() . $row->picture; ?>" title="<?php echo $imageTitle; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $row->picture; ?>" alt="<?php echo $imageTitle; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>
                <td class="hidden-phone"><?php echo $row->city; ?></td>
                <!--                    <td class="hidden-phone"><?php //echo $row->latitude; ?></td>
                    <td class="hidden-phone"><?php //echo $row->longitude; ?></td>-->
                <td class="center hidden-phone"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
                <td class="order hidden-phone">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'playgrounds.orderup', 'JLIB_HTML_MOVE_UP', true); ?>
                        </span>
                    <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'playgrounds.orderdown', 'JLIB_HTML_MOVE_DOWN', true); ?>
                            <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                </td>
                <td class="center hidden-phone"><?php echo $row->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>

