<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die;

//jimport('joomla.application.component.model');
//require_once JPATH_COMPONENT . '/models/item.php';

// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');


/**
 * sportsmanagementModelTreetonode
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelTreetonode extends JSMModelAdmin
{

//var $_project_id = 0;

/**
 * sportsmanagementModelTreetonode::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        
        //$this->_project_id = $this->jsmjinput->get('pid');
        
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($this->jsmjinput->get('pid'),true).'</pre>'),'Notice');
        
        }    
        
        
        /**
         * sportsmanagementModelTreetonode::save()
         * 
         * @param mixed $data
         * @return
         */
        public function save($data)
	{
	
    //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
    
     // zuerst sichern, damit wir bei einer neuanlage die id haben
       if ( parent::save($data) )
       {
        
        
       } 
       return true;   
       }
       
	//function _loadData()
//	{
//		// Lets load the content if it doesn't already exist
//		if(empty($this->_data))
//		{
//			$query = '	SELECT ttn.*
//							FROM #__joomleague_treeto_node AS ttn
//							WHERE ttn.id = ' . (int) $this->_id;
//			
//			$this->_db->setQuery($query);
//			$this->_data = $this->_db->loadObject();
//			return (boolean) $this->_data;
//		}
//		return true;
//	}
    
    /**
     * sportsmanagementModelTreetonode::getNode()
     * 
     * @param integer $node_id
     * @return
     */
    function getNode($node_id=0)
	{
	$this->jsmquery->clear();
	$this->jsmquery->select('ttn.*');   
    $this->jsmquery->from('#__sportsmanagement_treeto_node AS ttn');  
    $this->jsmquery->where('ttn.id = ' . $node_id );
    $this->jsmdb->setQuery( $this->jsmquery );
    
    //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
    
    return $this->jsmdb->loadObject();
    
    }   

//	function _initData()
//	{
//		// Lets load the content if it doesn't already exist
//		if(empty($this->_data))
//		{
//			$node = new stdClass();
//			$node->id = 0;
//			$node->treeto_id = 0;
//			$node->node = 0;
//			$node->row = 0;
//			$node->bestof = 0;
//			$node->title = null;
//			$node->content = null;
//			$node->team_id = 0;
//			$node->published = 0;
//			$node->is_leaf = 0;
//			$node->is_lock = 0;
//			$node->got_lc = 0;
//			$node->got_rc = 0;
//			$node->checked_out = 0;
//			$node->checked_out_time = 0;
//			$node->modified = null;
//			$node->modified_by = null;
//			
//			$this->_data = $node;
//			return (boolean) $this->_data;
//		}
//		return true;
//	}


	/**
	 * sportsmanagementModelTreetonode::getNodeMatch()
	 * 
	 * @return
	 */
	function getNodeMatch()
	{
////		$option = $this->input->getCmd('option');
////		$app = JFactory::getApplication();
//		// $division_id = $app->getUserState( $option . 'division_id' );
//		$query = ' SELECT mc.id AS mid ';
//		// $query .= ' CONCAT(t1.name, \'_\', mc.team1_result, \':\',
//		// mc.team2_result, \'_\', t2.name) AS text ';
//		$query .= ' ,mc.match_number AS match_number';
//		$query .= ' ,t1.name AS projectteam1';
//		$query .= ' ,mc.team1_result AS projectteam1result';
//		$query .= ' ,mc.team2_result AS projectteam2result';
//		$query .= ' ,t2.name AS projectteam2';
//		$query .= ' ,mc.round_id AS rid ';
//		$query .= ' ,mc.published AS published ';
//		$query .= ' ,ttm.node_id AS node_id ';
//		$query .= ' FROM #__joomleague_match AS mc ';
//		$query .= ' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
//		$query .= ' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
//		$query .= ' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
//		$query .= ' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
//		$query .= ' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
//		$query .= ' LEFT JOIN #__joomleague_treeto_match AS ttm ON mc.id = ttm.match_id ';
//		$query .= ' WHERE ttm.node_id = ' . (int) $this->_id;
//		$query .= ' ORDER BY mid ASC ';
//		$query .= ';';
// Select the required fields from the table.
    $this->jsmquery->clear();
		$this->jsmquery->select('mc.id AS mid,mc.match_number AS match_number,t1.name AS projectteam1,mc.team1_result AS projectteam1result,mc.team2_result AS projectteam2result');
		$this->jsmquery->select('t2.name AS projectteam2,mc.round_id AS rid,mc.published AS published,ttm.node_id AS node_id,r.roundcode AS roundcode, mc.checked_out');
        
		$this->jsmquery->from('#__sportsmanagement_match AS mc');   
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_round AS r ON r.id = mc.round_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ');
       
       $this->jsmquery->where('ttm.node_id = ' . (int) $this->jsmjinput->get('id') );
       
    $this->jsmquery->order('mid ASC');   
       
       //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
       		
		$this->jsmdb->setQuery($this->jsmquery);
		
		if(! $result = $this->jsmdb->loadObjectList())
		{
			$this->setError($this->jsmdb->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}


	/**
	 * sportsmanagementModelTreetonode::setUnpublishNode()
	 * 
	 * @return
	 */
	function setUnpublishNode()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		
		$post = JRequest::get('post');
		$id = (int) $post['id'];
		
		$query = ' UPDATE #__joomleague_treeto_node AS ttn ';
		$query .= ' SET ';
		$query .= ' ttn.published = 0 ';
		$query .= ' WHERE ttn.id = ' . $id;
		$query .= ';';
		$this->_db->setQuery($query);
		$this->_db->query($query);
		
		return true;
	}

//	/**
//	 * Returns a Table object, always creating it
//	 *
//	 * @param
//	 *        	type The table type to instantiate
//	 * @param
//	 *        	string A prefix for the table class name. Optional.
//	 * @param
//	 *        	array Configuration array for model. Optional.
//	 * @return JTable database object
//	 */
//	public function getTable($type = 'TreetoNode',$prefix = 'sportsmanagementTable',$config = array())
//	{
//		return JTable::getInstance($type,$prefix,$config);
//	}

//	/**
//	 * Method to get the record form.
//	 *
//	 * @param array $data
//	 *        	the form.
//	 * @param boolean $loadData
//	 *        	the form is to load its own data (default case), false if not.
//	 * @return mixed JForm object on success, false on failure
//	 */
//	public function getForm($data = array(),$loadData = true)
//	{
//		//// Get the form.
////		$form = $this->loadForm('com_sportsmanagement.' . $this->name,$this->name,array(
////				'load_data' => $loadData
////		));
//        // Get the form.
//		$form = $this->loadForm('com_sportsmanagement.treetonode', 'treetonode', array('control' => 'jform', 'load_data' => $loadData));
//        
//		if(empty($form))
//		{
//			return false;
//		}
//		return $form;
//	}


//	/**
//	 * Method to get the data that should be injected in the form.
//	 *
//	 * @return mixed data for the form.
//	 */
//    protected function loadFormData()
//	{
//		// Check the session for previously entered form data.
//		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->name . '.data',array());
//		if(empty($data))
//		{
//			$data = $this->getItem();
//		}
//		return $data;
//	}

}
