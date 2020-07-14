<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teampersons
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewteampersons
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewteampersons extends sportsmanagementView
{

	/**
	 * sportsmanagementViewteampersons::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$this->restartpage = false;

		$this->project_id  = $this->app->getUserState("$this->option.pid", '0');
		$this->_persontype = $this->jinput->getVar('persontype');

		if (empty($this->_persontype))
		{
			$this->_persontype = $this->app->getUserState("$this->option.persontype", '0');
		}

		$this->project_team_id = $this->jinput->getVar('project_team_id');
		$this->team_id         = $this->jinput->getInt('team_id');
		$this->season_team_id         = $this->jinput->getInt('season_team_id');

		if (!$this->team_id)
		{
			$this->team_id = $this->app->getUserState("$this->option.team_id", '0');
		}

		if (!$this->project_team_id)
		{
			$this->project_team_id = $this->app->getUserState("$this->option.project_team_id", '0');
		}

		$mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$project    = $mdlProject->getProject($this->project_id);

		$this->season_id = $project->season_id;
		$items           = $this->model->PersonProjectPosition($this->project_id, $this->_persontype);

		if (!$items)
		{
			/**
			 * fehlen im projekt die positionen ?
			 * wenn ja, dann fehlende positionen hinzufügen
			 */
		}
		else
		{
			$this->restartpage = false;
		}

		$this->table = Table::getInstance('teamperson', 'sportsmanagementTable');
		$this->app->setUserState("$this->option.pid", $project->id);
		$this->app->setUserState("$this->option.season_id", $project->season_id);
		$this->app->setUserState("$this->option.project_art_id", $project->project_art_id);
		$this->app->setUserState("$this->option.sports_type_id", $project->sports_type_id);

		$mdlProjectTeam = BaseDatabaseModel::getInstance('ProjectTeam', 'sportsmanagementModel');
		$project_team   = $mdlProjectTeam->getProjectTeam($this->team_id);

		/**
		 * build the html options for position
		 */
		$position_id   = array();
		$position_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_FUNCTION'));
		$mdlPositions  = BaseDatabaseModel::getInstance('Positions', 'sportsmanagementModel');

		if ($this->_persontype == 1)
		{
			$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);
		}
		elseif ($this->_persontype == 2)
		{
			$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);
		}

		if ($project_ref_positions)
		{
			$position_id = array_merge($position_id, $project_ref_positions);
		}

		$lists                        = array();
		$lists['project_position_id'] = $position_id;
		unset($position_id);

		/**
		 * build the html options for nation
		 */
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation              = array_merge($nation, $res);
			$this->search_nation = $res;
		}

		$lists['nation']    = $nation;
		$this->lists        = $lists;
		$this->project      = $project;
		$this->project_team = $project_team;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->app->setUserState("$this->option.pid", $this->project_id);
		$this->app->setUserState("$this->option.project_team_id", $this->project_team_id);
		$this->app->setUserState("$this->option.team_id", $this->team_id);
		$this->app->setUserState("$this->option.persontype", $this->_persontype);
		$this->app->setUserState("$this->option.season_id", $this->season_id);

		if ($this->_persontype == 1)
		{
			$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE') . ' ' . $this->project_team->name;
		}
		elseif ($this->_persontype == 2)
		{
			$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE') . ' ' . $this->project_team->name;
		}

		ToolbarHelper::apply('teampersons.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_APPLY'));
		ToolbarHelper::divider();
		sportsmanagementHelper::ToolbarButton('assignpersons', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN'), 'players', 0);
		ToolbarHelper::apply('teampersons.assignplayerscountry', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN_COUNTRY'));
		ToolbarHelper::divider();
		ToolbarHelper::back('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK', 'index.php?option=' . $this->option . '&view=projectteams&pid=' . $this->project_id . '&id=' . $this->project_id);
		parent::addToolbar();
	}

}

