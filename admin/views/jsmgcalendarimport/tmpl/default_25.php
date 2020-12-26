<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

if (!is_array($this->onlineItems))
{
	echo 'No data found!';

	return;
}
?>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendars'); ?>" method="post"
      name="adminForm" id="adminForm">
    <table class="table table-striped" id="eventList">
        <thead>
        <tr>
            <th width="1%" class="hidden-phone">
<?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th class="title">
				<?php echo Text::_('COM_GCALENDAR_FIELD_NAME_LABEL'); ?>
            </th>
            <th width="20%">
				<?php echo Text::_('COM_GCALENDAR_FIELD_CALENDAR_ID_LABEL'); ?>
            </th>
            <th width="40px">
				<?php echo Text::_('COM_GCALENDAR_FIELD_COLOR_LABEL'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($this->onlineItems as $i => $item)
		{
			$isIncluded = false;
			foreach ($this->dbItems as $dbItem)
			{
				if ($dbItem->calendar_id == $item->calendar_id)
				{
					$isIncluded = true;
					break;
				}
			}
			if ($isIncluded)
			{
				continue;
			}
			?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center hidden-phone">
					<?php
					echo HTMLHelper::_('grid.id', $i, base64_encode(serialize(array('id' => $item->calendar_id, 'color' => $item->color, 'name' => $item->name))));
					?>
                </td>
                <td class="nowrap has-context">
					<?php echo $this->escape($item->name); ?>
                </td>
                <td class="nowrap has-context">
					<?php echo urldecode($item->calendar_id); ?>
                </td>
                <td class="nowrap has-context">
                    <div style="background-color: ;width:40px;height:20px"></div>
                </td>
            </tr>
		<?php } ?>
        <tr>
            <td colspan="5"><b><?php echo Text::_('COM_GCALENDAR_VIEW_IMPORT_LABEL_ALREADY_ADDED'); ?></b></td>
        </tr>
		<?php foreach ($this->dbItems as $i => $item) { ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center hidden-phone"></td>
                <td class="nowrap has-context">
					<?php echo $this->escape($item->name); ?>
                </td>
                <td class="nowrap has-context">
					<?php echo urldecode($item->calendar_id); ?>
                </td>
                <td class="nowrap has-context">
                    <div style="background-color: ;width:40px;height:20px"></div>
                </td>
            </tr>
		<?php } ?>
        </tbody>
    </table>
    <input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<div align="center" style="clear: both">
	<?php echo sprintf(Text::_('COM_GCALENDAR_FOOTER'), Factory::getApplication()->input->getVar('GCALENDAR_VERSION')); ?>
</div>
