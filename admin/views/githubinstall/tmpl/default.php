<?php 
defined('_JEXEC') or die('Restricted access');

//echo $this->github_link;

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<p style='text-align:right;'><button type="button" onclick="Joomla.submitform('githubinstall.store')"><?php echo JText::_('JSAVE') ?></button></p>
<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>