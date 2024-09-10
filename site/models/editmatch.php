<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editmatch.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.match', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementModelEditMatch
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelEditMatch extends AdminModel
{

	const MATCH_ROSTER_STARTER = 0;
	const MATCH_ROSTER_SUBSTITUTE_IN = 1;
	const MATCH_ROSTER_SUBSTITUTE_OUT = 2;
	const MATCH_ROSTER_RESERVE = 3;

	static $projectid = 0;
	static $divisionid = 0;
	static $roundid = 0;
	static $mode = 0;
	static $seasonid = 0;
	static $order = 0;
	static $cfg_which_database = 0;
	static $oldlayout = '';
    
	var $latitude = null;
	var $longitude = null;

	/**
	 * sportsmanagementModelEditMatch::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		parent::__construct();
		self::$divisionid         = $jinput->getVar('division', '0');
		self::$mode               = $jinput->getVar('mode', '0');
		self::$order              = $jinput->getVar('order', '0');
		self::$projectid          = $jinput->getVar('p', '0');
		self::$oldlayout          = $jinput->getVar('oldlayout', '');
		self::$cfg_which_database = $jinput->getVar('cfg_which_database', '0');
		self::$roundid            = $jinput->getVar('r', '0');
		self::$seasonid           = $jinput->getVar('s', '0');
	}


function getSingleMatchData($match_id = 0,$match_number = '')
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$db     = sportsmanagementHelper::getDBConnection(true, $jinput->getInt('cfg_which_database', 0));
		$query  = $db->getQuery(true);
		$result = array();

		$query->select('m.*');
		$query->from('#__sportsmanagement_match_single AS m');
		$query->where('m.match_id = ' . (int) $match_id);
        $query->where('m.match_number = ' .$db->Quote('' . $match_number . '') );
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}
    
	function getSingleMatchDatas($match_id = 0)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$db     = sportsmanagementHelper::getDBConnection(true, $jinput->getInt('cfg_which_database', 0));
		$query  = $db->getQuery(true);
		$result = array();

		$query->select('m.*');
		$query->from('#__sportsmanagement_match_single AS m');
		$query->where('m.match_id = ' . (int) $match_id);
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}
	
	/**
	 * sportsmanagementModelEditMatch::savestats()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function savestats($data)
	{
		$result = sportsmanagementModelMatch::savestats($data);
	}


	/**
	 * sportsmanagementModelEditMatch::updateReferees()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function updateReferees($data)
	{
		$app    = Factory::getApplication();
		$config = Factory::getConfig();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		$sender = array(
			$config->get('mailfrom'),
			$config->get('fromname')
		);

		$positions         = sportsmanagementModelMatch::getProjectPositionsOptions(0, 3, $data['project_id']);
		$data['positions'] = $positions;
		$mdlMatch = BaseDatabaseModel::getInstance("Match", "sportsmanagementModel");
		$result = $mdlMatch->updateReferees($data);

		return $result;
	}

	/**
	 * sportsmanagementModelEditMatch::updateStaff()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function updateStaff($data)
	{
		$app                    = Factory::getApplication();
		$data['staffpositions'] = sportsmanagementModelMatch::getProjectPositionsOptions(0, 2, $data['project_id']);
		$mdlMatch = BaseDatabaseModel::getInstance("Match", "sportsmanagementModel");
		$result                 = $mdlMatch->updateStaff($data);

		return $result;
	}

	/**
	 * sportsmanagementModelEditMatch::updateRoster()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function updateRoster($data)
	{
		$app               = Factory::getApplication();
		$data['positions'] = sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $data['project_id']);
		$mdlMatch = BaseDatabaseModel::getInstance("Match", "sportsmanagementModel");
		$result            = $mdlMatch->updateRoster($data);

		return $result;
	}


function updateRosterBillard($data)
	{
	   $date          = Factory::getDate();
		$user          = Factory::getUser();
        $db            = sportsmanagementHelper::getDBConnection();
		$app               = Factory::getApplication();
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data '.'<pre>'.print_r($data,true).'</pre>' ), '');
        $projectpositions = sportsmanagementModelProject::getProjectPositions();
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' projectpositions '.'<pre>'.print_r($projectpositions,true).'</pre>' ), '');
        
        
        /**
        COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN
        COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER
        COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE
        */
        
        /** erst die spieler */
        foreach ( $data['roster'] as $key => $value )
        {
            /** spieler zugeordnet */
        if ( $value )
        {
        
        foreach ( $projectpositions as $keyposition => $valueposition )
        {
        if ( $valueposition->name == 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER' )
        {
        $temp                      = new stdClass;
		$temp->match_id            = $data['id'];
		$temp->teamplayer_id       = $value;
		//$temp->project_position_id = $valueposition->pposid;
        $temp->project_position_id = $valueposition->id;
		$temp->ordering            = $key;
        $temp->trikot_number            = $key;
		$temp->modified            = $date->toSql();
		$temp->modified_by         = $user->get('id');
		try
		{
		$resultquery = $db->insertObject('#__sportsmanagement_match_player', $temp);
		}
		catch (Exception $e)
		{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
            
            
        }    
            
        }
        
            
        }
            
        }
        /** der kaitän */
        foreach ( $data['rosterc'] as $key => $value )
        {
            /** spieler zugeordnet */
        if ( $value )
        {
        
        foreach ( $projectpositions as $keyposition => $valueposition )
        {
        if ( $valueposition->name == 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN' )
        {
        $temp                      = new stdClass;
		$temp->match_id            = $data['id'];
		$temp->teamplayer_id       = $value;
		//$temp->project_position_id = $valueposition->pposid;
        $temp->project_position_id = $valueposition->id;
		$temp->ordering            = '100';
        $temp->trikot_number            = '100';
		$temp->modified            = $date->toSql();
		$temp->modified_by         = $user->get('id');
		try
		{
		$resultquery = $db->insertObject('#__sportsmanagement_match_player', $temp);
		}
		catch (Exception $e)
		{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
            
            
        }    
            
        }
        
            
        }
            
        }
        
        
        
        /** der reservespieler */
        foreach ( $data['rosterr'] as $key => $value )
        {
            /** spieler zugeordnet */
        if ( $value )
        {
        
        foreach ( $projectpositions as $keyposition => $valueposition )
        {
        if ( $valueposition->name == 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE' )
        {
        $temp                      = new stdClass;
		$temp->match_id            = $data['id'];
		$temp->teamplayer_id       = $value;
		//$temp->project_position_id = $valueposition->pposid;
        $temp->project_position_id = $valueposition->id;
		$temp->ordering            = '50';
        $temp->trikot_number            = '50';
		$temp->modified            = $date->toSql();
		$temp->modified_by         = $user->get('id');
		try
		{
		$resultquery = $db->insertObject('#__sportsmanagement_match_player', $temp);
		}
		catch (Exception $e)
		{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
            
            
        }    
            
        }
        
            
        }
            
        }
        
        
        

        
		//$data['positions'] = sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $data['project_id']);
		//$mdlMatch = BaseDatabaseModel::getInstance("Match", "sportsmanagementModel");
		//$result            = $mdlMatch->updateRoster($data);

		return $result;
	}



	/**
	 * sportsmanagementModelEditMatch::updItem()
	 *
	 * @param   mixed  $data
	 *
	 * @return void
	 */
	function updItem($data)
	{
		$app = Factory::getApplication();

		foreach ($data['request'] as $key => $value)
		{
			switch ( $key )
			{
				case 'preview':
				$html          = $data['request']->get('preview', '', 'raw');
			$data['preview'] = $html;	
					break;
					case 'summary':
					$html          = $data['request']->get('summary', '', 'raw');
			$data['summary'] = $html;
					break;
				default:
				$data[$key] = $value;	
					break;
			}
			
		}

		/**
		 *
		 * Specify which columns are to be ignored. This can be a string or an array.
		 */
		$ignore = '';

		try
		{
			$table = $this->getTable('match');
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
	 * Returns a Table object, always creating it
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return Table    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array())
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
		$form                 = $this->loadForm('com_sportsmanagement.' . $this->name, $this->name, array('load_data' => $loadData));

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
	 * Method to load content person data
	 *
	 * @access private
	 * @return boolean    True on success
	 * @since  0.1
	 */
	function getData()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$db     = sportsmanagementHelper::getDBConnection(true, $jinput->getInt('cfg_which_database', 0));
		$query  = $db->getQuery(true);

		$this->_id = $jinput->getVar('matchid', '0');

		$query->select('m.*');
		$query->select('t1.name as hometeam ');
		$query->select('t2.name as awayteam ');
		$query->from('#__sportsmanagement_match AS m');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		$query->where('m.id = ' . (int) $this->_id);
		$db->setQuery($query);

		$this->_data = $db->loadObject();

		return $this->_data;
	}

}
