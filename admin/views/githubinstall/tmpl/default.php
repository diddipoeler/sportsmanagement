<?php 
defined('_JEXEC') or die('Restricted access');

//echo $this->github_link;

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<p style='text-align:right;'>

<input class='button' type='submit' value='<?php echo JText::_('JSAVE'); ?>' />
</p>
<input type="hidden" name="task"				value="githubinstall.store" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>