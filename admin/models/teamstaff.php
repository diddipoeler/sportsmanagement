<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       teamstaff.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * SportsManagement Model
 */
class sportsmanagementModelteamstaff extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed A form object on success, false on failure.
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app                  = Factory::getApplication();
		$option               = $app->getInput()->getCmd('option', 'com_sportsmanagement');
		$params               = ComponentHelper::getParams($option);
		$cfg_which_media_tool = $params->get('cfg_which_media_tool', 0);

		$form = $this->loadForm(
			'com_sportsmanagement.teamstaff',
			'teamstaff',
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		$form->setFieldAttribute('picture', 'default', $params->get('ph_player', ''));
		$form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/teamstaffs');
		$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

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
	 * Method to update checked team staff records.
	 *
	 * @return boolean True on success.
	 */
	public function saveshort()
	{
		$input = Factory::getApplication()->getInput();
		$pks   = $input->post->get('cid', array(), 'array');
		$post  = $input->post->getArray(array());
		$pks   = array_values(array_filter(array_map('intval', (array) $pks), static fn($id) => $id > 0));

		foreach ($pks as $pk)
		{
			$tblPerson                      = $this->getTable();
			$tblPerson->id                  = $pk;
			$tblPerson->project_position_id = (int) ($post['project_position_id' . $pk] ?? 0);

			if (!$tblPerson->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(
					get_class($this),
					__FUNCTION__,
					__FILE__,
					$tblPerson->getError(),
					__LINE__
				);
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate.
	 * @param   string  $prefix  A prefix for the table class name.
	 * @param   array   $config  Configuration array for the table.
	 *
	 * @return Table A database table object.
	 * @since  1.6
	 */
	public function getTable($type = 'teamstaff', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
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
	 * Method to remove team staff and dependent match staff data.
	 *
	 * @param array $pks Record IDs.
	 *
	 * @return boolean True on success.
	 * @since  0.1
	 */
	public function delete(&$pks)
	{
		$pks = array_values(array_filter(array_map('intval', (array) $pks), static fn($id) => $id > 0));

		if ($pks)
		{
			$db   = $this->getDatabase();
			$cids = implode(',', $pks);
			$query = 'DELETE mp, ms
				FROM #__sportsmanagement_team_staff AS m
				LEFT JOIN #__sportsmanagement_match_staff AS mp ON mp.team_staff_id = m.id
				LEFT JOIN #__sportsmanagement_match_staff_statistic AS ms ON ms.team_staff_id = m.id
				WHERE m.id IN (' . $cids . ')';

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

		return parent::delete($pks);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return boolean True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$post = Factory::getApplication()->getInput()->post->getArray(array());

		if (isset($post['extended']) && is_array($post['extended']))
		{
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		return parent::save($data);
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
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.teamstaff.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
