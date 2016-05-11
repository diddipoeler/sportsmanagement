<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelTeamPersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelTeamPersons extends JModelList
{
	var $_identifier = "teampersons";
    var $_project_id = 0;
    var $_season_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;
    var $_persontype = 0;
    
    
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
                        'ppl.person_id',
                        'ppl.project_position_id',
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
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
		//$app = JFactory::getApplication('administrator');
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);
        
        //$temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
        
        if ( JRequest::getVar('team_id') )
        {
        $this->setState('filter.team_id', JRequest::getVar('team_id') );    
        }
        else
        {
		$this->setState('filter.team_id', $app->getUserState( "$option.team_id", '0' ) );
        }
        
        if ( JRequest::getVar('persontype') )
        {
        $this->setState('filter.persontype', JRequest::getVar('persontype') );    
        }
        else
        {
        $this->setState('filter.persontype', $app->getUserState( "$option.persontype", '0' ) );
        }
        
        if ( JRequest::getVar('project_team_id') )
        {
        $this->setState('filter.project_team_id', JRequest::getVar('project_team_id') );    
        }
        else
        {
        $this->setState('filter.project_team_id', $app->getUserState( "$option.project_team_id", '0' ) );
        }
        
        $this->setState('filter.pid', $app->getUserState( "$option.pid", '0' ) );
        $this->setState('filter.season_id', $app->getUserState( "$option.season_id", '0' ) );

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('ppl.lastname', 'asc');
	}
    
    
    /**
	 * sportsmanagementModelTeamPersons::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(); 
		$query = $db->getQuery(true);
		        
        $this->_project_id	= $app->getUserState( "$option.pid", '0' );
            
        $query->select('ppl.id,ppl.firstname,ppl.lastname,ppl.nickname,ppl.picture,ppl.id as person_id,ppl.injury,ppl.suspension,ppl.away,ppl.ordering,ppl.published,ppl.checked_out,ppl.checked_out_time  ');
		$query->select('ppl.position_id as person_position_id');
        $query->select('tp.id as tpid, tp.market_value, tp.jerseynumber,tp.picture as season_picture');
		$query->select('u.name AS editor');
        $query->select('st.season_id AS season_id,st.id as projectteam_id');
        $query->select('ppos.id as project_position_id');

        $query->from('#__sportsmanagement_person AS ppl');
        $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
        $query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = ppl.id');
        $query->join('LEFT',' #__sportsmanagement_project_position AS ppos ON ppos.id = ppp.project_position_id ');
        $query->join('LEFT', '#__users AS u ON u.id = tp.checked_out');

        $query->where("ppl.published = 1");
        $query->where('st.team_id = '.$this->getState('filter.team_id') );
        $query->where('st.season_id = '.$this->getState('filter.season_id') );
        $query->where('tp.season_id = '.$this->getState('filter.season_id') );
        $query->where('tp.persontype = '.$this->getState('filter.persontype') );
        $query->where('ppp.persontype = '.$this->getState('filter.persontype') );
        $query->where('ppp.project_id = '.$this->_project_id );
        
        $query->where("ppos.id IS NOT NULL");
        
        if ($this->getState('filter.search'))
		{
        $query->where('(LOWER(ppl.lastname) LIKE ' . $db->Quote( '%' . $this->getState('filter.search') . '%' ).
						   'OR LOWER(ppl.firstname) LIKE ' . $db->Quote( '%' . $this->getState('filter.search') . '%' ) .
						   'OR LOWER(ppl.nickname) LIKE ' . $db->Quote( '%' . $this->getState('filter.search') . '%' ) . ')');
        }
            
        $query->order($db->escape($this->getState('list.ordering', 'ppl.lastname')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
             
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
	    {
        $my_text = 'dump<pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
        return $query;
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
    function checkProjectPositions($project_id,$persontype,$team_id,$season_id)
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
       
       // Select some fields
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
                $columns = array('person_id','project_id','project_position_id','persontype');
                // Insert values.
                $values = array($row->person_id,$project_id,$row->project_position_id,$persontype);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($insertquery->dump(),true).'</pre>'),'Notice');
                
                if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
                {
                    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($insertquery->dump(),true).'</pre>'),'Error');
                    $app->enqueueMessage(__METHOD__.' '.__LINE__.' message<br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
                    $app->enqueueMessage(__METHOD__.' '.__LINE__.' nummer<br><pre>'.print_r($db->getErrorNum(), true).'</pre><br>','Error');
                }
                else
                {
                    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($insertquery->dump(),true).'</pre>'),'Notice');
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
