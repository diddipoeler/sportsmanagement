<?php


defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
$calendar = $this->gcalendar;

JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $calendar->id); ?>" method="post" name="adminForm" id="gcalendar-form">
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo Text::_( 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDAR_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field){ ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php }; ?>
			</ul>
		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo Text::_( 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDAR_ACCESS_CONTROL' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('acl') as $field){ ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php }; ?>
			</ul>
		</fieldset>
	</div>
	<div>
		<input type="hidden" name="task" value="jsmgcalendar.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

