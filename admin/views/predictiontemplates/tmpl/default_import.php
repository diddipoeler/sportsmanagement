<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_import.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiontemplates
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
			echo JText::sprintf(	'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_INHERITS_SETTINGS',
									$this->predictiongame->name );
			?>
		</p>
		<p>
			<?php
			echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_OVERRIDES_SETTINGS' );
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
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
</div>