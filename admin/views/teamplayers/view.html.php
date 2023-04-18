<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplayers
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewteamplayers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewteamplayers extends sportsmanagementView
{

	/**
	 * sportsmanagementViewteamplayers::init()
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
		
		$this->items = $this->model->getprojectpublished($this->items);
		$this->items = $this->model->getprojectposition($this->items);

		$this->project_team_id = $this->jinput->getVar('project_team_id');
		$this->team_id = $this->jinput->getInt('team_id');
		$this->season_team_id = $this->jinput->getInt('season_team_id');

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

		$this->table = Table::getInstance('teamplayer', 'sportsmanagementTable');
$this->app->setUserState("$this->option.pid", $project->id);
$this->app->setUserState("$this->option.season_id", $project->season_id);
$this->app->setUserState("$this->option.project_art_id", $project->project_art_id);
$this->app->setUserState("$this->option.sports_type_id", $project->sports_type_id);
$this->app->setUserState("$this->option.project_id", $this->project_id);
$this->app->setUserState("$this->option.persontype", $this->_persontype);
$this->app->setUserState("$this->option.project_team_id", $this->project_team_id);
$this->app->setUserState("$this->option.team_id", $this->team_id);
$this->app->setUserState("$this->option.season_team_id", $this->season_team_id);
		
Factory::getApplication()->input->set("pid", $project->id);
Factory::getApplication()->input->set("season_id", $project->season_id);
Factory::getApplication()->input->set("project_art_id", $project->project_art_id);
Factory::getApplication()->input->set("sports_type_id", $project->sports_type_id);
Factory::getApplication()->input->set("project_id", $this->project_id);
Factory::getApplication()->input->set("persontype", $this->_persontype);
Factory::getApplication()->input->set("project_team_id", $this->project_team_id);
Factory::getApplication()->input->set("team_id", $this->team_id);
Factory::getApplication()->input->set("season_team_id", $this->season_team_id);		
		
		
$values = array();
$values[] = $project->id;
$values[] = $project->season_id;
$values[] = $project->project_art_id;
$values[] = $project->sports_type_id;
$values[] = $this->project_id;
$values[] = $this->_persontype;
$values[] = $this->project_team_id;
$values[] = $this->team_id;
$values[] = $this->season_team_id;
Factory::getApplication()->input->set("teamplayers", $values);	
		
		
// Get the cookie
$value = Factory::getApplication()->input->cookie->get('teamplayers', null);
// Set the cookie
$time = time() + 604800; // 1 week
Factory::getApplication()->input->cookie->set('teamplayers', implode(";", $values), $time, Factory::getApplication()->get('cookie_path', '/'), Factory::getApplication()->get('cookie_domain'), Factory::getApplication()->isSSLConnection());		
		
		
		
		$mdlProjectTeam = BaseDatabaseModel::getInstance('ProjectTeam', 'sportsmanagementModel');
		$project_team   = $mdlProjectTeam->getProjectTeam($this->team_id);

		/** build the html options for position */
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

		/** build the html options for nation */
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

		if (!array_key_exists('search_mode', $this->lists))
		{
			$this->lists['search_mode'] = '';
		}
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

		ToolbarHelper::apply('teamplayers.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_APPLY'));
		ToolbarHelper::divider();
		sportsmanagementHelper::ToolbarButton('assignpersons', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN'), 'players', 0);
		sportsmanagementHelper::ToolbarButton('assignpersonsclub', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN_CLUB'), 'players', 0);
		ToolbarHelper::apply('teamplayers.assignplayerscountry', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN_COUNTRY'));
		ToolbarHelper::divider();
		ToolbarHelper::back('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK', 'index.php?option=' . $this->option . '&view=projectteams&pid=' . $this->project_id . '&id=' . $this->project_id);
		parent::addToolbar();
	}

}

