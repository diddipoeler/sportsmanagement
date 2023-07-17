<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage positions
 * @file       positions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelPositions
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPositions extends JSMModelList
{
	var $_identifier = "positions";

	/**
	 * sportsmanagementModelPositions::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
	   if (empty($config['filter_fields']))
		{
		$config['filter_fields'] = array(
			'po.name','name',
			'po.picture','picture',
			'po.parent_id','parent_id',
			'po.sports_type_id','sports_type',
			'po.persontype','persontype',
			'po.id','id',
			'po.published','published','state',
			'po.modified','modified',
			'po.modified_by','modified_by',
			'po.ordering','ordering',
		);
        }
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
	}

	/**
	 * sportsmanagementModelPositions::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('po.*,pop.name AS parent_name,st.name AS sportstype,u.name AS editor');
		$this->jsmquery->select('(select count(*) FROM #__sportsmanagement_position_eventtype WHERE position_id = po.id) countEvents');
		$this->jsmquery->select('(select count(*) FROM #__sportsmanagement_position_statistic WHERE position_id = po.id) countStats');
		$this->jsmquery->from('#__sportsmanagement_position AS po');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = po.sports_type_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_position AS pop ON pop.id = po.parent_id');
		$this->jsmquery->join('LEFT', '#__users AS u ON u.id=po.checked_out');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(po.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('po.published = ' . $this->getState('filter.state'));
		}

		if ($this->getState('filter.sports_type'))
		{
			$this->jsmquery->where('po.sports_type_id = ' . $this->getState('filter.sports_type'));
		}
		
		if ($this->getState('filter.persontype'))
		{
			$this->jsmquery->where('po.persontype = ' . $this->getState('filter.persontype'));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'po.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}

	/**
	 * Method to return the positions array (id,name)
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getParentsPositions()
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('pos.id,pos.name,pos.id AS value,pos.name AS text,pos.alias,pos.parent_id,pos.persontype,pos.sports_type_id');
		$this->jsmquery->from('#__sportsmanagement_position AS pos');
		$this->jsmquery->where('pos.parent_id = 0');
		$this->jsmquery->order('pos.ordering ASC ');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$result = $this->jsmdb->loadObjectList())
		{
			return false;
		}

		return $result;
	}

	/**
	 * sportsmanagementModelPositions::getProjectPositions()
	 *
	 * @param   mixed    $project_id
	 * @param   integer  $persontype
	 *
	 * @return
	 */
	function getProjectPositions($project_id, $persontype = 1)
	{
        $this->jsmquery->clear();
		$this->jsmquery->select('ppos.id AS value, pos.name AS text, ppos.position_id as position_id');
		$this->jsmquery->from('#__sportsmanagement_position AS pos');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = pos.id');
		$this->jsmquery->where('ppos.project_id = ' . (int) $project_id);
		$this->jsmquery->where('pos.persontype = ' . (int) $persontype);
		$this->jsmquery->order('pos.ordering');

		try
		{
			Factory::getDbo()->setQuery($this->jsmquery);
			$result = Factory::getDbo()->loadObjectList();

			foreach ($result as $position)
			{
				$position->text = Text::_($position->text);
			}
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
            $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			$result = false;
		}

		return $result;
	}

	/**
	 * Method to return a project positions array (id,position)
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getPositions($project_id)
	{
        $this->jsmquery->clear();
		$this->jsmquery->select('pp.id AS value,name AS text');
		$this->jsmquery->from('#__sportsmanagement_position AS p');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_project_position AS pp ON pp.position_id = p.id');
		$this->jsmquery->where('pp.project_id = ' . $project_id);
		$this->jsmquery->order('p.ordering');

		Factory::getDbo()->setQuery($this->jsmquery);

		if (!$result = Factory::getDbo()->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__);

			return false;
		}
		else
		{
			foreach ($result as $position)
			{
				$position->text = Text::_($position->text);
			}

			return $result;
		}
	}

	/**
	 * Method to return a positions array (id,position + (sports_type_name))
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getAllPositions()
	{

		$this->jsmquery->clear();

		// Select some fields
		$this->jsmquery->select('pos.id AS value, pos.name AS posName,s.name AS sName');

		// From the table
		$this->jsmquery->from('#__sportsmanagement_position AS pos');
		$this->jsmquery->join('INNER', '#__sportsmanagement_sports_type AS s ON s.id = pos.sports_type_id');
		$this->jsmquery->where('pos.published = 1');
		$this->jsmquery->order('pos.ordering,pos.name');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$result = $this->jsmdb->loadObjectList())
		{
			return array();
		}
		else
		{
			foreach ($result as $position)
			{
				$position->text = Text::_($position->posName) . ' (' . Text::_($position->sName) . ')';
			}

			return $result;
		}
	}

	/**
	 * sportsmanagementModelPositions::getPositionListSelect()
	 *
	 * @return
	 */
	public function getPositionListSelect()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$query  = Factory::getDbo()->getQuery(true);

		// Select some fields
		$query->select('id,name,id AS value,name AS text,alias,parent_id,persontype,sports_type_id');

		// From the table
		$query->from('#__sportsmanagement_position');
		$query->order('name');

		Factory::getDbo()->setQuery($query);
		$result = Factory::getDbo()->loadObjectList();

		foreach ($result as $position)
		{
			$position->text = Text::_($position->text);
		}

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
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}
$list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.sports_type', $this->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));
		
		$this->setState('filter.persontype', $this->getUserStateFromRequest($this->context . '.filter.persontype', 'filter_persontype', ''));

		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'po.name';
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
