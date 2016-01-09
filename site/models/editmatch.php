<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
		$cfg_which_media_tool = JComponentHelper::getParams(JRequest::getCmd('option'))->get('cfg_which_media_tool',0);
        $app = JFactory::getApplication('site');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
        
//        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams(JRequest::getCmd('option'))->get('ph_player',''));
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
