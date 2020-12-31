<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage models
 * @file       predictionrounds.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelPredictionRounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionRounds extends JSMModelList
{
	var $_identifier = "predictionrounds";

	/**
	 * sportsmanagementModelPredictionRounds::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			's.id',
			's.points_tipp',
			's.points_correct_result',
			's.points_correct_diff',
			's.points_correct_draw',
			's.points_correct_tendence',
			'r.name',
			'r.roundcode',
			
		);
		parent::__construct($config);
		parent::setDbo($this->jsmdb);
	}

	/**
	 * return count of acive prediction tipprounds
	 *
	 * @param   int prediction_id
	 *
	 * @return integer
	 */
	function getAcivePredictionRoundsCount($prediction_id)
	{
		// Create a new query object.
		$this->jsmquery->clear();

		// Select some fields
		$this->jsmquery->select('count(*) AS count');

		// From the table
		$this->jsmquery->from('#__sportsmanagement_prediction_tippround');
		$this->jsmquery->where('prediction_id = ' . $prediction_id);
		$this->jsmquery->where('published = 1');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);

			return $this->jsmdb->loadResult();
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
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// prefer session variable
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.prediction_id', 'filter_prediction_id', '');
		if (!$temp_user_request)
		{
			$temp_user_request = $this->jsmjinput->getInt('prediction_id');
            if ($temp_user_request) {
				$this->jsmapp->setUserState("com_sportsmanagement.prediction_id", $temp_user_request);
            }
		}
		$this->setState('filter.prediction_id', $temp_user_request);

		// List state information.
		$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);

		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'roundname';
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
	 * sportsmanagementModelPredictionRounds::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$prediction_id = $this->state->get('filter.prediction_id');

		// Create a new query object.
		$this->jsmquery->clear();
		$this->jsmquery->select(array('s.*', 'u.name AS editor', 'u1.username', 'r.name AS roundname', 'r.roundcode'))
			->from('#__sportsmanagement_prediction_tippround AS s')
			->where('s.prediction_id = ' . $prediction_id)
			->join('LEFT', '#__users AS u ON u.id = s.checked_out')
			->join('LEFT', '#__users AS u1 ON u1.id = s.modified_by')
			->join('LEFT', '#__sportsmanagement_round AS r ON r.id = s.round_id');

		if ($prediction_id > 0)
		{
			$this->jsmquery->where('s.prediction_id = ' . $prediction_id);
		}

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where("LOWER(roundname) LIKE " . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('s.published = ' . $this->getState('filter.state'));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'roundname')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}

}
