<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      teampersons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teampersons
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelTeamPersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelTeamPersons extends JSMModelList
{
	var $_identifier = "teampersons";
    var $_project_id = 0;
    var $_season_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;
    var $_persontype = 0;
    static $db_num_rows = 0;
    
    /**
     * sportsmanagementModelTeamPersons::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'ppl.lastname',
                        'tp.person_id',
                        'ppl.position_id',
                        'ppl.published',
                        'ppl.ordering',
                        'ppl.picture',
                        'ppl.id',
                        'tp.market_value',
                        'tp.jerseynumber'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = 'ppl.lastname', $direction = 'asc')
	{
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        }

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
        
        if ( JFactory::getApplication()->input->getVar('team_id') )
        {
        $this->setState('filter.team_id', JFactory::getApplication()->input->getVar('team_id') );    
        }
        else
        {
		$this->setState('filter.team_id', $this->jsmapp->getUserState( "$this->jsmoption.team_id", '0' ) );
        }
        
        if ( JFactory::getApplication()->input->getVar('persontype') )
        {
        $this->setState('filter.persontype', JFactory::getApplication()->input->getVar('persontype') );    
        }
        else
        {
        $this->setState('filter.persontype', $this->jsmapp->getUserState( "$this->jsmoption.persontype", '0' ) );
        }
        
        if ( JFactory::getApplication()->input->getVar('project_team_id') )
        {
        $this->setState('filter.project_team_id', JFactory::getApplication()->input->getVar('project_team_id') );    
        }
        else
        {
        $this->setState('filter.project_team_id', $this->jsmapp->getUserState( "$this->jsmoption.project_team_id", '0' ) );
        }
        
        $this->setState('filter.pid', $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' ) );
        $this->setState('filter.season_id', $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' ) );

        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	
		// List state information.
		parent::populateState($ordering, $direction);
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
		
		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'ppl.lastname';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);
		
		
		
		
	}
    
    
    /**
	 * sportsmanagementModelTeamPersons::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		// Create a new query object.		
		$this->jsmquery->clear();
		        
        $this->_project_id	= $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
            
        $this->jsmquery->select('ppl.id,ppl.firstname,ppl.lastname,ppl.nickname,ppl.picture,ppl.id as person_id,ppl.injury,ppl.suspension,ppl.away,ppl.ordering,ppl.checked_out,ppl.checked_out_time  ');
		$this->jsmquery->select('ppl.position_id as person_position_id');
        $this->jsmquery->select('tp.id as tpid, tp.market_value, tp.jerseynumber,tp.picture as season_picture,tp.published');
		$this->jsmquery->select('u.name AS editor');
        $this->jsmquery->select('st.season_id AS season_id,st.id as projectteam_id');
        //$this->jsmquery->select('ppos.id as project_position_id');

        $this->jsmquery->from('#__sportsmanagement_person AS ppl');
        $this->jsmquery->join('INNER','#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
        $this->jsmquery->join('INNER','#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
        //$this->jsmquery->join('LEFT','#__sportsmanagement_person_project_position AS ppp on ppp.person_id = ppl.id');
        //$this->jsmquery->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = ppp.project_position_id ');
        $this->jsmquery->join('LEFT','#__users AS u ON u.id = tp.checked_out');

        $this->jsmquery->where('ppl.published = 1');
        $this->jsmquery->where('st.team_id = '.$this->getState('filter.team_id') );
        $this->jsmquery->where('st.season_id = '.$this->getState('filter.season_id') );
        $this->jsmquery->where('tp.season_id = '.$this->getState('filter.season_id') );
        $this->jsmquery->where('tp.persontype = '.$this->getState('filter.persontype') );
        //$this->jsmquery->where('ppp.persontype = '.$this->getState('filter.persontype') );
        //$this->jsmquery->where('ppp.project_id = '.$this->_project_id );
        
	$this->jsmsubquery1->clear();
        $this->jsmsubquery1->select('ppos.id');
        $this->jsmsubquery1->from('#__sportsmanagement_project_position AS ppos');
        $this->jsmsubquery1->join('LEFT','#__sportsmanagement_person_project_position AS ppp on ppp.project_position_id = ppos.id');
        $this->jsmsubquery1->where('ppp.person_id = ppl.id');
        $this->jsmsubquery1->where('ppp.project_id = '.$this->_project_id );
        $this->jsmsubquery1->where('ppp.persontype = '.$this->getState('filter.persontype') );
	$this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS project_position_id');

		
		
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('tp.published = '.$this->getState('filter.state') );
	}
		
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('(LOWER(ppl.lastname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ).
						   'OR LOWER(ppl.firstname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
						   'OR LOWER(ppl.nickname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) . ')');
        }
            
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'ppl.lastname')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
                
             
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
	    {
        $my_text = 'dump<pre>'.print_r($this->jsmquery->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        }
       
        return $this->jsmquery;
	}
    
    
    /**
     * sportsmanagementModelTeamPersons::PersonProjectPosition()
     * 
     * @return void
     */
    function PersonProjectPosition($project_id,$_persontype)
    {
    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(); 
		$query = $db->getQuery(true);    
        
        // Select some fields
		$query->select('ppl.*');
        // From table
        $query->from('#__sportsmanagement_person_project_position AS ppl');
        $query->where('ppl.project_id = '.$project_id);
        $query->where('ppl.persontype = '.$_persontype);
        
        $db->setQuery($query);
        //$db->query();
        $result = $db->loadObjectList();
        
		if (!$result)
		{
			return false;
		}
		return $result;
        
    }
    
    
    
    /**
     * sportsmanagementModelTeamPersons::checkProjectPositions()
     * 
     * @param mixed $project_id
     * @param mixed $persontype
     * @param mixed $team_id
     * @param mixed $season_id
     * @return
     */
    function checkProjectPositions($project_id,$persontype,$team_id,$season_id,$insert=1)
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.
		//$db	= sportsmanagementHelper::getDBConnection();
        $db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $date = JFactory::getDate();
	    $user = JFactory::getUser();
        $modified = $date->toSql();
	    $modified_by = $user->get('id');
       
/**
 * tabelle: sportsmanagement_person_project_position
 * feld import_id einfügen
 */
$jsm_table = '#__sportsmanagement_person_project_position'; 
try { 
$query = $db->getQuery(true);
$query->clear();
$query = "ALTER TABLE `".$jsm_table."` ADD `import_id` INT(11) NOT NULL DEFAULT '0' "   ;
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
//$result = $db->execute();
}
catch (Exception $e) {
//    // catch any database errors.
//    $db->transactionRollback();
//    JErrorPage::render($e);
}

       // Select some fields
       $query = $db->getQuery(true);
       $query->clear();
		$query->select('stp.person_id,ppos.id as project_position_id');
        $query->from('#__sportsmanagement_season_team_person_id as stp');
        $query->join('INNER','#__sportsmanagement_person AS p ON p.id = stp.person_id'); 
        
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.position_id = p.position_id'); 
        
        $query->where('stp.team_id = '.$team_id);
        $query->where('stp.season_id = '.$season_id);
        $query->where('stp.persontype = '.$persontype);
        $query->where('ppos.project_id = '.$project_id);
        $db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        $result = $db->loadObjectList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
        if ( $result )
        {
            foreach( $result as $row )
            {
                $query->clear();
                $query->select('person_id');
                $query->from('#__sportsmanagement_person_project_position');
                $query->where('person_id = '.$row->person_id);
                $query->where('project_id = '.$project_id);
                $query->where('project_position_id = '.$row->project_position_id);
                $query->where('persontype = '.$persontype);
                $db->setQuery($query);
                $resultcheck = $db->loadResult();
                if ( !$resultcheck )
            // projekt position eintragen
                {
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','project_id','project_position_id','persontype','import_id');
                // Insert values.
                $values = array($row->person_id,$project_id,$row->project_position_id,$persontype,1);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($insertquery->dump(),true).'</pre>'),'Notice');
                
                if ( $insert )
                {
                if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
                {
                    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($insertquery->dump(),true).'</pre>'),'Error');
                    //$app->enqueueMessage(__METHOD__.' '.__LINE__.' message<br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
                    //$app->enqueueMessage(__METHOD__.' '.__LINE__.' nummer<br><pre>'.print_r($db->getErrorNum(), true).'</pre><br>','Error');
                }
                else
                {
                    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($insertquery->dump(),true).'</pre>'),'Notice');
                }
                }
                
                }
            }
        return TRUE;
        }
        else
        {
            return FALSE;
        }

        
    }



	/**
	 * sportsmanagementModelTeamPersons::getProjectTeamplayers()
	 * 
	 * @param mixed $project_team_id
	 * @return
	 */
	function getProjectTeamplayers($team_id = 0,$season_id = 0)
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.
        $db = sportsmanagementHelper::getDBConnection(); 
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' project_team_id -> '.$project_team_id.'<br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' season_id -> '.$season_id.'<br>','Notice');
        
        // Select some fields
		$query->select('ppl.*');
        // From table
        $query->from('#__sportsmanagement_person AS ppl');
        $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id');
        $query->where('st.team_id IN ('.$team_id.')');
        $query->where('st.season_id = '.$season_id);
        $query->where('tp.team_id = '.$season_id);
        
        $db->setQuery($query);
        //$db->query();
        $result = $db->loadObjectList();
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(), true).'</pre><br>','Notice');
                
		if (!$result)
		{
            //$app->enqueueMessage(__METHOD__.' '.__LINE__.' message<br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
            //$app->enqueueMessage(__METHOD__.' '.__LINE__.' nummer<br><pre>'.print_r($db->getErrorNum(), true).'</pre><br>','Error');
			return false;
		}
		return $result;
    }
	
	/**
	 * remove specified players from team
	 * @param $cids player ids
	 * @return int count of removed
	 */
	function remove($cids)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $post = $jinput->post->getArray(array());
        $option = $jinput->getCmd('option');
        
        $project_team_id = $post['project_team_id'];
        $team_id = $post['team_id'];
        $pid = $post['pid'];
        $persontype = $post['persontype'];
        
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' project_team_id<br><pre>'.print_r($project_team_id, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' team_id<br><pre>'.print_r($team_id, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($pid, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' persontype<br><pre>'.print_r($persontype, true).'</pre><br>','Notice');
        
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' cids<br><pre>'.print_r($cids, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        /*
        $count = 0;
		foreach($cids as $cid)
		{
			$object=&$this->getTable('teamplayer');
			if ($object->canDelete($cid) && $object->delete($cid))
			{
				$count++;
			}
			else
			{
				$this->setError(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_TEAMPLAYER',$object->getError()));
			}
		}
		return $count;
        */
	}

}
?>
