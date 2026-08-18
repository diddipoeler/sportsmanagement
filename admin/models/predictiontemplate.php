<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       predictiontemplate.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

/**
 * sportsmanagementModelPredictionTemplate
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelPredictionTemplate extends AdminModel
{
	/**
	 * Return the prediction template table.
	 *
	 * @param string $type   Table type.
	 * @param string $prefix Table class prefix.
	 * @param array  $config Table configuration.
	 *
	 * @return Table
	 */
	public function getTable($type = 'predictiontemplate', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

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
			'com_sportsmanagement.predictiontemplate',
			'predictiontemplate',
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
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
	 * Save the form data.
	 *
	 * @param array $data Form data.
	 *
	 * @return boolean True on success.
	 */
	public function save($data)
	{
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$option = $input->getCmd('option', 'com_sportsmanagement');
		$date   = Factory::getDate();
		$user   = $app->getIdentity();
		$post   = $input->post->getArray(array());

		$data['modified']    = $date->toSql();
		$data['modified_by'] = $user->id;

		if (isset($post['params']) && is_array($post['params']))
		{
			$data['params'] = json_encode($post['params']);
		}

		if (!parent::save($data))
		{
			return false;
		}

		$id    = (int) $this->getState($this->getName() . '.id');
		$isNew = $this->getState($this->getName() . '.new');

		if ($isNew)
		{
			$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
		}

		return true;
	}

	/**
	 * Return a prediction game item.
	 *
	 * @param int $id Prediction game ID.
	 *
	 * @return object|false
	 */
	public function getPredictionGame($id)
	{
		$app   = Factory::getApplication();
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__sportsmanagement_prediction_game'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		try
		{
			$db->setQuery($query);
			return $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error');
			return false;
		}
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
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.predictiontemplate.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Get a single record.
	 *
	 * @param integer|null $pk Primary key.
	 *
	 * @return mixed
	 */
	public function getItem($pk = null)
	{
		return parent::getItem($pk);
	}
}
