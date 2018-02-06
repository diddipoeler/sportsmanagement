<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editmatch.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.model');
//jimport('joomla.application.component.modelitem');
//jimport('joomla.application.component.modelform');

jimport('joomla.application.component.modeladmin');

require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'imageselect.php');



class sportsmanagementModelEditMatch extends JModelAdmin
{

const MATCH_ROSTER_STARTER		= 0;
const MATCH_ROSTER_SUBSTITUTE_IN	= 1;
const MATCH_ROSTER_SUBSTITUTE_OUT	= 2;
const MATCH_ROSTER_RESERVE		= 3;	
  /* interfaces */
	var $latitude	= null;
	var $longitude	= null;
    
    static $projectid	= 0;
	static $divisionid	= 0;
	static $roundid = 0;
    	static $mode = 0;
	static $seasonid = 0;
    static $order = 0;
     static $cfg_which_database = 0;
    static $oldlayout = '';
	
	
    function __construct()
	{
		 // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        
        parent::__construct();

		self::$divisionid = $jinput->getVar('division','0');
		self::$mode = $jinput->getVar('mode','0');
		self::$order = $jinput->getVar('order','0');
        self::$projectid = $jinput->getVar('p','0');
        self::$oldlayout = $jinput->getVar('oldlayout','');
        self::$cfg_which_database = $jinput->getVar('cfg_which_database','0');
        self::$roundid = $jinput->getVar('r','0');
        self::$seasonid = $jinput->getVar('s','0');
        
    }    
    
	
function updateRoster($data)
    {
$app = JFactory::getApplication();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');	

$mid = $data['id'];
$team = $data['team'];
$trikotnumbers = $data['trikot_number']; 
$captain = $data['captain'];	
$positions = sportsmanagementModelMatch::getProjectPositionsOptions(0,1,$data['project_id']);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' positions <br><pre>'.print_r($positions ,true).'</pre>'),'Notice');		
$query = JFactory::getDBO()->getQuery(true);
$query->clear();
$query->select('mp.id');
$query->from('#__sportsmanagement_match_player AS mp');
$query->join('INNER',' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.teamplayer_id ');
$query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
$query->join('LEFT',' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
$query->where('mp.came_in = '.self::MATCH_ROSTER_STARTER);
$query->where('mp.match_id = '.$mid);
$query->where('pt.id = '.$team);
JFactory::getDBO()->setQuery($query);
$result = JFactory::getDbo()->loadColumn();
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result'.'<pre>'.print_r($result,true).'</pre>' ),'');
	
foreach ($positions AS $project_position_id => $pos)
		{
			if (isset($data['position'.$project_position_id]))
			{
				foreach ($data['position'.$project_position_id] AS $ordering => $player_id)
				{
// Create and populate an object.
$temp = new stdClass();
$temp->match_id = $mid;
$temp->teamplayer_id = $player_id;
$temp->project_position_id= $pos->pposid;
$temp->came_in = self::MATCH_ROSTER_STARTER;
$temp->trikot_number = $trikotnumbers[$player_id];
$temp->captain = $captain[$player_id];					
$temp->ordering = $ordering;
try{					
// Insert the object
$resultquery = JFactory::getDBO()->insertObject('#__sportsmanagement_match_player', $temp);    
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}


				}
			}
		}	
	
	
	
}
	
    function updItem($data)
    {
        $app = JFactory::getApplication();
        
        foreach( $data['request'] as $key => $value)
        {
            $data[$key] = $value;
        }
        
        // Specify which columns are to be ignored. This can be a string or an array.
        //$ignore = 'id';
        $ignore = '';
        // Get the table object from the model.
        $table = $this->getTable( 'match' );
        // Bind the array to the table object.
        $table->bind( $data, $ignore );
        
        if ( !$table->store() )
        {
            JError::raiseError(500, $db->getErrorMsg());
        }
        else
        {
            
        }
  
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' table<br><pre>'.print_r($table,true).'</pre>'),'Notice');
        
    }
    
    
    /**
	 * Method to load content person data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function getData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $jinput->getInt('cfg_which_database',0) );
        $query = $db->getQuery(true);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jinput<br><pre>'.print_r($jinput,true).'</pre>'),'');
        
	   $this->_id = $jinput->getVar('matchid','0');
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _id -> '.$this->_id.''),'');  
       
//		// Lets load the content if it doesn't already exist
//		if (empty($this->_data))
//		{
		
        $query->select('m.*');
        $query->select('t1.name as hometeam ');
        $query->select('t2.name as awayteam ');
        
        $query->from('#__sportsmanagement_match AS m');
        $query->join('LEFT','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('LEFT','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
        $query->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
        
        $query->where('m.id = '.(int)$this->_id);
        $db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
//        	$query='SELECT * FROM #__sportsmanagement_match WHERE id = '.(int) $this->_id;
//			$this->_db->setQuery($query);
			$this->_data = $db->loadObject();
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _data<br><pre>'.print_r($this->_data,true).'</pre>'),'');
            
			return $this->_data;
//		}
//		return true;
	}


	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$cfg_which_media_tool = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('cfg_which_media_tool',0);
        $app = JFactory::getApplication('site');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
        
//        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('ph_player',''));
//        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
//        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
    
}
