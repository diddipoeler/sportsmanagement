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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldJLMenuItems
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldJLMenuItems extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'JLMenuItems';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array(
				JHTML::_('select.option', '', JText::_('JNONE')),
				JHTML::_('select.option', 'separator', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_SEPARATOR')),
				JHTML::_('select.option', 'calendar', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CALENDAR')),
				JHTML::_('select.option', 'curve', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CURVE')),
				JHTML::_('select.option', 'eventsranking', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_EVENTSRANKING')),
				JHTML::_('select.option', 'matrix', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_MATRIX')),
				JHTML::_('select.option', 'ranking', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE')),
				JHTML::_('select.option', 'referees', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_REFEREES')),
				JHTML::_('select.option', 'results', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTS')),
				JHTML::_('select.option', 'resultsmatrix', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTSMATRIX')),
				JHTML::_('select.option', 'resultsranking', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE_AND_RESULTS')),
				JHTML::_('select.option', 'roster', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTER')),
				JHTML::_('select.option', 'rosteralltime', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTERALLTIME')),
				JHTML::_('select.option', 'stats', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATS')),
				JHTML::_('select.option', 'statsranking', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATSRANKING')),
				JHTML::_('select.option', 'clubinfo', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBINFO')),
				JHTML::_('select.option', 'clubplan', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBPLAN')),
        JHTML::_('select.option', 'teaminfo', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMINFO')),
        JHTML::_('select.option', 'teams', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMS')),
				JHTML::_('select.option', 'teamplan', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMPLAN')),
				JHTML::_('select.option', 'teamstats', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTATS')),
                JHTML::_('select.option', 'jltournamenttree', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLTOURNAMENTTREE')),
				JHTML::_('select.option', 'treetonode', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TREETONODE')),
                JHTML::_('select.option', 'jlallprojectrounds', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLALLPROJECTROUNDS')),
                JHTML::_('select.option', 'jlxmlexports', JText::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_XMLEXPORT')),
				);
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
