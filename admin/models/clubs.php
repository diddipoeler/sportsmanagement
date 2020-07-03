<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       clubs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelClubs
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelClubs extends JSMModelList
{
	var $_identifier = "clubs";

	/**
	 * sportsmanagementModelClubs::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'a.name',
			'a.website',
			'a.twitter',
			'a.facebook',
			'a.email',
			'a.logo_big',
			'a.logo_middle',
			'a.logo_small',
			'a.country',
			'a.state',
			'a.alias',
			'a.zipcode',
			'a.location',
			'a.address',
			'a.latitude',
			'a.longitude',
			'a.id',
			'a.published',
			'a.unique_id',
			'a.new_club_id',
			'a.ordering',
			'a.checked_out',
			'a.checked_out_time'
		);
		parent::__construct($config);
		parent::setDbo($this->jsmdb);

	}

	/**
	 * sportsmanagementModelClubs::getClubListSelect()
	 *
	 * @return
	 */
	public function getClubListSelect()
	{
		$results   = array();
		$this->jsmquery->clear();
		$this->jsmquery->select('id,name,id AS value,name AS text,country,standard_playground');
		$this->jsmquery->from('#__sportsmanagement_club');
		$this->jsmquery->order('name');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$results = $this->jsmdb->loadObjectList();
			return $results;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
			return false;
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

//$list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');
//echo 'jsmapp<pre>'.print_r($list,true).'</pre>';
		
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.association', 'filter_association', '');
		$this->setState('filter.association', $temp_user_request);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.season', 'filter_season', '');
		$this->setState('filter.season', $temp_user_request);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.geo_daten', 'filter_geo_daten', '');
		$this->setState('filter.geo_daten', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.standard_picture', 'filter_standard_picture', '');
		$this->setState('filter.standard_picture', $temp_user_request);
		//$value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$value = $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', 0, 'int');
		$this->setState('list.limit', $value);
		$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.name';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);
	}

	/**
	 * sportsmanagementModelClubs::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$this->jsmquery->clear();
		$this->jsmquery->select(implode(",", $this->filter_fields));
		$this->jsmquery->from('#__sportsmanagement_club as a');
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

		/** keine geodaten gesetzt */
		if ($this->getState('filter.geo_daten') == 1)
		{
			$this->jsmquery->where(' ( a.latitude IS NULL OR a.latitude = 0.00000000 )');
		}

		/** geo daten gesetzt */
		if ($this->getState('filter.geo_daten') == 2)
		{
			$this->jsmquery->where(' a.latitude > 0.00000000 ');
		}

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where(' ( LOWER(a.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') . ' OR LOWER(a.unique_id) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') . ')');
		}

		if ($this->getState('filter.search_nation'))
		{
			$this->jsmquery->where('a.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->getState('filter.season'))
		{
			$this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t ON a.id = t.club_id');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id as st ON t.id = st.team_id ');
			$this->jsmquery->where('st.season_id = ' . $this->getState('filter.season'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('a.published = ' . $this->getState('filter.state'));
		}

		if (is_numeric($this->getState('filter.association')))
		{
			$this->jsmquery->where('a.associations = ' . $this->getState('filter.association'));
		}
        
        if ( $this->getState('filter.standard_picture') )
		{
		$this->jsmquery->where('a.logo_big LIKE ' . $this->jsmdb->Quote('%' . 'placeholder' . '%'));  
        }

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'a.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}

}
