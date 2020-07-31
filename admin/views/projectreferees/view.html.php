<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectreferees
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Sportsmanagement Component
 *
 * @static
 * @package Sportsmanagement
 * @since   0.1
 */
class sportsmanagementViewprojectreferees extends sportsmanagementView
{

	/**
	 * sportsmanagementViewprojectreferees::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->state         = $this->get('State');
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn    = $this->state->get('list.ordering');

		$items      = $this->get('Items');
		$total      = $this->get('Total');
		$pagination = $this->get('Pagination');

		$table       = Table::getInstance('projectreferee', 'sportsmanagementTable');
		$this->table = $table;

		$this->_persontype = $this->jinput->get('persontype');

		if (empty($this->_persontype))
		{
			$this->_persontype = $this->app->getUserState("$this->option.persontype", '0');
		}

		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$mdlProject       = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$this->project          = $mdlProject->getProject($this->project_id);

		/** build the html options for position */
		$position_id[]         = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REFEREE_FUNCTION'));
		$mdlPositions          = BaseDatabaseModel::getInstance('Positions', 'sportsmanagementModel');
		$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);

		if ($project_ref_positions)
		{
			$position_id               = array_merge($position_id, $project_ref_positions);
			$this->project_position_id = $project_ref_positions;
		}

		$lists['project_position_id'] = $position_id;
		unset($position_id);

		$this->user       = Factory::getUser();
		$this->config     = Factory::getConfig();
		$this->lists      = $lists;
		$this->items      = $items;
		$this->pagination = $pagination;
        
        $this->filterForm    = $this->model->getFilterForm();
		$this->activeFilters = $this->model->getActiveFilters();
	
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->filterForm ,true).'</pre>' ), Log::NOTICE, 'jsmerror');		
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->activeFilters ,true).'</pre>' ), Log::NOTICE, 'jsmerror');

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->app->setUserState("$this->option.persontype", $this->_persontype);
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=project&layout=panel&id='.$this->project_id);
		ToolbarHelper::apply('projectreferees.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_APPLY'));
		sportsmanagementHelper::ToolbarButton('assignpersons', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_ASSIGN'), 'players', 3);
		parent::addToolbar();
	}

}

