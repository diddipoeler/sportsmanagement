<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editclub
 * @file       editclub.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelEditClub
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelEditClub extends AdminModel
{
	/**
	 *
	 * interfaces
	 */
	var $latitude = null;
	var $longitude = null;
	var $projectid = 0;
	var $clubid = 0;
	var $club = null;

	/**
	 * sportsmanagementModelEditClub::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$app = Factory::getApplication();
		parent::__construct();
		$this->projectid = Factory::getApplication()->input->getInt('p', 0);
		$this->clubid    = Factory::getApplication()->input->getInt('cid', 0);
		$this->name      = 'club';

	}

	/**
	 * sportsmanagementModelEditClub::updItem()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function updItem($data)
	{
		$app = Factory::getApplication();
/** wurden jahre mitgegeben ? */
				if ($data['founded'] != '0000-00-00' && $data['founded'] != '')
				{
					$data['founded'] = sportsmanagementHelper::convertDate($data['founded'], 0);
				}

				if ($data['dissolved'] != '0000-00-00' && $data['dissolved'] != '')
				{
					$data['dissolved'] = sportsmanagementHelper::convertDate($data['dissolved'], 0);
				}

				if ($data['founded'] == '0000-00-00' || $data['founded'] == '')
				{
					$data['founded'] = '0000-00-00';
				}

				if ($data['founded'] != '0000-00-00' && $data['founded'] != '')
				{
					$data['founded_year']      = date('Y', strtotime($data['founded']));
					$data['founded_timestamp'] = sportsmanagementHelper::getTimestamp($data['founded']);
				}
				else
				{
					$data['founded_year'] = $data['founded_year'];
				}

				if ($data['dissolved'] == '0000-00-00' || $data['dissolved'] == '')
				{
					$data['dissolved'] = '0000-00-00';
				}

				if ($data['dissolved'] != '0000-00-00' && $data['dissolved'] != '')
				{
					$data['dissolved_year']      = date('Y', strtotime($data['dissolved']));
					$data['dissolved_timestamp'] = sportsmanagementHelper::getTimestamp($data['dissolved']);
				}
				else
				{
					$data['dissolved_year'] = $data['dissolved_year'];
				}
		if ( !$data['founded_year'] )
            {
            $data['founded_year'] = 'kein';
            }
		
		
		foreach ($data['request'] as $key => $value)
		{
			$data[$key] = $value;
		}

		try
		{
			/**
			 *
			 * Specify which columns are to be ignored. This can be a string or an array.
			 */
			$ignore = '';
			/**
			 *
			 * Get the table object from the model.
			 */
			$table = $this->getTable('club');
			/**
			 *
			 * Bind the array to the table object.
			 */
			$table->bind($data, $ignore);
			$table->store();
		}
		catch (Exception $e)
		{
			Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');

			// $result = false;
		}

		// Return $result;
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return Table    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'club', $prefix = 'sportsmanagementTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * sportsmanagementModelEditClub::getForm()
	 *
	 * @param   mixed  $data
	 * @param   bool   $loadData
	 *
	 * @return
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app                  = Factory::getApplication();
		$option               = Factory::getApplication()->input->getCmd('option');
		$cfg_which_media_tool = ComponentHelper::getParams($option)->get('cfg_which_media_tool', 0);
		$show_team_community  = ComponentHelper::getParams($option)->get('show_team_community', 0);

		$app = Factory::getApplication('site');

		/**
		 *
		 * Get the form.
		 */
		Form::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms');
		Form::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		$form = $this->loadForm(
			'com_sportsmanagement.' . $this->name, $this->name,
			array('load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		if (!$show_team_community)
		{
			$form->setFieldAttribute('merge_teams', 'type', 'hidden');
		}

		$form->setFieldAttribute('logo_small', 'default', ComponentHelper::getParams($option)->get('ph_logo_small', ''));
		$form->setFieldAttribute('logo_small', 'directory', 'com_sportsmanagement/database/clubs/small');
		$form->setFieldAttribute('logo_small', 'type', $cfg_which_media_tool);

		$form->setFieldAttribute('logo_middle', 'default', ComponentHelper::getParams($option)->get('ph_logo_medium', ''));
		$form->setFieldAttribute('logo_middle', 'directory', 'com_sportsmanagement/database/clubs/medium');
		$form->setFieldAttribute('logo_middle', 'type', $cfg_which_media_tool);

		$form->setFieldAttribute('logo_big', 'default', ComponentHelper::getParams($option)->get('ph_logo_big', ''));
		$form->setFieldAttribute('logo_big', 'directory', 'com_sportsmanagement/database/clubs/large');
		$form->setFieldAttribute('logo_big', 'type', $cfg_which_media_tool);

		$form->setFieldAttribute('trikot_home', 'default', ComponentHelper::getParams($option)->get('ph_logo_small', ''));
		$form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/clubs/trikot_home');
		$form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);

		$form->setFieldAttribute('trikot_away', 'default', ComponentHelper::getParams($option)->get('ph_logo_small', ''));
		$form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/clubs/trikot_away');
		$form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);

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
		$app = Factory::getApplication();
		/**
		 *
		 * Check the session for previously entered form data.
		 */
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * sportsmanagementModelEditClub::getData()
	 *
	 * @return
	 */
	function getData()
	{
		if (is_null($this->club))
		{
			$this->club = $this->getTable('Club', 'sportsmanagementTable');
			$this->club->load($this->clubid);
		}

		return $this->club;
	}


}
