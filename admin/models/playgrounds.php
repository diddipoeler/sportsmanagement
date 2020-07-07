<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       playgrounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelPlaygrounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPlaygrounds extends JSMModelList
{
	var $_identifier = "playgrounds";

	/**
	 * sportsmanagementModelPlaygrounds::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'v.name','name',
			'v.alias','alias',
			'v.short_name','short_name',
			'v.max_visitors','max_visitors',
			'v.picture','picture',
			'v.country','country',
			'club',
			'v.id','id',
			'v.ordering','ordering',
			'state','search_nation',
		);
		parent::__construct($config);
		parent::setDbo($this->jsmdb);
	}

	/**
	 * sportsmanagementModelPlaygrounds::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		// Select some fields
		$this->jsmquery->select('v.*');

		// From table
		$this->jsmquery->from('#__sportsmanagement_playground as v');

		// Join over the clubs
		$this->jsmquery->select('c.name As club');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c ON c.id = v.club_id');

		// Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(v.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_nation'))
		{
			$this->jsmquery->where("v.country LIKE '" . $this->getState('filter.search_nation') . "'");
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'v.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;

	}

	/**
	 * Method to return a playground/venue array (id,text)
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getPlaygrounds($picture=false)
	{
		$starttime = microtime();
		$results   = array();
		$this->jsmquery->clear();
		$this->jsmquery->select('id AS value, name AS text');
if ( $picture )
{
$this->jsmquery->select('picture as playgroundpicture');	
}
		// From table
		$this->jsmquery->from('#__sportsmanagement_playground');
		$this->jsmquery->order('text ASC');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();

			return $result;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * sportsmanagementModelPlaygrounds::getPlaygroundListSelect()
	 *
	 * @return
	 */
	public function getPlaygroundListSelect()
	{
		$starttime = microtime();
		$results   = array();
		$this->jsmquery->clear();
		$this->jsmquery->select('id,name,id AS value,name AS text,short_name,club_id');
		$this->jsmquery->from('#__sportsmanagement_playground');
		$this->jsmquery->order('name');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$results = $this->jsmdb->loadObjectList();
			return $results;
		}
		catch (ExecutionFailureException $databaseException)
		{
            Log::add(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $databaseException->getCode(), $databaseException->getMessage()) . '<br />', Log::ERROR, 'jsmerror');
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
	   $list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'v.name';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

	}

}
