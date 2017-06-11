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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelteam extends JSMModelAdmin
{
    static $change_training_date = false;
    
    
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
        
        $this->app = JFactory::getApplication();
        $this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
        $this->club_id = $this->app->getUserState( "$this->option.club_id", '0' );
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->club_id ,true).'</pre>'),'');
	
	}
    
    /**
     * sportsmanagementModelteam::getTeamLogo()
     * 
     * @param mixed $team_id
     * @return
     */
    public static function getTeamLogo($team_id)
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        
        // Select some fields
		$query->select('c.logo_small,c.country,t.name,t.id as team_id');
        // From table
		$query->from('#__sportsmanagement_team t');
        $query->join('LEFT', '#__sportsmanagement_club c ON c.id = t.club_id');
        $query->where('t.id = '.$team_id);
        

        $db->setQuery( $query );
        $result = $db->loadObjectList();

        return $result;
    }
    
    /**
	 * return 
	 *
	 * @param int team_id
	 * @return int
	 */
	function getTeam($team_id=0,$pro_team_id=0)
	{
//	   $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//		$db		= JFactory::getDbo();
//		$query	= $db->getQuery(true);
        $this->jsmquery->clear();
        // Select some fields
		$this->jsmquery->select('t.*');
        // From table
		$this->jsmquery->from('#__sportsmanagement_team t');
        
        if ( $team_id)
        {
        $this->jsmquery->where('t.id = '.$team_id);
        }
        else
        {
        $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $this->jsmquery->where('st.id = '.$pro_team_id); 
        }
        
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');

		$this->jsmdb->setQuery($this->jsmquery);
		return $this->jsmdb->loadObject();
	}
 
    /**
	* Method to delete team trainingdata
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
    function DeleteTrainigData($id)
    {
        $option = JRequest::getCmd('option');
		$app	= JFactory::getApplication();
        
    $db = JFactory::getDbo();
 
$query = $db->getQuery(true);
 
// delete all custom keys
$conditions = array(
    $db->quoteName('id') . '='.$id
);
 
$query->delete($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata'));
$query->where($conditions);
 
$db->setQuery($query);    
if (!$db->query())
		{
			
            $app->enqueueMessage(JText::_('sportsmanagementModelteam DeleteTrainigData<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
        
        // store the variable that we would like to keep for next time
        // function syntax is setUserState( $key, $value );
        self::$change_training_date = true; 
        $app->setUserState( "$option.change_training_date", self::$change_training_date);
        
     return true;           
    }
        
    /**
	* Method to update team trainingdata
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
    function UpdateTrainigData($post)
    {
        $option = JRequest::getCmd('option');
		$app	= JFactory::getApplication();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
        
        //$db		= $this->getDbo();
		//$query	= $db->getQuery(true);
    
    for($a=0; $a < count($post['tdids']); $a++ )    
    {
        $rowtraining = JTable::getInstance( 'TeamTrainingData', 'sportsmanagementTable' );
        $rowtraining->load( (int)$post['tdids'][$a] );
  
        // Create an object for the record we are going to update.
            $object = new stdClass();
            // Must be a valid primary key value.
            $object->id = $post['tdids'][$a];
            $object->time_start = sportsmanagementHelper::time_to_sec($post['time_start'][$post['tdids'][$a]].':00');
            $object->time_end = sportsmanagementHelper::time_to_sec($post['time_end'][$post['tdids'][$a]].':00');
            $object->place = $post['place'][$post['tdids'][$a]];
            $object->notes = $post['notes'][$post['tdids'][$a]];
            $object->dayofweek = $post['dayofweek'][$post['tdids'][$a]];
                       
        // Update their details in the table using id as the primary key.
        $result_update = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata', $object, 'id', true);
        
        if( $object->time_start <> $rowtraining->time_start ||
        $object->time_end <> $rowtraining->time_end ||
        $object->place <> $rowtraining->place ||
        $object->notes <> $rowtraining->notes ||
        $object->dayofweek <> $rowtraining->dayofweek 
         )
        {
        self::$change_training_date = true;    
        }
    
    }
    
    // store the variable that we would like to keep for next time
    // function syntax is setUserState( $key, $value );
    $app->setUserState( "$option.change_training_date", self::$change_training_date);
       
    return true; 
    }
    
    /**
	* Method to return a team trainingdata array
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
	function getTrainigData($team_id=0,$pro_team_id=0)
	{
		$option = JRequest::getCmd('option');
		$app	= JFactory::getApplication();
        //$db		= $this->getDbo();
		$query	= JFactory::getDbo()->getQuery(true);
        // Select some fields
		$query->select('tt.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata as tt');
        
        if ( $team_id)
        {
        $query->where('tt.team_id = '.$team_id);
        }
        else
        {
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = tt.team_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');   
        $query->where('pt.id = '.$pro_team_id); 
        }
        
        
        $query->order('dayofweek ASC');
        JFactory::getDbo()->setQuery($query);
		
		if (!$result = JFactory::getDbo()->loadObjectList())
		{
			$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_NO_TRAINING'),'Notice');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
			return false;
		}
		return $result;
	}

	/**
	* Method to add a team trainingdata 
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
    function addNewTrainigData($team_id)
	{
		$option = JRequest::getCmd('option');
		$app	= JFactory::getApplication();
        
        // Get a db connection.
        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('team_id');
        // Insert values.
        $values = array($team_id);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->query())
		{
			
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
        $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_INSERT_TRAINING'),'Notice');
        
        // store the variable that we would like to keep for next time
        // function syntax is setUserState( $key, $value );
        self::$change_training_date = true; 
        $app->setUserState( "$option.change_training_date", self::$change_training_date);
    
		return true;
	}
    
    
    /**
     * sportsmanagementModelteam::saveshort()
     * 
     * @return
     */
    public function saveshort()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT');
        }
        $post = JRequest::get('post');
        
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        $my_text = 'pks <pre>'.print_r($pks,true).'</pre>';    
        $my_text .= 'post <pre>'.print_r($post,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        //$result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblProject = & $this->getTable();
			$tblProject->id = $pks[$x];
            $tblProject->sports_type_id	= $post['sportstype'.$pks[$x]];
            $tblProject->agegroup_id	= $post['agegroup'.$pks[$x]];

			if(!$tblProject->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE');
	}
    
    
    
    
	
}
