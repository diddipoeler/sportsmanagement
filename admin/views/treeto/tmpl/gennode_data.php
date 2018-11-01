<?php 

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('behavior.tooltip');

?>

	<div>
		<fieldset class="adminform">
			<legend><?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE_GENERATENODE' ); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('generate') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
			<?php endforeach; ?>
				<li>
				<input type="submit" value="<?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_GENERATE'); ?>" />
				</li>
			</ul>
		</fieldset>
	</div>



