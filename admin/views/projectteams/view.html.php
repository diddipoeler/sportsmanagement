<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectteams
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewprojectteams
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewprojectteams extends sportsmanagementView
{

	/**
	 * sportsmanagementViewprojectteams::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$this->state         = $this->get('State');
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn    = $this->state->get('list.ordering');

		$this->division   = $this->jinput->request->get('division', 0, 'INT');
		$this->project_id = $this->jinput->request->get('pid', 0, 'INT');

		if (!$this->project_id)
		{
			$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		}
		$this->modelclub   = BaseDatabaseModel::getInstance('club', 'sportsmanagementModel');
		$mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$this->project    = $mdlProject->getProject($this->project_id);

		if ($this->project->fast_projektteam)
		{
			$lists['country_teams']         = $this->model->getCountryTeams();
			$lists['country_teams_picture'] = $this->model->getCountryTeamsPicture();
			$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_QUICKADD_DESCR');
		}

		$this->project_art_id         = $this->project->project_art_id;
		$this->season_id              = $this->project->season_id;
		$this->league_id              = $this->project->league_id;
		$this->sports_type_id         = $this->project->sports_type_id;
		$this->projectsbyleagueseason = $mdlProject->getProjectsbyCurrentProjectLeagueSeason($this->season_id, $this->league_id);
		$this->app->setUserState("$this->option.pid", $this->project->id);
		$this->app->setUserState("$this->option.season_id", $this->project->season_id);
		$this->app->setUserState("$this->option.project_art_id", $this->project->project_art_id);
		$this->app->setUserState("$this->option.sports_type_id", $this->project->sports_type_id);

		$starttime = microtime();
		$items     = $this->get('Items');

		$total      = $this->get('Total');
		$pagination = $this->get('Pagination');

		$this->table       = Table::getInstance('projectteam', 'sportsmanagementTable');

		if ($this->project_art_id == 3)
		{
			$filter_order = $this->app->getUserStateFromRequest($this->option . '.' . $this->model->_identifier . '.tl_filter_order', 'filter_order', 't.lastname', 'cmd');
		}
		else
		{
			$filter_order = $this->app->getUserStateFromRequest($this->option . '.' . $this->model->_identifier . '.tl_filter_order', 'filter_order', 't.name', 'cmd');
		}

		$mdlDivisions     = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
		$this->divisions = array();
		$this->divisions = $mdlDivisions->getDivisions($this->project_id);

		$divisionsList[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));

		if ($this->divisions)
		{
			$this->divisions = array_merge($divisionsList, $this->divisions);
		}

		$lists['divisions'] = $this->divisions;

		/** build the html select list for project assigned teams */
		$ress         = array();
		$res1         = array();
		$notusedteams = array();

		if ($ress = $this->model->getProjectTeams($this->project_id, false))
		{
			$teamslist = array();

			foreach ($ress as $res)
			{
				if (empty($res1->info))
				{
					$project_teamslist[] = JHtmlSelect::option($res->season_team_id, $res->text);
				}
				else
				{
					$project_teamslist[] = JHtmlSelect::option($res->season_team_id, $res->text . ' (' . $res->info . ')');
				}
			}

			$lists['project_teams'] = JHtmlSelect::genericlist(
				$project_teamslist, 'project_teamslist[]',
				' style="width:250px; height:300px;" class="inputbox" multiple="true" size="' . min(30, count($ress)) . '"',
				'value',
				'text'
			);
		}
		else
		{
			$lists['project_teams'] = '<select name="project_teamslist[]" id="project_teamslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		if ($ress1 = $this->model->getTeams())
		{
			if ($ress = $this->model->getProjectTeams($this->project_id, false))
			{
				foreach ($ress1 as $res1)
				{
					$used = 0;

					foreach ($ress as $res)
					{
						if ($res1->value == $res->season_team_id)
						{
							$used = 1;
						}
					}

					if ($used == 0 && !empty($res1->info))
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text . ' (' . $res1->info . ')');
					}
					elseif ($used == 0 && empty($res1->info))
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text);
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					if (empty($res1->info))
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text);
					}
					else
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text . ' (' . $res1->info . ')');
					}
				}
			}
		}
		else
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADD_TEAM'), 'Notice');
		}

		/** build the html select list for teams */
		if (count($notusedteams) > 0)
		{
			$lists['teams'] = JHtmlSelect::genericlist(
				$notusedteams,
				'teamslist[]',
				' style="width:250px; height:300px;" class="inputbox" multiple="true" size="' . min(30, count($notusedteams)) . '"',
				'value',
				'text'
			);
		}
		else
		{
			$lists['teams'] = '<select name="teamslist[]" id="teamslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		unset($res);
		unset($res1);
		unset($notusedteams);

		/** build the html options for nation */
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation              = array_merge($nation, $res);
			$this->search_nation = $res;
		}

		$lists['nation']   = $nation;
		$lists['nationpt'] = JHtmlSelect::genericlist(
			$nation,
			'filter_search_nation',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);

