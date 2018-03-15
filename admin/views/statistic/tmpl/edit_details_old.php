<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
?>
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_STAT_STAT' );
				?>
			</legend>
			<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getField('name')->label; ?></td>
					<td><?php echo $this->form->getField('name')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('sports_type_id')->label; ?></td>
					<td><?php echo $this->form->getField('sports_type_id')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('short')->label; ?></td>
					<td><?php echo $this->form->getField('short')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('alias')->label; ?></td>
					<td><?php echo $this->form->getField('alias')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('class')->label; ?></td>
					<td><?php echo $this->form->getField('class')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('published')->label; ?></td>
					<td><?php echo $this->form->getField('published')->input; ?></td>
				</tr>
				<tr>
		  			<td class="key">
		   				<label for="note">
			  				<?php
			  				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_STAT_NOTE' );
			  				?>
						</label>
		  			</td>
		  			<td>
							<input type="text" id="note" name="note" value="<?php echo $this->item->note; ?>" size="100"/>
		  			</td>
				</tr>
	    			
			</table>
		</fieldset>