<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       projectposition.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Sportsmanagement Component Positionstool Model
 *
 * @package Sportsmanagement
 * @since   0.1
 */
class sportsmanagementModelProjectposition extends JSMModelAdmin
{
	var $_identifier = 'pposition';
	var $_project_id = 0;

	/**
	 * Method to update project positions list.
	 *
	 * @param array $data Submitted project position data.
	 *
	 * @return boolean True on success.
	 */
	public function store($data)
	{
		$db          = $this->getDatabase();
		$projectId   = (int) ($data['project_id'] ?? 0);
		$positionIds = isset($data['project_positionslist']) && is_array($data['project_positionslist'])
			? $data['project_positionslist']
			: array();

		ArrayHelper::toInteger($positionIds);
		$positionIds = array_values(array_filter($positionIds, static fn($id) => $id > 0));

		$query = 'DELETE FROM #__sportsmanagement_project_position WHERE project_id=' . $projectId;

		if ($positionIds)
		{
			$query .= ' AND position_id NOT IN (' . implode(',', $positionIds) . ')';
		}

		try
		{
			$db->setQuery($query);
			$db->execute();
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

		foreach ($positionIds as $positionId)
		{
			$query = 'INSERT IGNORE INTO #__sportsmanagement_project_position (project_id,position_id) VALUES ('
				. $projectId . ',' . (int) $positionId . ')';

			try
			{
				$db->setQuery($query);
				$db->execute();
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
	 * @since 1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$id = (int) ($data[$key] ?? 0);

		return Factory::getApplication()->getIdentity()->authorise(
			'core.edit',
			'com_sportsmanagement.message.' . $id
		) || parent::allowEdit($data, $key);
	}
}
