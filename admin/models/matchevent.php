<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       matchevent.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

/**
 * SportsManagement Model
 */
class sportsmanagementModelmatchevent extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param array   $data     Data for the form.
	 * @param boolean $loadData True if the form should load its own data.
	 *
	 * @return mixed A Form object on success, false on failure.
	 * @since 1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_sportsmanagement.matchevent', 'matchevent', array('control' => 'jform', 'load_data' => $loadData));

		return empty($form) ? false : $form;
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
	 * @param array|null $pks   Primary keys.
	 * @param array|null $order Ordering values.
	 *
	 * @return boolean True on success.
	 * @since 1.5
	 */
	public function saveorder($pks = null, $order = null)
	{
		$row = $this->getTable();

		for ($i = 0; $i < count((array) $pks); $i++)
		{
			$row->load((int) $pks[$i]);

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(
						get_class($this),
						__FUNCTION__,
						__FILE__,
						(string) $row->getError(),
						__LINE__
					);
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns a Table object.
	 *
	 * @param string $type   The table type to instantiate.
	 * @param string $prefix A prefix for the table class name.
	 * @param array  $config Configuration array.
	 *
	 * @return Table
	 * @since 1.6
	 */
	public function getTable($type = 'matchevent', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param array  $data An array of input data.
	 * @param string $key  The name of the primary key.
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

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed The data for the form.
	 * @since 1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.matchevent.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
