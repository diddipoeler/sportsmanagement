<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       allprojects.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @copyright  Copyright:  2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage allprojects
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelallprojects
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelallprojects extends ListModel
{
	var $_identifier = "allprojects";

	var $limitstart = 0;

	var $limit = 0;

	/**
	 * sportsmanagementModelallprojects::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
			// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$this->use_current_season = $jinput->getVar('use_current_season', '0', 'request', 'string');
		$this->season = $jinput->request->get('s', 0, 'INT');

			$this->limitstart = $jinput->getVar('limitstart', 0, '', 'int');
				$config['filter_fields'] = array(
						'v.name',
						'v.picture',
						'v.website',
						'l.name',
						's.name',
						'v.location',
						'l.country'
						);
				parent::__construct($config);
				$getDBConnection = sportsmanagementHelper::getDBConnection();
				parent::setDbo($getDBConnection);
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return integer  The starting number of items available in the data set.
	 *
	 * @since 11.1
	 */
	public function getStart()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// $limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
		$this->setState('list.start', $this->limitstart);

		$store = $this->getStoreId('getstart');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
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
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Initialise variables.
		$app = Factory::getApplication('site');

			  // List state information
		// $value = Factory::getApplication()->input->getUInt('limit', $app->getCfg('list_limit', 0));
		$value = $this->getUserStateFromRequest($this->context . '.limit', 'limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

			  $temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_leagues', 'filter_search_leagues', '');

		if (!$temp_user_request)
		{
			//            $temp_user_request = Factory::getApplication()->input->getInt( "l", 0 );
			//            Factory::getApplication()->input->setVar( "l", 0 );
		}

			  $this->setState('filter.search_leagues', $temp_user_request);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_seasons', 'filter_search_seasons', '');
		$this->setState('filter.search_seasons', $temp_user_request);

		// $filter_order = Factory::getApplication()->input->getCmd('filter_order');
		$filter_order = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($filter_order, $this->filter_fields))
		{
			$filter_order = 'v.name';
		}

			  // $filter_order_Dir = Factory::getApplication()->input->getCmd('filter_order_Dir');
		$filter_order_Dir = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC', '')))
		{
			$filter_order_Dir = 'ASC';
		}

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);

	}


	/**
	 * sportsmanagementModelallplaygrounds::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// $search   = $this->getState('filter.search');
		// $search_nation    = $this->getState('filter.search_nation');

			  // $search_leagues   = $this->getState('filter.search_leagues');
		// $search_seasons   = $this->getState('filter.search_seasons');

			  // Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query    = $db->getQuery(true);
		$user    = Factory::getUser();

			  $query->select('v.id,v.name,v.picture');
		$query->select('l.country,l.name as leaguename');
		$query->select('s.name as seasonname');
		$query->select('CONCAT_WS( \':\', v.id, v.alias ) AS slug');
		$query->select('CONCAT_WS( \':\', l.id, l.alias ) AS leagueslug');

		// From table
		$query->from('#__sportsmanagement_project AS v');
		$query->join('INNER', '#__sportsmanagement_league AS l ON l.id = v.league_id');
		$query->join('INNER', '#__sportsmanagement_season AS s ON s.id = v.season_id');

		//        $query->join('LEFT','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		//        $query->join('LEFT','#__sportsmanagement_project AS p ON p.id = pt.project_id');

			  //        // Join over the users for the checked out user.
		//		$query->select('uc.name AS editor');
		//		$query->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');

		if ($this->getState('filter.search'))
		{
			$query->where('LOWER(v.name) LIKE ' . $db->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_nation'))
		{
					// $query->where("l.country = '".$search_nation."'");
					$query->where('l.country LIKE ' . $db->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->getState('filter.search_leagues'))
		{
			$query->where('v.league_id = ' . $this->getState('filter.search_leagues'));
		}

		if ($this->getState('filter.search_seasons'))
		{
					$query->where('v.season_id = ' . $this->getState('filter.search_seasons'));
		}

		if ($this->use_current_season)
		{
			$filter_season = ComponentHelper::getParams($option)->get('current_season', 0);
			$query->where('v.season_id IN (' . implode(',', $filter_season) . ')');
		}

		if ($this->season)
		{
			$query->where('v.season_id = ' . $this->season);
		}

			$query->where('v.published = 1');

			  $query->group('v.id');

			$query->order($db->escape($this->getState('filter_order', 'v.name')) . ' ' . $db->escape($this->getState('filter_order_Dir', 'ASC')));

			return $query;

	}


}

