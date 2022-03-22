<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage modelss
 * @file       projects.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * sportsmanagementModelProjects
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelProjects extends JSMModelList
{
	var $_identifier = "projects";

	/**
	 * sportsmanagementModelProjects::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'p.name',
			'l.name',
			'l.country',
			's.name',
			'st.name',
			'p.project_type',
			'p.master_template',
			'p.published',
			'p.id',
			'p.ordering',
			'p.picture',
			'ag.name',
			'p.agegroup_id',
			'ag.name'
		);
		parent::__construct($config);
		parent::setDbo($this->jsmdb);
	}

	/**
	 * sportsmanagementModelProjects::existcurrentseason()
	 *
	 * @param   mixed    $season_ids
	 * @param   integer  $league_id
	 *
	 * @return
	 */
	function existcurrentseason($season_ids = array(), $league_id = 0)
	{
		if ($season_ids)
		{
			$seasons = implode(",", $season_ids);
			$this->jsmquery->clear();
			$this->jsmquery->select('pro.id');
			$this->jsmquery->from('#__sportsmanagement_project as pro');
			$this->jsmquery->join('INNER', '#__sportsmanagement_league as le on le.id = pro.league_id');
			$this->jsmquery->where('le.id = ' . $league_id);
			$this->jsmquery->where('pro.season_id IN (' . $seasons . ')');

			try
			{
				$this->jsmdb->setQuery($this->jsmquery);
				$result = $this->jsmdb->loadResult();

				return $result;
			}
			catch (Exception $e)
			{
				// $app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
				return null;
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}
        $list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));
		$this->setState('filter.association', $this->getUserStateFromRequest($this->context . '.filter.association', 'filter_association', ''));
		$this->setState('filter.season', $this->getUserStateFromRequest($this->context . '.filter.season', 'filter_season', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));
		$this->setState('filter.search_league', $this->getUserStateFromRequest($this->context . '.filter.search_league', 'filter_search_league', ''));
		$this->setState('filter.sports_type', $this->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', ''));
		$this->setState('filter.search_association', $this->getUserStateFromRequest($this->context . '.filter.search_association', 'filter_search_association', ''));
		$this->setState('filter.project_type', $this->getUserStateFromRequest($this->context . '.filter.project_type', 'filter_project_type', ''));
		$this->setState('filter.userfields', $this->getUserStateFromRequest($this->context . '.filter.userfields', 'filter_userfields', ''));
		$this->setState('filter.search_agegroup', $this->getUserStateFromRequest($this->context . '.filter.search_agegroup', 'filter_search_agegroup', ''));
		$this->setState('filter.unique_id', $this->getUserStateFromRequest($this->context . '.filter.unique_id', 'filter_unique_id', ''));

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');
        
        Factory::getApplication()->input->set('search_nation_projects', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'p.name';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
        
        $this->jsmapp->setUserState("$this->jsmoption.projectnation", $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '') );

		$this->setState('list.direction', $listOrder);

	}

	/**
	 * sportsmanagementModelProjects::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$this->jsmquery->clear();
		$this->jsmsubquery1->clear();
		$this->jsmsubquery2->clear();

		switch ($this->getState('filter.unique_id'))
		{
			case 0:
			case '':
			$this->jsmsubquery1->select('count(pt.id)');
			$this->jsmsubquery1->from('#__sportsmanagement_project_team AS pt');
			$this->jsmsubquery1->where('pt.project_id = p.id');
			break;
			case 1:
			$this->jsmsubquery1->select('count(pt.id)');
			$this->jsmsubquery1->from('#__sportsmanagement_project_team AS pt');
			$this->jsmsubquery1->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id');
			$this->jsmsubquery1->join('INNER', '#__sportsmanagement_team as t ON t.id = st.team_id');
			$this->jsmsubquery1->join('INNER', '#__sportsmanagement_club as c ON c.id = t.club_id');
			$this->jsmsubquery1->where('pt.project_id = p.id');
			$this->jsmsubquery1->where('( c.unique_id IS NULL OR c.unique_id LIKE ' . $this->jsmdb->Quote('' . '') . ' )');
			break;
			case 2:
			$this->jsmsubquery1->select('count(pt.id)');
			$this->jsmsubquery1->from('#__sportsmanagement_project_team AS pt');
			$this->jsmsubquery1->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id');
			$this->jsmsubquery1->join('INNER', '#__sportsmanagement_team as t ON t.id = st.team_id');
			$this->jsmsubquery1->join('INNER', '#__sportsmanagement_club as c ON c.id = t.club_id');
			$this->jsmsubquery1->where('pt.project_id = p.id');
			$this->jsmsubquery1->where('( c.unique_id NOT LIKE ' . $this->jsmdb->Quote('' . '') . ' )');
			break;
		}

		$this->jsmsubquery2->select('ef.name');
		$this->jsmsubquery2->from('#__sportsmanagement_user_extra_fields_values as ev ');
		$this->jsmsubquery2->join('INNER', '#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');
		$this->jsmsubquery2->where('ev.jl_id = p.id');
		$this->jsmsubquery2->where('ef.template_backend LIKE ' . $this->jsmdb->Quote('' . 'project' . ''));
		$this->jsmsubquery2->where('ev.fieldvalue != ' . $this->jsmdb->Quote('' . ''));

		$this->jsmsubquery3->select('count(co.id)');
		$this->jsmsubquery3->from('#__sportsmanagement_confidential AS co');
		$this->jsmsubquery3->where('co.project = p.id');
		$this->jsmsubquery3->where('co.team_id = 0');

		$this->jsmquery->select('p.id,p.ordering,p.published,p.project_type,p.name,p.alias,p.checked_out,p.checked_out_time,p.sports_type_id,p.current_round,p.picture,p.agegroup_id,p.master_template,p.fast_projektteam ');
		$this->jsmquery->select('p.league_id,p.use_leaguechampion');
		$this->jsmquery->select('p.modified,p.modified_by');
		$this->jsmquery->select('u1.username');

		$this->jsmquery->select('st.name AS sportstype');
		$this->jsmquery->select('s.name AS season');
		$this->jsmquery->select('l.name AS league,l.country');
		$this->jsmquery->select('u.name AS editor');
		$this->jsmquery->select('ag.name AS agegroup');
		$this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS proteams');
		$this->jsmquery->select('(' . $this->jsmsubquery3 . ') AS notassign');

		$this->jsmquery->from('#__sportsmanagement_project AS p');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_season AS s ON s.id = p.season_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_league AS l ON l.id = p.league_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = p.agegroup_id');
		$this->jsmquery->join('LEFT', '#__users AS u ON u.id = p.checked_out');
		$this->jsmquery->join('LEFT', '#__users AS u1 ON u1.id = p.modified_by');

		if ($this->getState('filter.userfields'))
		{
			$this->jsmquery->select('ev.fieldvalue as user_fieldvalue,ev.id as user_field_id');
			$this->jsmquery->join('INNER', '#__sportsmanagement_user_extra_fields_values as ev ON ev.jl_id = p.id');
			$this->jsmquery->join('INNER', '#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');
			$this->jsmquery->where('ef.id = ' . $this->getState('filter.userfields'));
		}

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(p.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_league'))
		{
			$this->jsmquery->where('p.league_id = ' . $this->getState('filter.search_league'));
		}

		if ($this->getState('filter.sports_type'))
		{
			$this->jsmquery->where('p.sports_type_id = ' . $this->getState('filter.sports_type'));
		}

		if ($this->getState('filter.season'))
		{
			$this->jsmquery->where('p.season_id = ' . $this->getState('filter.season'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('p.published = ' . $this->getState('filter.state'));
		}

		if ($this->getState('filter.search_agegroup'))
		{
			$this->jsmquery->where('p.agegroup_id = ' . $this->getState('filter.search_agegroup'));
		}

		if ($this->getState('filter.search_nation'))
		{
			$this->jsmquery->where('l.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->getState('filter.search_association'))
		{
			$this->jsmquery->where("l.associations = " . $this->getState('filter.search_association'));
		}

		if ($this->getState('filter.project_type'))
		{
			$this->jsmquery->where('p.project_type LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.project_type') . ''));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'p.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;

	}


}
