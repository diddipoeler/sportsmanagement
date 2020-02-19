<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      editlist.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictionmembers
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_( 'behavior.tooltip' );

//save and close 
$close = Factory::getApplication()->input->getInt('close',0);
if($close == 1) {
	?><script>
	window.addEvent('domready', function() {
		$('cancel').onclick();	
	});
	</script>
	<?php 
}




?>
<!-- import the functions to move the events between selection lists  -->
<?php

?>


<form action="<?php echo Route::_('index.php?option=com_sportsmanagement');?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	
    <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('predictionmembers.save_memberlist', this.form)">
				<?php echo Text::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo Factory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo Text::_('JCANCEL');?></button>
		</div>
		
	</fieldset>
    
    <div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
				echo Text::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_TITLE', '<i>' . $this->prediction_name . '</i>');
				?>
			</legend>
			
			
			
			<table class="admintable" border="0">
				<tr>
					<td>
						<b>
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_AVAIL_MEMBERS' );
							?>
						</b><br />
						<?php
                        if ( isset($this->lists['members']) )
                        {
						echo $this->lists['members'];
                        }
                        else
                        {
                            
                        }
						?>
					</td>
					<td style="text-align:center; ">
						&nbsp;&nbsp;
						<input	type="button" class="inputbox"
								onclick="move_list_items('members','prediction_members');jQuery('#prediction_members option').prop('selected', true);"
								value="&gt;&gt;" />
						&nbsp;&nbsp;<br />&nbsp;&nbsp;
					 	<input	type="button" class="inputbox"
					 			onclick="move_list_items('prediction_members','members');jQuery('#prediction_members option').prop('selected', true);"
								value="&lt;&lt;" />
						&nbsp;&nbsp;
					</td>
					<td>
						<b>
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_PROJ_MEMBERS' );
							?>
						</b><br />
						<?php
						echo $this->lists['prediction_members'];
						?>
					</td>
			   </tr>
			</table>
		</fieldset>
		<div class="clr"></div>

		<input type='hidden' name='task' value='' />
        <input type="hidden" name="option"				value="com_sportsmanagement" />
		<input type="hidden" name="cid"				value="<?php echo $this->prediction_id; ?>" />
        <input type="hidden" name="component" value="com_sportsmanagement" />
		<?php echo HTMLHelper::_('form.token')."\n"; ?>
	</div>
</form>