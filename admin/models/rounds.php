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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelRounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelRounds extends JModelList
{
	var $_identifier = "rounds";
    static $_project_id = 0;
	
    /**
     * sportsmanagementModelRounds::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {
            $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
                //$this->_project_id	= $app->getUserState( "$option.pid", '0' );
                //self::$_project_id	= $app->getUserState( "$option.pid", '0' );
                self::$_project_id	= JRequest::getInt('pid',0);
                $app->setUserState( "$option.pid", self::$_project_id ); 
                $config['filter_fields'] = array(
                        'r.name',
                        'r.alias',
                        'r.roundcode',
                        'r.round_date_first',
                        'r.round_date_last',
                        'r.id',
                        'r.ordering'
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
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('r.roundcode', 'asc');
	}
    
	/**
	 * sportsmanagementModelRounds::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = JFactory::getDbo()->getQuery(true);
        $subQuery1= JFactory::getDbo()->getQuery(true);
        $subQuery2= JFactory::getDbo()->getQuery(true);
        $subQuery3= JFactory::getDbo()->getQuery(true);
        
		// Select some fields
		$query->select('r.*');
		// From the rounds table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round as r');
        // join match
        $subQuery1->select('count(published)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery1->where('round_id=r.id and published=0');
        // join match
        $subQuery2->select('count(*)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery2->where('round_id=r.id AND cancel=0 AND (team1_result is null OR team2_result is null)');
        // join match
        $subQuery3->select('count(*)');
        $subQuery3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery3->where('round_id=r.id');
        
        $query->select('('.$subQuery1.') AS countUnPublished');
        $query->select('('.$subQuery2.') AS countNoResults');
        $query->select('('.$subQuery3.') AS countMatches');
        
        
       //$query->where(' r.project_id = '.$this->_project_id);
       $query->where(' r.project_id = '.self::$_project_id);
        
       if ($this->getState('filter.search'))
		{
        $query->where(' LOWER(r.name) LIKE '.JFactory::getDbo()->Quote('%'.$this->getState('filter.search').'%'));
		}
       
        $query->order(JFactory::getDbo()->escape($this->getState('list.ordering', 'r.roundcode')).' '.
                JFactory::getDbo()->escape($this->getState('list.direction', 'ASC')));

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        return $query;
	}
	
  
  



	
	/**
	 * return count of  project rounds
	 *
	 * @param int project_id
	 * @return int
	 */
	function getRoundsCount($project_id)
	{
	   $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
        $query = $db->getQuery(true);
	  // Select some fields
        $query->select('count(*) AS count');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$project_id);  

		$db->setQuery($query);
		return $db->loadResult();
	}
    
    /**
	 * 
	 * @param int $projectid
	 * @return assocarray
	 */
	public static function getFirstRound($projectid,$cfg_which_database = 0) 
    {
         $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_which_database<br><pre>'.print_r($cfg_which_database,true).'</pre>'),'');
        
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('id, roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->order('roundcode ASC, id ASC');  

		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		if ( !$result = $db->loadAssocList() )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result[0];
	}
	
	/**
	 * 
	 * @param int $projectid
	 * @return assocarray
	 */
	public static function getLastRound($projectid,$cfg_which_database = 0) 
    {
         $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_which_database<br><pre>'.print_r($cfg_which_database,true).'</pre>'),'');
        
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('id, roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->order('roundcode DESC, id DESC');  
        		
		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result[0];
	}
    
    /**
	 * 
	 * @param int $roundid
	 * @param int $projectid
	 * @return assocarray
	 */
	public static function getPreviousRound($roundid, $projectid,$cfg_which_database = 0) 
    {
         $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('id, roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->order('id ASC');  
        

		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		for ($i=0,$n=count($result); $i < $n; $i++) {
			if(isset($result[$i-1])) {
				return $result[$i-1];
			} else {
				return $result[$i];
			}
		}
	}
    
    /**
	 * 
	 * @param int $roundid
	 * @param int $projectid
	 * @return assocarray
	 */
	public static function getNextRound($roundid, $projectid,$cfg_which_database = 0) 
    {
         $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
       // Select some fields
        $query->select('id, roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->order('id ASC');  
        
	
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		for ($i=0,$n=count($result); $i < $n; $i++) {
			if($result[$i]['id']==$roundid) {
				if(isset($result[$i+1])) {
					return $result[$i+1];
				} else {
					return $result[$i];
				}
			}
		}
	}
    
    /**
	 * Get the next round by todays date
	 * @param int $project_id
	 * @return assocarray
	 */
	function getNextRoundByToday($projectid)
	{
	    $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
        $query = $db->getQuery(true);
        
	  // Select some fields
        $query->select('id, roundcode, round_date_first , round_date_last');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->where('DATEDIFF(CURDATE(), DATE(round_date_first)) < 0');
        $query->order('round_date_first ASC'); 
        
	
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;		
	}
    
    /**
	 * return project rounds as array of objects(roundid as value, name as text)
	 *
	 * @param string $ordering
	 * @return array
	 */
	public static function getRoundsOptions($project_id, $ordering='ASC',$cfg_which_database = 0)
	{
	    $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'   ),'');
        
      // Select some fields
        $query->select('id as value,name as text, id, name, round_date_first, round_date_last, roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$project_id);  
        $query->order('roundcode '.$ordering); 

		$db->setQuery($query);
        $result = $db->loadObjectList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'   ),'');
        
        return $result;
	}
	
	

	
}
?>