<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       predictionproject.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * sportsmanagementModelpredictionproject
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelpredictionproject extends AdminModel
{
	/**
	 * Get the record form.
	 *
	 * @param array   $data     Form data.
	 * @param boolean $loadData Whether to load stored data.
	 *
	 * @return mixed
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_sportsmanagement.predictionproject',
			'predictionproject',
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		if ($form->getValue('joker'))
		{
			$form->setFieldAttribute('joker_limit', 'type', 'text');
			$form->setFieldAttribute('points_tipp_joker', 'type', 'text');
			$form->setFieldAttribute('points_correct_result_joker', 'type', 'text');
			$form->setFieldAttribute('points_correct_diff_joker', 'type', 'text');
			$form->setFieldAttribute('points_correct_draw_joker', 'type', 'text');
			$form->setFieldAttribute('points_correct_tendence_joker', 'type', 'text');
		}

		if ($form->getValue('champ'))
		{
			$form->setFieldAttribute('points_tipp_champ', 'type', 'text');
		}

		return $form;
	}

	/**
	 * Return the form script path.
	 *
	 * @return string
	 */
	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

	/**
	 * Save item order.
	 *
	 * @param array|null $pks   Record IDs.
	 * @param array|null $order Ordering values.
	 *
	 * @return boolean True on success.
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
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Return the prediction project table.
	 *
	 * @param string $type   Table type.
	 * @param string $prefix Table class prefix.
	 * @param array  $config Table configuration.
	 *
	 * @return Table
	 */
	public function getTable($type = 'predictionproject', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Save the form data.
	 *
	 * @param array $data Form data.
	 *
	 * @return boolean True on success.
	 */
	public function save($data)
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();
		$date  = Factory::getDate();
		$user  = $app->getIdentity();
		$post  = $input->post->getArray(array());

		$data['modified']    = $date->toSql();
		$data['modified_by'] = $user->id;

		if (isset($post['extended']) && is_array($post['extended']))
		{
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		if (!isset($data['league_final4']))
		{
			$data['league_final4'] = '0';
		}
		elseif (is_array($data['league_final4']))
		{
			$data['league_final4'] = implode(',', array_map('intval', $data['league_final4']));
		}

		return parent::save($data);
	}

	/**
	 * Delete prediction projects.
	 *
	 * @param array $pks Record IDs.
	 *
	 * @return boolean
	 */
	public function delete(&$pks)
	{
		return parent::delete($pks);
	}

	/**
	 * Check whether an existing record can be edited.
	 *
	 * @param array  $data Input data.
	 * @param string $key  Primary key field.
	 *
	 * @return boolean
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
	 * Get form data from the session or current item.
	 *
	 * @return mixed
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.predictionproject.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			if ($data && isset($data->league_final4))
			{
				$value = trim((string) $data->league_final4);
				$data->league_final4 = $value === '' ? array() : explode(',', $value);
			}
		}

		return $data;
	}

	/**
	 * Return a prediction project object.
	 *
	 * @param int $prediction_id Prediction project ID.
	 *
	 * @return object|false
	 */
	public function getPredictionProject($prediction_id = 0)
	{
		$predictionId = (int) $prediction_id;

		if (!$predictionId)
		{
			return false;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__sportsmanagement_prediction_project'))
			->where($db->quoteName('prediction_id') . ' = ' . $predictionId);

		try
		{
			$db->setQuery($query);
			return $db->loadObject() ?: false;
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
}
