<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmpersons
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;


/**
 * sportsmanagementViewplayers
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewplayers extends sportsmanagementView
{


	/**
	 * sportsmanagementViewplayers::init()
	 *
	 * @return
	 */
	public function init()
	{
		$tpl              = '';
		$this->assign     = false;
		$this->season_id  = 0;
		$this->team_id    = 0;
		$this->persontype = 0;

		switch ($this->getLayout())
		{
			case 'assignpersons':
			case 'assignpersons_3':
			case 'assignpersons_4':
				$this->season_id  = $this->jinput->get('season_id');
				$this->team_id    = $this->jinput->get('team_id');
				$this->persontype = $this->jinput->get('persontype');
				$this->assign     = true;
				break;
		}

		$this->table = Table::getInstance('player', 'sportsmanagementTable');
		$this->app->setUserState($this->option . 'task', '');

		/**
		 * build the html select list for positions
		 */
		$positionsList[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));
		$positions       = BaseDatabaseModel::getInstance('positions', 'sportsmanagementmodel')->getAllPositions();

		if ($positions)
		{
			$positions = array_merge($positionsList, $positions);
		}

		$lists['positions'] = $positions;
		unset($positionsList);

		/**
		 * build the html options for nation
		 */
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
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);

		unset($nation);

		$myoptions   = array();
		$myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = BaseDatabaseModel::getInstance('agegroups', 'sportsmanagementModel');

		if ($res = $mdlagegroup->getAgeGroups())
		{
			$myoptions             = array_merge($myoptions, $res);
			$this->search_agegroup = $res;
		}

		$lists['agegroup']  = $myoptions;
		$lists['agegroup2'] = JHtmlSelect::genericlist(
			$myoptions,
			'filter_search_agegroup',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_agegroup')
		);
		unset($myoptions);

		$this->lists = $lists;

	}


	/**
	 * sportsmanagementViewplayers::_displayAssignPlayers()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	function _displayAssignPlayers($tpl = null)
	{

		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$this->persontype = $this->app->getUserState("$this->option.persontype", '0');
		$mdlProject       = BaseDatabaseModel::getInstance('project', 'sportsmanagementModel');
		$project          = $mdlProject->getProject($this->project_id);
		$project_name     = $project->name;
		$project_team_id  = $this->app->getUserState($this->option . 'project_team_id');
		$team_name        = $this->model->getProjectTeamName($project_team_id);

		// $items = $this->get('Items');
		// $total = $this->get('Total');
		// $pagination = $this->get('Pagination');

		$this->table = Table::getInstance('player', 'sportsmanagementTable');

		//		$this->table	= $table;

		// Save icon should be replaced by the apply
		ToolbarHelper::apply('person.saveassigned', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_SAVE_SELECTED'));

		// Set toolbar items for the page
		$type = $jinput->getInt('type');

		if ($type == 0)
		{
			// Back icon should be replaced by the abort/close icon
			ToolbarHelper::back(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BACK'), 'index.php?option=com_sportsmanagement&view=teamplayers');
			ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_PLAYERS'), 'generic.png');
		}
		elseif ($type == 1)
		{
			// Back icon should be replaced by the abort/close icon
			ToolbarHelper::back(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BACK'), 'index.php?option=com_sportsmanagement&view=teamstaffs');
			ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_STAFF'), 'generic.png');
		}
		elseif ($type == 2)
		{
			// Back icon should be replaced by the abort/close icon
			ToolbarHelper::back(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BACK'), 'index.php?option=com_sportsmanagement&view=projectreferees');
			ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_REFEREES'), 'generic.png');
		}

		// Build the html options for nation
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation = array_merge($nation, $res);
		}

		$lists['nation'] = $nation;

		// Build the html select list for positions
		$positionsList[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));
		$positions       = BaseDatabaseModel::getInstance('positions', 'sportsmanagementmodel')->getAllPositions();

		if ($positions)
		{
			$positions = array_merge($positionsList, $positions);
		}

		$lists['positions'] = $positions;
		unset($positionsList);

		$myoptions   = array();
		$myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = BaseDatabaseModel::getInstance("agegroups", "sportsmanagementModel");

		if ($res = $mdlagegroup->getAgeGroups())
		{
			$myoptions             = array_merge($myoptions, $res);
			$this->search_agegroup = $res;
		}

		$lists['agegroup']  = $myoptions;
		$lists['agegroup2'] = JHtmlSelect::genericlist(
			$myoptions,
			'filter_search_agegroup',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_agegroup')
		);
		unset($myoptions);

		$this->prjid    = $this->project_id;
		$this->prj_name = $project_name;

		// $this->team_id    = $team_id;
		$this->team_name       = $team_name;
		$this->project_team_id = $project_team_id;
		$this->lists           = $lists;

		//	$this->items	= $items;
		//        $this->user	= $user;
		//		$this->pagination	= $pagination;
		//		$this->request_url	= Factory::getURI()->toString();
		$this->type = $type;

		$this->setLayout('assignplayers');

	}


	/**
	 * sportsmanagementViewplayers::calendar()
	 *
	 * @param   mixed   $value
	 * @param   mixed   $name
	 * @param   mixed   $id
	 * @param   string  $format
	 * @param   mixed   $attribs
	 * @param   mixed   $onUpdate
	 * @param   mixed   $i
	 *
	 * @return
	 */
	function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null, $onUpdate = null, $i = null)
	{
		HTMLHelper::_('behavior.calendar'); // Load the calendar behavior

		if (is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}

		$document = Factory::getDocument();
		$document->addScriptDeclaration(
			'window.addEvent(\'domready\',function() {Calendar.setup({
	        inputField     :    "' . $id . '",    // id of the input field
	        ifFormat       :    "' . $format . '",     // format of the input field
	        button         :    "' . $id . '_img", // trigger for the calendar (button ID)
	        align          :    "Tl",          // alignment (defaults to "Bl")
	        onUpdate       :    ' . ($onUpdate ? $onUpdate : 'null') . ',
	        singleClick    :    true
    	});});'
		);
		$html = '';
		$html .= '<input onchange="document.getElementById(\'cb' . $i . '\').checked=true" type="text" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />' .
			'<img class="calendar" src="' . Uri::root(true) . '/templates/system/images/calendar.png" alt="calendar" id="' . $id . '_img" />';

		return $html;
	}


	/**
	 * sportsmanagementViewplayers::addToolbar()
	 *
	 * @return
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE');

		ToolbarHelper::publish('players.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('players.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::divider();

		ToolbarHelper::apply('players.saveshort');
		ToolbarHelper::editList('player.edit');
		ToolbarHelper::addNew('player.add');
		ToolbarHelper::custom('player.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('player.export', Text::_('JTOOLBAR_EXPORT'));

		parent::addToolbar();
	}
}
