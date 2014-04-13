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
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_gcalendar&view=gcalendars'); ?>" method="post" name="adminForm" id="adminForm">
<table class="table table-striped" id="eventList">
	<thead>
		<tr>
			<th width="1%" class="hidden-phone">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title">
				<?php echo JText::_('COM_GCALENDAR_FIELD_NAME_LABEL'); ?>
			</th>
			<th width="40px">
				<?php echo JText::_('COM_GCALENDAR_FIELD_COLOR_LABEL'); ?>
			</th>
			<th width="20%">
				<?php echo JText::_('COM_GCALENDAR_FIELD_CALENDAR_ID_LABEL'); ?>
			</th>
			<th width="20%">
				<?php echo JText::_('COM_GCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->items as $i => $item) {?>
		<tr class="row<?php echo $i % 2; ?>">
				<td class="center hidden-phone">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="nowrap has-context">
					<a href="<?php echo JRoute::_( 'index.php?option=com_gcalendar&task=gcalendar.edit&id='. $item->id ); ?>" title="<?php echo JText::_('JACTION_EDIT');?>">
						<?php echo $this->escape($item->name); ?>
					</a>
				</td>
				<td class="nowrap has-context"><div style="background-color: <?php echo jsmGCalendarUtil::getFadedColor($item->color);?>;width:40px;height:20px"></div></td>
				<td class="nowrap has-context">
					<?php echo urldecode($item->calendar_id); ?>
				</td>
				<td class="nowrap has-context">
					<?php
					if (!empty($item->magic_cookie)) {
						echo JText::_('COM_GCALENDAR_FIELD_MAGIC_COOKIE_LABEL');
					} else if(!empty($item->username)) {
						echo JText::_('COM_GCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION_USERNAME');
					} else {
						echo JText::_('COM_GCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION_NO');
					}
					?>
				</td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="6">
				<?php echo $this->pagination->getListFooter(); ?>
				<br/><br/>
			</td>
		</tr>
	</tfoot>
</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_GCALENDAR_FOOTER'), JRequest::getVar('GCALENDAR_VERSION'));?>
</div>