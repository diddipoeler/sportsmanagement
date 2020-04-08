<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editperson
 * @file       editperson.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;

JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);

/**
 * sportsmanagementModelEditPerson
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelEditPerson extends AdminModel
{
	// Interfaces
	var $latitude    = null;

	var $longitude    = null;


	/**
	 * sportsmanagementModelEditPerson::updItem()
	 *
	 * @param   mixed $data
	 * @return void
	 */
	function updItem($data)
	{
		$app = Factory::getApplication();

		foreach ($data['request'] as $key => $value)
		{
			$data[$key] = $value;
		}

			  // Specify which columns are to be ignored. This can be a string or an array.
			// $ignore = 'id';
			$ignore = '';

		try
		{
					  // Get the table object from the model.
					  $table = $this->getTable('person');

					  // Bind the array to the table object.
					  $table->bind($data, $ignore);
					  $table->store();
		}
		catch (Exception $e)
		{
			Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		}

	}


	/**
	 * Method to load content person data
	 *
	 * @access private
	 * @return boolean    True on success
	 * @since  0.1
	 */
	function getData()
	{
		  $this->_id = Factory::getApplication()->input->getInt('pid', 0);

		//		// Lets load the content if it doesn't already exist
		//		if (empty($this->_data))
		//		{
		  $this->_data = $this->getTable('person', 'sportsmanagementTable');
		 $this->_data->load($this->_id);

				  //			$query='SELECT * FROM #__sportsmanagement_person WHERE id='.(int) $this->_id;
		//			$this->_db->setQuery($query);
		//			$this->_data = $this->_db->loadObject();
		 return $this->_data;

		//		}
		//		return true;
	}


	/**
	 * Returns a Table object, always creating it
	 *
	 * @param  type    The table type to instantiate
	 * @param  string    A prefix for the table class name. Optional.
	 * @param  array    Configuration array for model. Optional.
	 * @return Table    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'person', $prefix = 'sportsmanagementTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$cfg_which_media_tool = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('cfg_which_media_tool', 0);
		$app = Factory::getApplication('site');

		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.' . $this->name, $this->name, array('load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

			  $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('ph_player', ''));
		$form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
		$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

			  return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

}
