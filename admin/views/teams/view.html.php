<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage teams
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Table\Table;

jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewTeams
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeams extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeams::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$starttime = microtime();
		$this->assign = false;

		if ($this->getLayout() == 'assignteams' || $this->getLayout() == 'assignteams_3')
		{
			$this->season_id = $this->jinput->get('season_id');
			$this->assign = true;
		}

		$this->table = Table::getInstance('team', 'sportsmanagementTable');

		// Build the html select list for sportstypes
		$sportstypes[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$mdlSportsTypes = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);

		$this->sports_type = $allSportstypes;
		$lists['sportstype'] = $sportstypes;
		$lists['sportstypes'] = HTMLHelper::_('select.genericList', $sportstypes, 'filter_sports_type', 'class="inputbox" onChange="this.form.submit();" style="width:120px"', 'id', 'name', $this->state->get('filter.sports_type'));
		unset($sportstypes);

		// Build the html options for nation
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation = array_merge($nation, $res);

			// $this->assignRef('search_nation', $res);
			$this->search_nation = $res;
		}

		$lists['nation'] = $nation;
		$lists['nation2'] = JHtmlSelect::genericlist($nation, 'filter_search_nation', 'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 'value', 'text', $this->state->get('filter.search_nation'));

		$myoptions = array();
		$myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = BaseDatabaseModel::getInstance('agegroups', 'sportsmanagementModel');

		if ($res = $mdlagegroup->getAgeGroups())
		{
			$myoptions = array_merge($myoptions, $res);
		}

		$lists['agegroup'] = $myoptions;
		unset($myoptions);

		$this->club_id = $this->jinput->get->get('club_id');
		$this->lists = $lists;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{

		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_TITLE');
		$this->icon = 'teams';
		ToolbarHelper::apply('teams.saveshort');
		ToolbarHelper::addNew('team.add');
		ToolbarHelper::editList('team.edit');
		ToolbarHelper::custom('team.copysave', 'copy.png', 'copy_f2.png', Text::_('JTOOLBAR_DUPLICATE'), true);
		ToolbarHelper::custom('team.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('team.export', Text::_('JTOOLBAR_EXPORT'));

		if ($this->jinput->get->get('club_id'))
		{
			ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=clubs');
		}

		parent::addToolbar();
	}

}

