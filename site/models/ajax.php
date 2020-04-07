<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       ajax.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelAjax
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelAjax extends BaseDatabaseModel
{



	/**
	 * sportsmanagementModelAjax::getLink()
	 *
	 * @param   string  $view
	 * @param   integer $project_id
	 * @param   integer $round_id
	 * @param   integer $division_id
	 * @return void
	 */
	public function getLink($view='',$project_id=0,$round_id=0,$division_id=0,$season_id=0)
	{
		$app = Factory::getApplication();
		$link = '';

		if ($view)
		{
			switch ($view)
			{
				case "ranking":
					$routeparameter = array();
					$routeparameter['cfg_which_database'] = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0);
					$routeparameter['s'] = $season_id;
					$routeparameter['p'] = $project_id;
					$routeparameter['type'] = 0;
					$routeparameter['r'] = $round_id;
					$routeparameter['from'] = 0;
					$routeparameter['to'] = 0;
					$routeparameter['division'] = $division_id;
					$link = sportsmanagementHelperRoute::getSportsmanagementRoute($view, $routeparameter);
				break;

				case "results":
					$routeparameter = array();
					$routeparameter['cfg_which_database'] = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0);
					$routeparameter['s'] = $season_id;
					$routeparameter['p'] = $project_id;
					$routeparameter['r'] = $round_id;
					$routeparameter['division'] = $division_id;
					$routeparameter['mode'] = 0;
					$routeparameter['order'] = '';
					$routeparameter['layout'] = '';
					$link = sportsmanagementHelperRoute::getSportsmanagementRoute($view, $routeparameter);
				break;

				case "resultsranking":
					$routeparameter = array();
					$routeparameter['cfg_which_database'] = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0);
					$routeparameter['s'] = $season_id;
					$routeparameter['p'] = $project_id;
					$routeparameter['r'] = $round_id;
					$routeparameter['division'] = $division_id;
					$routeparameter['mode'] = 0;
					$routeparameter['order'] = '';
					$routeparameter['layout'] = '';
					$link = sportsmanagementHelperRoute::getSportsmanagementRoute($view, $routeparameter);
				break;

				case "teams":
				case "teamstree":
					$routeparameter = array();
					$routeparameter['cfg_which_database'] = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0);
					$routeparameter['s'] = $season_id;
					$routeparameter['p'] = $project_id;
					$routeparameter['division'] = $division_id;
					$link = sportsmanagementHelperRoute::getSportsmanagementRoute($view, $routeparameter);
				break;
			}

			return $link;
		}
	}


	/**
	 * sportsmanagementModelAjax::getProjectTeams()
	 *
	 * @param   mixed $project_id
	 * @return
	 */
	public function getProjectTeams($project_id)
	{
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$query->select('t.id AS value,t.name AS text');

		// From
		  $query->from('#__sportsmanagement_project_team as pt');
		$query->join('INNER', ' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
		$query->join('INNER', ' #__sportsmanagement_team t ON t.id = st.team_id ');
		$query->join('INNER', ' #__sportsmanagement_project p ON p.id = pt.project_id ');
		$query->where('pt.project_id = ' . (int) $project_id);
		$query->group('t.id,t.alias,t.name');

		// Order
		$query->order('t.name');
		$db->setQuery($query);

			  $res = $db->loadObjectList();

		if ($res)
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Team selektieren --')));
			 $options = array_merge($options, $res);
		}
		else
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- keine Teams -- ')));
		}

		return $options;
	}


	/**
	 * sportsmanagementModelAjax::getProjectSelect()
	 *
	 * @param   mixed $league_id
	 * @return
	 */
	public function getProjectSelect($league_id)
	{
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('p.id AS value, p.name AS text');
			$query->from('#__sportsmanagement_project AS p');
			$query->join('INNER', '#__sportsmanagement_season AS s on s.id = p.season_id ');
			$query->join('INNER', '#__sportsmanagement_league AS l on l.id = p.league_id');
			$query->where('p.published = 1');
			$query->where('p.league_id = ' . $league_id);
			$query->order('s.name DESC, p.name ASC');

		  $db->setQuery($query);

			  $res = $db->loadObjectList();

		if ($res)
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Projekt selektieren --')));
			$options = array_merge($options, $res);
		}
		else
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- keine Projekte -- ')));
		}

			return $options;
	}


	/**
	 * sportsmanagementModelAjax::getAssocLeagueSelect()
	 *
	 * @param   mixed $country_id
	 * @param   mixed $associd
	 * @return
	 */
	public function getAssocLeagueSelect($country_id,$associd)
	{
		// $app = Factory::getApplication();
		$this->_db = sportsmanagementHelper::getDBConnection();
		$this->_query = $this->_db->getQuery(true);
		$this->_query->clear();
		$this->_query->select('l.id AS value, l.name AS text');
			$this->_query->from('#__sportsmanagement_league AS l');
			$this->_query->join('INNER', '#__sportsmanagement_project AS p on l.id = p.league_id');
			$this->_query->join('INNER', '#__sportsmanagement_season AS s on s.id = p.season_id ');

		if ($associd)
		{
			$this->_query->where('l.associations = ' . $associd);
		}

			$this->_query->where('l.country = \'' . $country_id . '\'');
			$this->_query->group('l.name');
			$this->_query->order('l.name');

		  $this->_db->setQuery($this->_query);

				$res = $this->_db->loadObjectList();

		if ($res)
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Liga selektieren --')));
			$options = array_merge($options, $res);
		}
		else
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- keine Ligen -- ')));
		}

		  return $options;
	}

		  /**
		   * sportsmanagementModelAjax::getCountrySubSubAssocSelect()
		   *
		   * @param   mixed $subassoc_id
		   * @return
		   */
	public function getCountrySubSubAssocSelect($subassoc_id)
	{
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_associations AS s');
		$query->where('s.parent_id = ' . $subassoc_id);
		$query->where('s.published = 1');
		$query->order('s.name');

					  $db->setQuery($query);

			 $res = $db->loadObjectList();

		if ($res)
		{
			  $options = array(HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- ')));
			   $options = array_merge($options, $res);
		}
		else
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- keine Kreisverbände -- ')));
		}

		return $options;

	}

	/**
	 * sportsmanagementModelAjax::getCountrySubAssocSelect()
	 *
	 * @param   mixed $assoc_id
	 * @return
	 */
	public function getCountrySubAssocSelect($assoc_id)
	{
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$options = '';

			  $query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_associations AS s');
		$query->where('s.parent_id = ' . $assoc_id);
		$query->order('s.name');

		$db->setQuery($query);

			 $res = $db->loadObjectList();

		if ($res)
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Landesverbände -- ')));
			$options = array_merge($options, $res);
		}
		else
		{
			$options = array(HTMLHelper::_('select.option', 0, Text::_('-- keine Landesverbände -- ')));
		}

		return $options;
	}

		  /**
		   * sportsmanagementModelAjax::getCountryAssocSelect()
		   *
		   * @param   mixed $country
		   * @return
		   */
	public function getCountryAssocSelect($country)
	{
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$options = '';

			  $query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_associations AS s');
		$query->where('s.country = \'' . $country . '\'');
		$query->where('s.parent_id = 0');
		$query->order('s.name');

		$db->setQuery($query);

			 $res = $db->loadObjectList();

		if ($res)
		{
				$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- ')));
			   $options = array_merge($options, $res);
		}
		else
		{
				$options = array(HTMLHelper::_('select.option', 0, Text::_('-- keine Regionalverbände -- ')));
		}

		return $options;
	}



	/**
	 * sportsmanagementModelAjax::getProjectsOptions()
	 *
	 * @param   integer $season_id
	 * @param   integer $league_id
	 * @param   integer $ordering
	 * @return
	 */
	function getProjectsOptions($season_id = 0, $league_id = 0, $ordering = 0)
	{
		$app = Factory::getApplication();
		$db = Factory::getDbo();

		// $option = Factory::getApplication()->input->getCmd('option');

			  $query = $db->getQuery(true);
		$query->select('p.id AS value, p.name AS text, s.name AS season_name, l.name AS league_name');
		$query->from('#__sportsmanagement_project AS p');
		$query->join('INNER', '#__sportsmanagement_season AS s on s.id = p.season_id');
		$query->join('INNER', '#__sportsmanagement_league AS l on l.id = p.league_id');
		$query->where('p.published = 1');

		if ($season_id)
		{
			$query->where('p.season_id = ' . $season_id);
		}

		if ($league_id)
		{
			$query->where('p.league_id = ' . $league_id);
		}

		switch ($ordering)
		{
			case 1:
				$query->order('p.ordering DESC');
			break;

			case 2:
				$query->order('s.ordering ASC, l.ordering ASC, p.ordering ASC');
			break;

			case 3:
				$query->order('s.ordering DESC, l.ordering DESC, p.ordering DESC');
			break;

			case 4:
				$query->order('p.name ASC');
			break;

			case 5:
				$query->order('p.name DESC');
			break;

			case 0:
			default:
				$query->order('p.ordering ASC');
			break;
		}

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}
}
