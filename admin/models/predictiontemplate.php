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
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;

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
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return mixed  Object on success, false on failure.
	 *
	 * @since 1.6
	 */
	public function getItem($pk = null)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

			   $prediction_id = $app->getUserState("$option.prediction_id", '0');

		if ($item = parent::getItem($pk))
		{
		}

			return $item;
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key.
	 *
	 * @return boolean
	 * @since  1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return Factory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) || parent::allowEdit($data, $key);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param  type    The table type to instantiate
	 * @param  string    A prefix for the table class name. Optional.
	 * @param  array    Configuration array for model. Optional.
	 * @return JTable    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'predictiontemplate', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$cfg_which_media_tool = ComponentHelper::getParams($option)->get('cfg_which_media_tool', 0);

		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.predictiontemplate', 'predictiontemplate', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

			  return $form;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string    Script files
	 */
	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.predictiontemplate.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param  array    The form data.
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		  $app = Factory::getApplication();
		  $date = Factory::getDate();
		  $user = Factory::getUser();
		  $post = Factory::getApplication()->input->post->getArray(array());

		  // Set the values
		  $data['modified'] = $date->toSql();
		  $data['modified_by'] = $user->get('id');

		if (isset($post['params']) && is_array($post['params']))
		{
			// Convert the params field to a string.
			$paramsString = json_encode($post['params']);
			$data['params'] = $paramsString;
		}

			// Zuerst sichern, damit wir bei einer neuanlage die id haben
		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$isNew = $this->getState($this->getName() . '.new');
			$data['id'] = $id;

			if ($isNew)
			{
				// Here you can do other tasks with your newly saved record...
				$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
			}
		}

			  return true;
	}

	/**
	 * Method to return a prediction game item array
	 *
	 * @access public
	 * @return object
	 */
	function getPredictionGame( $id )
	{
		  $app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_prediction_game');
		$query->where('id = ' . (int) $id);

		$db->setQuery($query);

		if (!$result = $db->loadObject())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}
		else
		{
			return $result;
		}
	}



}
