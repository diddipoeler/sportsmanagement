<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- START of match summary -->
<?php

// workaround to support {jcomments (off|lock)} in match summary
// no comments are shown if {jcomments (off|lock)} is found in the match summary
$commentsDisabled = 0;

if (!empty($this->match->summary) && preg_match('/{jcomments\s+(off|lock)}/is', $this->match->summary))
{
	$commentsDisabled = 1;
}

if (!empty($this->match->summary))
{
	?>
	<table width="100%" class="contentpaneopen">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY' );
				?>
			</td>
		</tr>
	</table>
	<table class="matchreport">
		<tr>
			<td>
			<?php
			$summary = $this->match->summary;
			$summary = JHtml::_('content.prepare', $summary);

			if ($commentsDisabled) {
				$summary = preg_replace('#{jcomments\s+(off|lock)}#is', '', $summary);
			}
			echo $summary;

			?>
			</td>
		</tr>
	</table>
	<?php
}

// Comments integration
if (!$commentsDisabled) {

	JPluginHelper::importPlugin( 'joomleague' );
	$dispatcher =& JDispatcher::getInstance();
	$comments = '';

	// get joomleague comments plugin params
	$plugin				= & JPluginHelper::getPlugin('joomleague', 'comments');
	if (is_object($plugin)) {
		$pluginParams = new JParameter($plugin->params);
	}
	else {
		$pluginParams = new JParameter('');
	}
	$separate_comments 	= $pluginParams->get( 'separate_comments', 0 );

	if ($separate_comments) {

	// Comments integration trigger when separate_comments in plugin is set to yes/1
		if ($dispatcher->trigger( 'onMatchReportComments', array( &$this->match, $this->team1->name .' - '. $this->team2->name, &$comments ) )) {
			echo $comments;
		}
	}
	else {
		// Comments integration trigger when separate_comments in plugin is set to no/0
		if ($dispatcher->trigger( 'onMatchComments', array( &$this->match, $this->team1->name .' - '. $this->team2->name, &$comments ) )) {
			echo $comments;
		}
	}
}

?>
<!-- END of match summary -->
