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

GCalendarUtil::loadLibrary();

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$calendar = $this->gcalendar;
$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'gcalendar.cancel' || document.formvalidator.isValid(document.id('gcalendar-form'))) {
			Joomla.submitform(task, document.getElementById('gcalendar-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_gcalendar&layout=edit&id='.(int) $calendar->id); ?>" method="post" name="adminForm" id="gcalendar-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('COM_GCALENDAR_VIEW_GCALENDAR_DETAILS') : JText::sprintf('COM_GCALENDAR_VIEW_GCALENDAR_DETAILS', $this->item->id); ?></a></li>
			</ul>
			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="general">
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('name'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('name'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('calendar_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('calendar_id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('magic_cookie'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('magic_cookie'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('description'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('description'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('username'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('username'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('password'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('password'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('color'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('color'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<div class="span2">
			<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
				<div class="control-group">
					<div class="controls">
						<?php echo $this->form->getValue('title'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('access'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('access'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('access_content'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('access_content'); ?>
					</div>
				</div>
			</fieldset>
		</div>
		<!-- End Sidebar -->
	</div>
</form>

<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_GCALENDAR_FOOTER'), JRequest::getVar('GCALENDAR_VERSION'));?>
</div>