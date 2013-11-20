<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
<!--
window.addEvent('domready', function()
{
	$('templateid').addEvent('change', function()
	{
		if (this.value)
		{
			$('importform').submit();
		}
	});
});
//-->
</script>
<div id="masterimport">
	<form method="post" name="importform" id="importform">
		<p>
			<?php
			echo JText::sprintf(	'JL_ADMIN_PTMPLS_INHERITS_SETTINGS',
									$this->predictiongame->name );
			?>
		</p>
		<p>
			<?php
			echo JText::_( 'JL_ADMIN_PTMPLS_OVERRIDES_SETTINGS' );
			?>
		</p>
		<?php
		echo $this->lists['mastertemplates'];
		?>
		<!--
		<input type='hidden' name='project_id'	value='<?php echo $this->projectws->id; ?>' />
		-->
		<input type='hidden' name='controller'	value='predictiontemplate' />
		<input type='hidden' name='task' 		value='masterimport' />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>