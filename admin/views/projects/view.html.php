<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projects
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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewProjects
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewProjects extends sportsmanagementView
{

	/**
	 * sportsmanagementViewProjects::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$inputappend = '';

		$starttime = microtime();

		Table::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tables');
		$table = Table::getInstance('project', 'sportsmanagementTable');
		$this->table = $table;

			  $javascript = "onchange=\"$('adminForm').submit();\"";

		// Build the html select list for userfields
		$userfields[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USERFIELD_FILTER'), 'id', 'name');
		$mdluserfields = BaseDatabaseModel::getInstance('extrafields', 'sportsmanagementModel');
		$alluserfields = $mdluserfields->getExtraFields('project');
		$userfields = array_merge($userfields, $alluserfields);

			  $this->userfields    = $alluserfields;

			  $lists['userfields'] = HTMLHelper::_(
				  'select.genericList',
				  $userfields,
				  'filter_userfields',
				  'class="inputbox" onChange="this.form.submit();" style="width:120px"',
				  'id',
				  'name',
				  $this->state->get('filter.userfields')
			  );
		unset($userfields);

		foreach ($this->items as $row)
		{
			$row->user_field = $mdluserfields->getExtraFieldsProject($row->id);
			$dest = JPATH_ROOT . '/images/com_sportsmanagement/database/projectimages/' . $row->id;

			if (Folder::exists($dest))
			{
			}
			else
			{
				$result = Folder::create($dest);
			}
		}

			  // Build the html select list for leagues
			$leagues[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER'), 'id', 'name');
			$mdlLeagues = BaseDatabaseModel::getInstance('Leagues', 'sportsmanagementModel');
			$allLeagues = $mdlLeagues->getLeagues();
			$leagues = array_merge($leagues, $allLeagues);

			  $this->league    = $allLeagues;

			  $lists['leagues'] = HTMLHelper::_(
				  'select.genericList',
				  $leagues,
				  'filter_league',
				  'class="inputbox" onChange="this.form.submit();" style="width:120px"',
				  'id',
				  'name',
				  $this->state->get('filter.league')
			  );
		unset($leagues);

			  // Build the html select list for sportstypes
		$sportstypes[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$mdlSportsTypes = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);

			  $this->sports_type    = $allSportstypes;

			  $lists['sportstype'] = $sportstypes;
		$lists['sportstypes'] = HTMLHelper::_(
			'select.genericList',
			$sportstypes,
			'filter_sports_type',
			'class="inputbox" onChange="this.form.submit();" style="width:120px"',
			'id',
			'name',
			$this->state->get('filter.sports_type')
		);
		unset($sportstypes);

			  // Build the html select list for seasons
		$seasons[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name');
		$mdlSeasons = BaseDatabaseModel::getInstance('Seasons', 'sportsmanagementModel');
		$allSeasons = $mdlSeasons->getSeasons();
		$seasons = array_merge($seasons, $allSeasons);

			  $this->season    = $allSeasons;

			  $lists['seasons'] = HTMLHelper::_(
				  'select.genericList',
				  $seasons,
				  'filter_season',
				  'class="inputbox" onChange="this.form.submit();" style="width:120px"',
				  'id',
				  'name',
				  $this->state->get('filter.season')
			  );

		unset($seasons);

			  // Build the html options for nation
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation = array_merge($nation, $res);
			$this->search_nation    = $res;
		}

			  $lists['nation'] = $nation;
		$lists['nation2'] = JHtmlSelect::genericlist(
			$nation,
			'filter_search_nation',
			$inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);
		$myoptions = array();
		$myoptions[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE_FILTER'));
		$myoptions[] = HTMLHelper::_('select.option', 'SIMPLE_LEAGUE', Text::_('COM_SPORTSMANAGEMENT_SIMPLE_LEAGUE'));
		$myoptions[] = HTMLHelper::_('select.option', 'DIVISIONS_LEAGUE', Text::_('COM_SPORTSMANAGEMENT_DIVISIONS_LEAGUE'));
		$myoptions[] = HTMLHelper::_('select.option', 'TOURNAMENT_MODE', Text::_('COM_SPORTSMANAGEMENT_TOURNAMENT_MODE'));
		$myoptions[] = HTMLHelper::_('select.option', 'FRIENDLY_MATCHES', Text::_('COM_SPORTSMANAGEMENT_FRIENDLY_MATCHES'));
		$lists['project_type'] = $myoptions;

			  $lists['project_types'] = HTMLHelper::_(
				  'select.genericList',
				  $myoptions,
				  'filter_project_type',
				  'class="inputbox" onChange="this.form.submit();" style="width:120px"',
				  'value',
				  'text',
				  $this->state->get('filter.project_type')
			  );
		unset($myoptions);

		$myoptions[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_UNIQUE_ID_FILTER'));
		$myoptions[] = HTMLHelper::_('select.option', '1', Text::_('JNO'));
		$myoptions[] = HTMLHelper::_('select.option', '2', Text::_('JYES'));
		$this->unique_id = $myoptions;
		unset($myoptions);

			  $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_TEMPLATES'));
		$mdltemplates = BaseDatabaseModel::getInstance('Templates', 'sportsmanagementModel');
		$res = $mdltemplates->getMasterTemplates();
		$myoptions = array_merge($myoptions, $res);
		$lists['mastertemplates'] = $myoptions;
		unset($myoptions);

			  $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = BaseDatabaseModel::getInstance('agegroups', 'sportsmanagementModel');

		if ($res = $mdlagegroup->getAgeGroups())
		{
			$myoptions = array_merge($myoptions, $res);
			$this->search_agegroup = $res;
		}

		$lists['agegroup'] = $myoptions;
		$lists['agegroup2'] = JHtmlSelect::genericlist(
			$myoptions,
			'filter_search_agegroup',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_agegroup')
		);
		unset($myoptions);

			  unset($nation);
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
		$mdlassociation = BaseDatabaseModel::getInstance('jlextassociations', 'sportsmanagementModel');

		if ($res = $mdlassociation->getAssociations())
		{
			$nation = array_merge($nation, $res);
			$this->search_association    = $res;
		}

			  $lists['association'] = array();

		foreach ($res as $row)
		{
			if (array_key_exists($row->country, $lists['association']))
			{
				$lists['association'][$row->country][] = $row;

					 // Echo "Das Element 'erstes' ist in dem Array vorhanden";
			}
			else
			{
				$lists['association'][$row->country][] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
				$lists['association'][$row->country][] = $row;
			}

					  // $lists['association'] = $nation;
		}

		// $lists['association'] = $nation;

			  $lists['association2'] = JHtmlSelect::genericlist(
				  $nation,
				  'filter_search_association',
				  $inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
				  'value',
				  'text',
				  $this->state->get('filter.search_association')
			  );

			  $mdlProjectDivisions = BaseDatabaseModel::getInstance('divisions', 'sportsmanagementModel');
		$mdlRounds = BaseDatabaseModel::getInstance('Rounds', 'sportsmanagementModel');
		 $mdlMatches = BaseDatabaseModel::getInstance('Matches', 'sportsmanagementModel');

			$this->modeldivision = $mdlProjectDivisions;
		$this->modelround = $mdlRounds;
		$this->modelmatches = $mdlMatches;
		$this->lists = $lists;
		$this->season_ids = ComponentHelper::getParams($this->option)->get('current_season');

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.6
	 */
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE');
		$this->icon = 'projects';

		ToolbarHelper::publishList('projects.publish');
		ToolbarHelper::unpublishList('projects.unpublish');
		ToolbarHelper::divider();
		ToolbarHelper::apply('projects.saveshort');
		ToolbarHelper::addNew('project.add');
		ToolbarHelper::editList('project.edit');
		ToolbarHelper::custom('project.import', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CSV_IMPORT'), false);
		ToolbarHelper::archiveList('project.export', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_XML_EXPORT'));
		ToolbarHelper::custom('project.copy', 'copy.png', 'copy_f2.png', Text::_('JTOOLBAR_DUPLICATE'), false);

		parent::addToolbar();
	}
}
