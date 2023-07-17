<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       leagues.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelLeagues
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelLeagues extends JSMModelList
{
	var $_identifier = "leagues";

	/**
	 * sportsmanagementModelLeagues::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'obj.name',
			'obj.alias',
			'obj.short_name',
			'obj.country',
			'obj.published_act_season',
            'obj.champions_complete',
			'st.name',
			'obj.id',
			'obj.ordering',
			'obj.published',
			'obj.modified',
			'obj.modified_by',
			'ag.name',
			'fed.name',
            'state',
            'search_agegroup',
            'search_nation'
		);
		parent::__construct($config);
		parent::setDbo($this->jsmdb);
	}


	/**
	 * sportsmanagementModelLeagues::getLeagues()
	 * 
	 * @return
	 */
	function getLeagues()
	{

		$search_nation = '';
        $search_associations = '';

		if ($this->jsmapp->isClient('administrator'))
		{
			$search_nation = $this->getState('filter.search_nation');
            $search_associations = $this->getState('filter.search_associations');
		}

        $this->jsmquery->clear();
		$this->jsmquery->select('id,name,league_level');
		$this->jsmquery->from('#__sportsmanagement_league');

		if ($search_nation)
		{
			$this->jsmquery->where('country LIKE ' . $this->jsmdb->Quote('' . $search_nation . ''));
		}
        if ( $search_associations )
        {
            $this->jsmquery->where('associations = ' . $search_associations );
            
        }

		$this->jsmquery->order('name ASC');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$result = $this->jsmdb->loadObjectList())
		{
			return array();
		}

		// foreach ($result as $league)
		// {
		// 	$league->name = Text::_($league->name).' ('.$league->league_level.')';
		// }

		return $result;
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
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}
        $list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));
		$this->setState('filter.search_agegroup', $this->getUserStateFromRequest($this->context . '.filter.search_agegroup', 'filter_search_agegroup', ''));
		
		$this->setState('filter.search_league_level', $this->getUserStateFromRequest($this->context . '.filter.search_league_level', 'filter_search_league_level', ''));
		$this->setState('filter.search_champions_complete', $this->getUserStateFromRequest($this->context . '.filter.search_champions_complete', 'filter_search_champions_complete', ''));
		
		$this->setState('filter.search_associations', $this->getUserStateFromRequest($this->context . '.filter.search_associations', 'filter_search_associations', ''));
		$this->setState('filter.search_federation', $this->getUserStateFromRequest($this->context . '.filter.search_federation', 'filter_search_federation', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'obj.name';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		//$this->jsmjinput->set('leaguenation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '') );
		$this->jsmapp->setUserState("$this->jsmoption.leaguenation", $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '') );
        $this->jsmapp->setUserState("$this->jsmoption.leaguefederation", $this->getUserStateFromRequest($this->context . '.filter.search_federation', 'filter_search_federation', '') );
		$this->setState('list.direction', $listOrder);
	}

	/**
	 * sportsmanagementModelLeagues::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('obj.name,obj.short_name,obj.alias,obj.associations,obj.country,obj.ordering,obj.id,obj.picture,obj.checked_out,obj.checked_out_time,obj.agegroup_id');
		$this->jsmquery->select('obj.published,obj.modified,obj.modified_by,obj.published_act_season,obj.league_level,obj.champions_complete');
		$this->jsmquery->select('st.name AS sportstype');
		$this->jsmquery->from('#__sportsmanagement_league as obj');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = obj.sports_type_id');
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
		$this->jsmquery->select('ag.name AS agegroup');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = obj.agegroup_id');
		$this->jsmquery->select('fed.name AS fedname');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_associations AS fed ON fed.id = obj.associations');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(obj.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_nation'))
		{
			$this->jsmquery->where('obj.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->getState('filter.search_associations'))
		{
			$this->jsmquery->where('obj.associations = ' . $this->getState('filter.search_associations'));
		}

		/** sonderselektion bei verbänden */
        if ($this->getState('filter.search_federation'))
		{
		  $this->jsmquery->join('LEFT', '#__sportsmanagement_countries AS co ON co.alpha3 = obj.country');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_federations AS fe ON fe.id = co.federation');
          $this->jsmquery->where('fe.id = ' . $this->getState('filter.search_federation'));
		}

		if ($this->getState('filter.search_agegroup'))
		{
			$this->jsmquery->where('obj.agegroup_id = ' . $this->getState('filter.search_agegroup'));
		}
		
		if ($this->getState('filter.search_league_level'))
		{
			$this->jsmquery->where('obj.league_level = ' . $this->getState('filter.search_league_level'));
		}
		
		if ( $this->getState('filter.search_champions_complete') != '' )
		{
			$this->jsmquery->where('obj.champions_complete = ' . $this->getState('filter.search_champions_complete'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('obj.published = ' . $this->getState('filter.state'));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'obj.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}


}
