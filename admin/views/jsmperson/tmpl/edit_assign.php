<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      edit_assign.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage person
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>		


			<fieldset class="adminform">
				<legend>
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN_DESCR');
					?>
				</legend>
				<table class="admintable" border="0">
					<tr>
						<td class="key">
							<label for="project_id">
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN_PID');
								?>
							</label>
						</td>
						<td>
							<input	class="text_area" type="text" name="project_id" id="project_id" value=""
									size="4" maxlength="5" />
						</td>
						<td colspan="2" rowspan="2">
							<div class="button2-left" style="display:inline">
								<div class="readmore">
									<?php
									//create the button code to use in form while selecting a project and team to assign a new person to
									$button = '<a class="modal-button" title="Select" ';
									$button .= 'href="index.php?option=com_sportsmanagement&view=person&task=person.personassign" ';
									$button .= 'rel="{handler: \'iframe\', size: {x: 600, y: 400}}">' . Text::_('Select') . '</a>';
									#echo $this->button;
									echo $button;
									?>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="team">
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN_TID');
								?>
							</label>
						</td>
						<td>
							<input	class="text_area" type="text" name="team_id" id="team_id" value="" size="4"
									maxlength="5" />
						</td>
					</tr>
				</table>
			</fieldset>