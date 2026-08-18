<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       positionstatistic.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * sportsmanagementModelpositionstatistic
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelpositionstatistic extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param array   $data     Data for the form.
	 * @param boolean $loadData True if the form is to load its own data.
	 *
	 * @return mixed A form object on success, false on failure.
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_sportsmanagement.positionstatistic',
			'positionstatistic',
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the script that has to be included on the form.
	 *
	 * @return string Script file.
	 */
	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

	/**
	 * Method to save item order.
	 *
	 * @param array|null $pks   Record IDs.
	 * @param array|null $order Ordering values.
	 *
	 * @return boolean True on success.
	 * @since  1.5
	 */
	public function saveorder($pks = null, $order = null)
	{
		$pks   = (array) $pks;
		$order = (array) $order;
		$row   = $this->getTable();
		$count = min(count($pks), count($order));

		for ($i = 0; $i < $count; $i++)
		{
			$row->load((int) $pks[$i]);

			if ($row->ordering != $order[$i])
			{
				$row->ordering = (int) $order[$i];

				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(
						get_class($this),
						__FUNCTION__,
						__FILE__,
						$row->getError(),
						__LINE__
					);
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param string $type   The table type to instantiate.
	 * @param string $prefix A prefix for the table class name.
	 * @param array  $config Configuration array for the table.
	 *
	 * @return Table A database table object.
	 * @since  1.6
	 */
	public function getTable($type = 'positionstatistic', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Update the statistic assignments for a position.
	 *
	 * @param array $data        Submitted assignment data.
	 * @param int   $position_id Position ID.
	 *
	 * @return boolean True on success.
	 */
	public function store($data, $position_id)
	{
		$db         = $this->getDatabase();
		$positionId = (int) $position_id;
		$statIds    = isset($data['position_statistic']) && is_array($data['position_statistic'])
			? $data['position_statistic']
			: array();

		ArrayHelper::toInteger($statIds);
		$statIds = array_values(array_filter($statIds, static fn($id) => $id > 0));
		$statIds = array_values(array_unique($statIds));

		$query = 'DELETE FROM #__sportsmanagement_position_statistic WHERE position_id = ' . $positionId;

		if ($statIds)
		{
			$query .= ' AND statistic_id NOT IN (' . implode(',', $statIds) . ')';
		}

		try
		{
			$db->setQuery($query);
			$db->execute();

			foreach ($statIds as $ordering => $statId)
			{
				$query = 'UPDATE #__sportsmanagement_position_statistic'
					. ' SET ordering = ' . (int) $ordering
					. ' WHERE position_id = ' . $positionId
					. ' AND statistic_id = ' . (int) $statId;
				$db->setQuery($query);
				$db->execute();
			}

			foreach ($statIds as $ordering => $statId)
			{
				$query = 'INSERT IGNORE INTO #__sportsmanagement_position_statistic'
					. ' (position_id, statistic_id, ordering) VALUES ('
					. $positionId . ', ' . (int) $statId . ', ' . (int) $ordering . ')';
				$db->setQuery($query);
				$db->execute();
			}
		}
		catch (RuntimeException $e)
		{
			sportsmanagementModeldatabasetool::writeErrorLog(
				get_class($this),
				__FUNCTION__,
				__FILE__,
				$e->getMessage(),
				__LINE__
			);
			return false;
		}

		return true;
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param array  $data An array of input data.
	 * @param string $key  The name of the key for the primary key.
	 *
	 * @return boolean
	 * @since  1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$id = (int) ($data[$key] ?? 0);

		return Factory::getApplication()->getIdentity()->authorise(
			'core.edit',
			'com_sportsmanagement.message.' . $id
		) || parent::allowEdit($data, $key);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.positionstatistic.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
