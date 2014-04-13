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

$calendar = $this->gcalendar;

JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_gcalendar&layout=edit&id='.(int) $calendar->id); ?>" method="post" name="adminForm" id="gcalendar-form">
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_GCALENDAR_VIEW_GCALENDAR_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field){ ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php }; ?>
			</ul>
		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_GCALENDAR_VIEW_GCALENDAR_ACCESS_CONTROL' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('acl') as $field){ ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php }; ?>
			</ul>
		</fieldset>
	</div>
	<div>
		<input type="hidden" name="task" value="gcalendar.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_GCALENDAR_FOOTER'), JRequest::getVar('GCALENDAR_VERSION'));?>
</div>