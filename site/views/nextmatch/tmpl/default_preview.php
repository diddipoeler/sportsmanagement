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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- START of match preview -->
<?php

// workaround to support {jcomments (off|lock)} in match preview
// no comments are shown if {jcomments (off|lock)} is found in the match preview
$commentsDisabled = 0;

if (!empty($this->match->preview) && preg_match('/{jcomments\s+(off|lock)}/is', $this->match->preview))
{
	$commentsDisabled = 1;
}

if (!empty($this->match->preview))
{
	?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'); ?></h2>
<table class="table">
	<tr>
		<td><?php
		$preview = $this->match->preview;
		$preview = JHtml::_('content.prepare', $preview);

		if ($commentsDisabled) {
			$preview = preg_replace('#{jcomments\s+(off|lock)}#is', '', $preview);
		}

		echo $preview;
		?>
		</td>
	</tr>
</table>
<!-- END of match preview -->

<?php
}

