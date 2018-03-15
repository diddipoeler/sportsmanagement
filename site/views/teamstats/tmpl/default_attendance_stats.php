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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
?>	
		<div class="clr"></div>
		<div class="jl_teamsubstats">
			<table width="50%" align="center" border="0" cellpadding="0" cellspacing="0">
				<tr class="sectiontableheader">
					<th colspan="2" class="le">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE');
						?>
					</th>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_TOTAL');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalattendance;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_PER_MATCH');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->averageattendance;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_BEST');
							?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->bestattendance;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_WORST');
							?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->worstattendance;
						?>
					</td>
				</tr>
			</table>
		</div>						

<div class="clr"></div>