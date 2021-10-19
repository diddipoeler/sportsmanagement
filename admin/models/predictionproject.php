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
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
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
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app                  = Factory::getApplication();
		$option               = Factory::getApplication()->input->getCmd('option');
		$cfg_which_media_tool = ComponentHelper::getParams($option)->get('cfg_which_media_tool', 0);

		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.predictionproject', 'predictionproject', array('control' => 'jform', 'load_data' => $loadData));

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
	 * Method to get the script that have to be included on the form
	 *
	 * @return string    Script files
	 */
	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

	/**
	 * Method to save item order
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  1.5
	 */
	function saveorder($pks = null, $order = null)
	{
		$row =& $this->getTable();

		// Update ordering values
		for ($i = 0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return JTable    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'predictionproject', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array    The form data.
	 *
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$post   = Factory::getApplication()->input->post->getArray(array());

		// Set the values
		$data['modified']    = $date->toSql();
		$data['modified_by'] = $user->get('id');

		if (isset($post['extended']) && is_array($post['extended']))
		{
			// Convert the extended field to a string.
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}
		// special case final4 Club IDs, save as CSV list
		if (!isset($data['league_final4'])) {
			$data['league_final4'] = "0";
		} else {
			if (is_array($data['league_final4'])) {
				$data['league_final4'] = implode(",", $data['league_final4']);
			}
		}
		// Proceed with the save
		return parent::save($data);
	}

	/**
	 * sportsmanagementModelpredictionproject::delete()
	 *
	 * @param   mixed  $pks
	 *
	 * @return
	 */
	public function delete(&$pks)
	{
		$app = Factory::getApplication();

		return parent::delete($pks);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
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
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.predictionproject.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
			// Prepare Multiple selection list for final 4 which was stored as CSV string
			$data->league_final4 = explode(",", $data->league_final4);	
		}

		return $data;
	}

	/**
	 * Method to return a prediction project object
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getPredictionProject($prediction_id = 0)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		if ($prediction_id)
		{
			// Select some fields
			$query->clear();
			$query->select('*');
			$query->from('#__sportsmanagement_prediction_project');
			$query->where('prediction_id = ' . $prediction_id);

			$db->setQuery($query);

			if (!$result = $db->loadObject())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);

				return false;
			}
			else
			{
				return $result;
			}
		}
		else
		{
			return false;
		}

	}
}
