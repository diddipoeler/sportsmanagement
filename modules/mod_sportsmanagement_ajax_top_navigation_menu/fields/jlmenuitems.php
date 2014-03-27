<?php
/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * Session form field class
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
				JHTML::_('select.option', 'separator', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_SEPARATOR')),
				JHTML::_('select.option', 'calendar', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_CALENDAR')),
				JHTML::_('select.option', 'curve', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_CURVE')),
				JHTML::_('select.option', 'eventsranking', JText::_('MODULE_JLG_NAVIGATION_eventsranking')),
				JHTML::_('select.option', 'matrix', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_matrix')),
				JHTML::_('select.option', 'ranking', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_TABLE')),
				JHTML::_('select.option', 'referees', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_referees')),
				JHTML::_('select.option', 'results', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_RESULTS')),
				JHTML::_('select.option', 'resultsmatrix', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_resultsmatrix')),
				JHTML::_('select.option', 'resultsranking', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_TABLE_AND_RESULTS')),
				JHTML::_('select.option', 'roster', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_roster')),
				JHTML::_('select.option', 'rosteralltime', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_rosteralltime')),
				JHTML::_('select.option', 'stats', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_stats')),
				JHTML::_('select.option', 'statsranking', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_statsranking')),
				JHTML::_('select.option', 'clubinfo', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_clubinfo')),
				JHTML::_('select.option', 'clubplan', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_clubplan')),
        JHTML::_('select.option', 'teaminfo', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_teaminfo')),
        JHTML::_('select.option', 'teams', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_teams')),
				JHTML::_('select.option', 'teamplan', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_teamplan')),
				JHTML::_('select.option', 'teamstats', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_teamstats')),
                JHTML::_('select.option', 'jltournamenttree', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_jltournamenttree')),
				JHTML::_('select.option', 'treetonode', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_treetonode')),
                JHTML::_('select.option', 'jlallprojectrounds', JText::_('MODULE_JLG_NAVIGATION_NAVSELECT_jlallprojectrounds')),
				);
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