//		if ( $this->project->fast_projektteam )
//		{
//			$lists['country_teams']         = $this->model->getCountryTeams();
//			$lists['country_teams_picture'] = $this->model->getCountryTeamsPicture();
//		}
		$finaltablerank    = array();

		for ($a = 0; $a < 41; $a++)
		{
			$finaltablerank[] = HTMLHelper::_('select.option', $a, $a);
		}

		$lists['finaltablerank'] = $finaltablerank;

		/** build the html select list for all teams */
		$allTeams    = array();
		$all_teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));

		if ($allTeams = $this->model->getAllTeams($this->project_id))
		{
			$all_teams = array_merge($all_teams, $allTeams);
		}
		else
		{
			$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_CHANGE_TEAMS');	
		}

		$lists['all_teams'] = $all_teams;
		unset($all_teams);

		$myoptions            = array();
		$myoptions[]          = HTMLHelper::_('select.option', '0', Text::_('JNO'));
		$myoptions[]          = HTMLHelper::_('select.option', '1', Text::_('JYES'));
		$lists['is_in_score'] = $myoptions;
		$lists['use_finally'] = $myoptions;

		$this->modelmatches = BaseDatabaseModel::getInstance('Matches', 'sportsmanagementModel');

		$this->config         = Factory::getConfig();
		$this->lists          = $lists;
		$this->projectteam    = $items;
		$this->pagination     = $pagination;
		$this->project_art_id = $this->project_art_id;

/** pro division die punkte hinterlegen*/
if ($this->project->project_type == 'DIVISIONS_LEAGUE')
{
foreach ($this->projectteam as $teams)
{
$this->model->checkProjectTeamDivision($teams->projectteamid,$teams->id,$teams->project_id,$teams->team_id);			
}

}


/** Build the html options for nation */
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation              = array_merge($nation, $res);
			$this->search_nation = $res;
		}

		$lists['nation']  = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist(
			$nation,
			'filter_search_nation',
			$inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);
        
        
		switch ($this->getLayout())
		{
			case 'editlist';
			case 'editlist_3';
			case 'editlist_4';
				$this->setLayout('editlist');
				break;
			case 'changeteams';
			case 'changeteams_3';
			case 'changeteams_4';
				foreach ($this->projectteam as $teams)
				{
					$teams->name = $teams->name . ' (' . $teams->seasonname . ')';
				}
				$this->setLayout('changeteams');
				break;
		}
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
		$this->app->setUserState("$this->option.season_id", $this->season_id);
		$this->app->setUserState("$this->option.project_art_id", $this->project_art_id);
		$this->app->setUserState("$this->option.sports_type_id", $this->sports_type_id);

		/** Set toolbar items for the page */
		if ($this->project_art_id != 3)
		{
			$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE');
		}
		else
		{
			$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_TITLE');
		}

		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id);
		ToolbarHelper::custom('projectteams.setseasonid', 'purge.png', 'purge_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_SEASON_ID'), true);
		ToolbarHelper::custom('projectteams.matchgroups', 'purge.png', 'purge_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_MATCH_GROUPS'), true);
		ToolbarHelper::deleteList('', 'projectteams.delete');
		ToolbarHelper::apply('projectteams.saveshort');

		$layout = new FileLayout('changeteams', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
		$html   = $layout->render();
		Toolbar::getInstance('toolbar')->appendButton('Custom', $html, 'batch');
		$modal_params           = array();
		$modal_params['url']    = 'index.php?option=com_sportsmanagement&view=projectteams&layout=changeteams&tmpl=component&pid=' . $this->project_id;
		$modal_params['height'] = $this->modalheight;
		$modal_params['width']  = $this->modalwidth;
		$modal_params['modalWidth']  = '60';
		echo HTMLHelper::_('bootstrap.renderModal', 'collapseModalchangeTeams', $modal_params);

		$layout = new FileLayout('assignteams', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
		$html   = $layout->render();
		Toolbar::getInstance('toolbar')->appendButton('Custom', $html, 'batch');
		$modal_params           = array();
		$modal_params['url']    = 'index.php?option=com_sportsmanagement&view=projectteams&layout=editlist&tmpl=component&pid=' . $this->project_id;
		$modal_params['height'] = $this->modalheight;
		$modal_params['width']  = $this->modalwidth;
		$modal_params['modalWidth']  = '60';
		echo HTMLHelper::_('bootstrap.renderModal', 'collapseModalassignTeams', $modal_params);

		ToolbarHelper::custom('projectteam.copy', 'copy', 'copy', Text::_('JTOOLBAR_DUPLICATE'), true);
		ToolbarHelper::checkin('projectteams.checkin');
		ToolbarHelper::publish('projectteams.use_table_yes', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_YES', true);
		ToolbarHelper::unpublish('projectteams.use_table_no', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_NO', true);
		ToolbarHelper::publish('projectteams.use_table_points_yes', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_POINTS_YES', true);
		ToolbarHelper::unpublish('projectteams.use_table_points_no', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_POINTS_NO', true);
		ToolbarHelper::unpublish('projectteams.set_playground', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_PLAYGROUND', true);
		ToolbarHelper::unpublish('projectteams.set_playground_match', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_PLAYGROUND_MATCH', true);

		parent::addToolbar();

	}
}
