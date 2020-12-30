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
			's.prediction_id',
			's.project_id',
			's.round_id',
			's.rien_ne_va_plus',
			's.points_tipp',
			's.points_correct_result',
			's.points_correct_diff',
			's.points_correct_draw',
			's.points_correct_tendence',
			's.modified',
			's.modified_by'
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

		if ($this->jsmjinput->getInt('prediction_id'))
		{
			$this->setState('filter.prediction_id', $this->jsmjinput->getInt('prediction_id'));
			$this->jsmapp->setUserState("com_sportsmanagement.prediction_id", $temp_user_request);
		}
		else
		{
			$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.prediction_id', 'filter_prediction_id', '');
			$this->setState('filter.prediction_id', $temp_user_request);
		}

		// List state information.
		$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);

		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 's.name';
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
		// Create a new query object.
		$this->jsmquery->clear();
		$this->jsmquery->select('s.*')
			->from('#__sportsmanagement_prediction_tippround AS s')
			//->join('LEFT', '#__users AS u ON u.id = s.checked_out')
			//->join('LEFT', '#__users AS u1 ON u1.id = s.modified_by')
			;

		// if ($this->getState('filter.search'))
		// {
		// 	$this->jsmquery->where('(LOWER(s.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		// }

		// $this->jsmquery->order(
		// 	$this->jsmdb->escape($this->getState('list.ordering', 's.name')) . ' ' .
		// 	$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		// );

		return $this->jsmquery;
	}

}
