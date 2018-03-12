<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      project.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * sportsmanagementModelProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProject extends JSMModelAdmin
{
	static $db_num_rows = 0;
    var $_tables_to_delete = array();
  
	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
	}	   
    
    /**
	 * return 
	 *
	 * @param int team_id
	 * @return int
	 */
	function getProjectTeam($projectteam_id)
	{
	   $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection(); 
       $query = $db->getQuery(true);
       
       $query->select('t.*');
       $query->from('#__sportsmanagement_team AS t');
       $query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
       $query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
       $query->where('pt.id = ' . $projectteam_id);
       
		$db->setQuery($query);
		return $db->loadObject();
	}
    
    /**
	 * return 
	 *
	 * @param int project_id
	 * @return int
	 */
	public static function getProject($project_id)
	{
	   $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
       //// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(); 
        $query	= $db->getQuery(true);
        $query->select('*');
        $query->from('#__sportsmanagement_project');
        $query->where('id = ' . $project_id);
		
        $db->setQuery($query);
		return $db->loadObject();
	}
   
    
    /**
	 * @param int iDivisionId
	 * return project teams as options
	 * @return unknown_type
	 */
	function getProjectTeamsOptions($project_id,$iDivisionId=0)
	{
		$app	= JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        //$db	= $this->getDbo();
        $db = sportsmanagementHelper::getDBConnection(); 
		$query = $db->getQuery(true);
        $this->project_art_id	= $app->getUserState( "$option.project_art_id", '0' );
        
		//$project_id = $app->getUserState($option . 'project');
        
        if ( $this->project_art_id == 3 )
        {
            // Select some fields
		    $query->select("pt.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text,t.notes");
            // From table
		    $query->from('#__sportsmanagement_person AS t');
            $query->join('LEFT', '#__sportsmanagement_season_person_id AS st on st.person_id = t.id');
            $query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        }
        else
        {
            // Select some fields
		    $query->select('pt.id AS value');
            $query->select('t.name AS text');
            $query->from('#__sportsmanagement_team AS t');
            $query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        }
		
        $query->where('pt.project_id = ' . $project_id);
        
        if( $iDivisionId > 0 )  
        {
            $query->where('pt.division_id = ' . $iDivisionId);
		}
		
        $query->order('text ASC'); 

		$db->setQuery($query);
		$result = $db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}
    
    
    /**
	 * Method to remove projects
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app = JFactory::getApplication();
    // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
    $success = $this->deleteProjectsData($pks);  
    
    if ( $success )
    {
    $app->setUserState( "$option.pid", 0 );     
    return parent::delete($pks);
    }
         
   }
   
    /**
	 * Method to remove all project datas
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function deleteProjectsData($pk=array())
	{
	$app = JFactory::getApplication();
    // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

    $db = sportsmanagementHelper::getDBConnection(); 
    $query = $db->getQuery(true);
    
	$result = false;
    if (count($pk))
		{
			$cids = implode(',',$pk);
            // rounds 
            $query->clear();
            $query->select('r.id');
            $query->from('#__sportsmanagement_round as r');
            $query->where('r.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $rounds = JFactory::getDbo()->loadColumn();
            
            // matches 
            if ( $rounds )
            {
            $query->clear();
            $query->select('m.id');
            $query->from('#__sportsmanagement_match as m');
            $query->where('m.round_id IN ('.implode(",",$rounds).')');
            JFactory::getDBO()->setQuery($query);
            $matches = JFactory::getDbo()->loadColumn();
            }
            
            // project_teams 
            $query->clear();
            $query->select('p.id');
            $query->from('#__sportsmanagement_project_team as p');
            $query->where('p.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $project_teams = JFactory::getDbo()->loadColumn();
            
            // project_referee 
            $query->clear();
            $query->select('p.id');
            $query->from('#__sportsmanagement_project_referee as p');
            $query->where('p.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $project_referee = JFactory::getDbo()->loadColumn();
            
            // project_position 
            $query->clear();
            $query->select('p.id');
            $query->from('#__sportsmanagement_project_position as p');
            $query->where('p.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $project_position = JFactory::getDbo()->loadColumn();
            
            // zu löschende tabellen
            $field = 'project_id';
            $id = implode(",",$pk);
            $temp = new stdClass();
            $temp->table = '_project_position';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            
            $temp = new stdClass();
            $temp->table = '_person_project_position';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            
            $temp = new stdClass();
            $temp->table = '_project_referee';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_project_team';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_round';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_division';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            if ( $rounds )
            {
            $field= 'round_id';
            $id = implode(",",$rounds);
            $temp = new stdClass();
            $temp->table = '_match';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            }
            if ( $matches )
            {
            $field= 'match_id';
            $id = implode(",",$matches);
            $temp = new stdClass();
            $temp->table = '_match_commentary';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_event';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_player';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_referee';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_single';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_staff';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_staff_statistic';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_statistic';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            }
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' export<br><pre>'.print_r($export,true).'</pre>'),'');    
            $this->_tables_to_delete = array_merge($export);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _tables_to_delete<br><pre>'.print_r($this->_tables_to_delete,true).'</pre>'),'');
            
            // jetzt starten wir das löschen
            foreach( $this->_tables_to_delete as $row_to_delete )
            {
            $query->clear();
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.$row_to_delete->table)->where($row_to_delete->field.' IN ('.$row_to_delete->id.')' );
            $db->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
            if ( self::$db_num_rows )
            {
            $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT'.strtoupper($row_to_delete->table).'_ITEMS_DELETED',self::$db_num_rows),'');
            }    
            }
            
            

           
           
        }    
   return true;     
   } 

    /**
	 * Method to update checked project
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	public function saveshort()
	{
		$app = JFactory::getApplication();
        $date = JFactory::getDate();
              
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        // Get the input
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }
        $post = JFactory::getApplication()->input->post->getArray(array());
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
//        $app->enqueueMessage(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
//        $app->enqueueMessage(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        $my_text = 'pks <pre>'.print_r($pks,true).'</pre>';    
        $my_text .= 'post <pre>'.print_r($post,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        //$result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblProject = & $this->getTable();
			$tblProject->id = $pks[$x];
            $tblProject->project_type	= $post['project_type'.$pks[$x]];
            $tblProject->agegroup_id	= $post['agegroup'.$pks[$x]];
            
            if ( $post['league'.$pks[$x]] )
            {
            $tblProject->league_id	= $post['league'.$pks[$x]];
            }

            
            $tblProject->modified = $date->toSql();
            $tblProject->modified_timestamp = sportsmanagementHelper::getTimestamp($date->toSql());

			if(!$tblProject->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
            
            if ( $post['user_field_id'.$pks[$x]] )
{			
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $post['user_field_id'.$pks[$x]];
$object->fieldvalue = $post['user_field'.$pks[$x]];
// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_user_extra_fields_values', $object, 'id');			
}	

		}
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
	}

	
}
