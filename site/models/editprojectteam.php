<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editprojectteam
 * @file       editprojectteam.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);

/**
 * sportsmanagementModelEditprojectteam
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelEditprojectteam extends AdminModel
{

	var $latitude = null;
	var $longitude = null;
    var $_id = 0;
    var $_data = array();

	/**
	 * sportsmanagementModelEditprojectteam::updItem()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function updItem($data)
	{
	   // Log::add(Text::_('<pre>'.print_r($data,true).'</pre>'   ), Log::ERROR, 'jsmerror');
       // Factory::getApplication()->enqueueMessage(Text::_('<pre>'.print_r($data,true).'</pre>'   ), Log::ERROR, 'jsmerror');  
		$app = Factory::getApplication();
        $query = Factory::getDbo()->getQuery(true);

		foreach ($data['request'] as $key => $value)
		{
			$data[$key] = $value;
		}

		/** Specify which columns are to be ignored. This can be a string or an array. */
		$ignore = '';

		try
		{
			$table = $this->getTable('projectteam');
			$data = array_filter($data);
			$table->bind($data, $ignore);
			$table->check();
			$table->store();
		}
		catch (Exception $e)
		{
			Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		}
        
        
// Fields to update.
$fields = array(
    Factory::getDbo()->quoteName('picture') . ' = ' . Factory::getDbo()->quote($data['picture']),
);
// Conditions for which records should be updated.
$conditions = array(
    Factory::getDbo()->quoteName('team_id') . ' = ' . (int) $data['team_id']
);
$query->clear(); 
$query->update(Factory::getDbo()->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
Factory::getDbo()->setQuery($query);
try
		{
$result = Factory::getDbo()->execute();
	}
		catch (Exception $e)
		{
		Factory::getApplication()->enqueueMessage(Text::_('data <pre>'.print_r($data,true).'</pre>'   ),  'error');
        Factory::getApplication()->enqueueMessage(Text::_('query <pre>'.print_r($query->dump(),true).'</pre>'   ), 'error');
        Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
		Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
//			Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
//			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		}


// Fields to update.
$fields = array(
    Factory::getDbo()->quoteName('picture') . ' = ' . Factory::getDbo()->quote($data['picture']),
);
// Conditions for which records should be updated.
$conditions = array(
    Factory::getDbo()->quoteName('id') . ' = ' . (int) $data['team_id']
);
$query->clear(); 
$query->update(Factory::getDbo()->quoteName('#__sportsmanagement_season_team_id'))->set($fields)->where($conditions);
Factory::getDbo()->setQuery($query);
try
		{
$result = Factory::getDbo()->execute();
	}
		catch (Exception $e)
		{
		Factory::getApplication()->enqueueMessage(Text::_('data <pre>'.print_r($data,true).'</pre>'   ),  'error');
        Factory::getApplication()->enqueueMessage(Text::_('query <pre>'.print_r($query->dump(),true).'</pre>'   ), 'error');
        Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
		Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
//			Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
//			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		}












        

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
	public function getTable($type = 'projectteam', $prefix = 'sportsmanagementTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$cfg_which_media_tool = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('cfg_which_media_tool', 0);
		$app                  = Factory::getApplication('site');

		$form = $this->loadForm('com_sportsmanagement.' . $this->name, $this->name, array('load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

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
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * sportsmanagementModelEditprojectteam::getData()
	 *
	 * @return
	 */
	function getData()
	{
		$this->_id   = Factory::getApplication()->input->getInt('ptid', 0);
		$this->_data = $this->getTable('projectteam', 'sportsmanagementTable');
		$this->_data->load($this->_id);
		return $this->_data;
	}

}


