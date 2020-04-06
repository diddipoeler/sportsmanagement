<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       jlmenuitems.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldJLMenuItems
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldJLMenuItems extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'JLMenuItems';

    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     *
     * @since 11.1
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array(
        HTMLHelper::_('select.option', '', Text::_('JNONE')),
        HTMLHelper::_('select.option', 'separator', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_SEPARATOR')),
        HTMLHelper::_('select.option', 'calendar', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CALENDAR')),
        HTMLHelper::_('select.option', 'curve', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CURVE')),
        HTMLHelper::_('select.option', 'eventsranking', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_EVENTSRANKING')),
        HTMLHelper::_('select.option', 'matrix', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_MATRIX')),
        HTMLHelper::_('select.option', 'ranking', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE')),
        HTMLHelper::_('select.option', 'referees', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_REFEREES')),
        HTMLHelper::_('select.option', 'results', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTS')),
        HTMLHelper::_('select.option', 'resultsmatrix', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTSMATRIX')),
        HTMLHelper::_('select.option', 'resultsranking', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE_AND_RESULTS')),
        HTMLHelper::_('select.option', 'roster', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTER')),
        HTMLHelper::_('select.option', 'rosteralltime', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTERALLTIME')),
        HTMLHelper::_('select.option', 'stats', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATS')),
        HTMLHelper::_('select.option', 'statsranking', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATSRANKING')),
        HTMLHelper::_('select.option', 'clubinfo', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBINFO')),
        HTMLHelper::_('select.option', 'clubplan', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBPLAN')),
        HTMLHelper::_('select.option', 'teaminfo', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMINFO')),
        HTMLHelper::_('select.option', 'teams', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMS')),
        HTMLHelper::_('select.option', 'teamstree', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTREE')),
        HTMLHelper::_('select.option', 'treetonode', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TREETONODE')),
        HTMLHelper::_('select.option', 'teamplan', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMPLAN')),
        HTMLHelper::_('select.option', 'teamstats', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTATS')),
        HTMLHelper::_('select.option', 'jltournamenttree', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLTOURNAMENTTREE')),
        HTMLHelper::_('select.option', 'treetonode', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TREETONODE')),
        HTMLHelper::_('select.option', 'jlallprojectrounds', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLALLPROJECTROUNDS')),
        HTMLHelper::_('select.option', 'jlxmlexports', Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_XMLEXPORT')),
        );
        
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
