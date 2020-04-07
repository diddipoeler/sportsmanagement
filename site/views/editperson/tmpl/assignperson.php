<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       assignperson.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 * @subpackage editperson
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div>
	<form action="index.php" method="post" id="adminForm">
		<div id="editcell">
			<fieldset class="adminform">
				<legend>
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN_DESCR2');
		?>
				</legend>
				<table class="adminform">
					<tr>
						<td>
		<?php
		echo $this->lists['projects'];
		?>
						</td>
					</tr>
		<?php
		if ($this->project_id)
		{
			?>
		   <tr>
		  <td>
			<?php
			echo $this->lists['projectteams'];
			?>
		  </td>
		   </tr>
		   <tr>
		  <td>
		   <div class="button" style="text-align:left">
			<input    type="button" class="inputbox"
												onclick="window.top.document.forms.adminForm.elements.project_id.value = document.getElementById('prjid').value; window.top.document.forms.adminForm.elements.team_id.value = document.getElementById('xtid').value; window.top.document.forms.adminForm.elements.assignperson.value ='1'; window.parent.SqueezeBox.close();"
												value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN'); ?>" />
									</div>
								</td>
		 </tr>
			<?php
		}
		?>
				</table>
			</fieldset>
		</div>
		<div style="clear"></div>
		<input type="hidden" name="option" value="com_sportsmanagement" />
		<input type="hidden" name="view" value="person" />
		<input type="hidden" name="task" value="person.personassign" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
