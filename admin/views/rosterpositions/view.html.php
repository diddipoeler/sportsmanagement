<?php
/**
 * SportsManagement administrator roster-positions list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\RosterpositionTable;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewrosterpositions extends sportsmanagementView
{
    public function init()
    {
        $this->table = new RosterpositionTable($this->model->getDatabase());
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_TITLE');
        ToolbarHelper::custom('rosterpositions.addhome', 'new', 'new', Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_HOME'), false);
        ToolbarHelper::custom('rosterpositions.addaway', 'new', 'new', Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_AWAY'), false);
        ToolbarHelper::editList('rosterposition.edit');
        parent::addToolbar();
    }
}
