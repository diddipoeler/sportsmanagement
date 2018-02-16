<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      rounds.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rounds
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelRounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelRounds extends JSMModelList
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
                parent::setDbo($this->jsmdb);
                self::$_project_id	= $this->jsmjinput->getInt('pid',0);
                
                if ( !self::$_project_id )
                {
                self::$_project_id	= $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );    
                }
                
                $this->jsmapp->setUserState( "$this->jsmoption.pid", self::$_project_id ); 
                
                
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
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	
		// List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        // Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'r.roundcode';
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
	 * sportsmanagementModelRounds::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
	
	// Create a new query object.		
	$this->jsmquery->clear();	
	// Select some fields
	$this->jsmquery->select('p.season_id');
        // From table
	$this->jsmquery->from('#__sportsmanagement_project AS p');
	$this->jsmquery->where('p.id = ' . self::$_project_id);	
	$this->jsmdb->setQuery($this->jsmquery);	
	$this->_season_id = $this->jsmdb->loadResult();	
	$this->jsmapp->setUserState( "$this->jsmoption.season_id", $this->_season_id);
		
	// Create a new query object.		
	$this->jsmquery->clear();
        $this->jsmsubquery1->clear();
        $this->jsmsubquery2->clear();
        $this->jsmsubquery3->clear();
        
	// Select some fields
	$this->jsmquery->select('r.*');
	// From the rounds table
	$this->jsmquery->from('#__sportsmanagement_round as r');
        // join match
        $this->jsmsubquery1->select('count(published)');
        $this->jsmsubquery1->from('#__sportsmanagement_match ');
        $this->jsmsubquery1->where('round_id=r.id and published=0');
        // join match
        $this->jsmsubquery2->select('count(*)');
        $this->jsmsubquery2->from('#__sportsmanagement_match ');
        $this->jsmsubquery2->where('round_id=r.id AND cancel=0 AND (team1_result is null OR team2_result is null)');
        // join match
        $this->jsmsubquery3->select('count(*)');
        $this->jsmsubquery3->from('#__sportsmanagement_match ');
        $this->jsmsubquery3->where('round_id=r.id');
        
        $this->jsmquery->select('('.$this->jsmsubquery1.') AS countUnPublished');
        $this->jsmquery->select('('.$this->jsmsubquery2.') AS countNoResults');
        $this->jsmquery->select('('.$this->jsmsubquery3.') AS countMatches');
        
        
       //$query->where(' r.project_id = '.$this->_project_id);
       $this->jsmquery->where(' r.project_id = '.self::$_project_id);
        
       if ($this->getState('filter.search'))
		{
        $this->jsmquery->where(' LOWER(r.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
		}
       
       if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('r.published = '.$this->getState('filter.state'));	
		}
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'r.roundcode')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        return $this->jsmquery;
	}
	
	/**
	 * return count of  project rounds
	 *
	 * @param int project_id
	 * @return int
	 */
	function getRoundsCount($project_id)
	{
	  // Create a new query object.		
		$this->jsmquery->clear();
      // Select some fields
        $this->jsmquery->select('count(*) AS count');
        // From the table
		$this->jsmquery->from('#__sportsmanagement_round');
        $this->jsmquery->where('project_id = '.$project_id);  

		try{
        $this->jsmdb->setQuery($this->jsmquery);
		return $this->jsmdb->loadResult();
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
	}
    
    /**
	 * 
	 * @param int $projectid
	 * @return assocarray
	 */
	public static function getFirstRound($projectid,$cfg_which_database = 0) 
    {
         $app = JFactory::getApplication();
        $option = $app->input->getCmd('option');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_which_database<br><pre>'.print_r($cfg_which_database,true).'</pre>'),'');
        
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS( \':\', id, alias ) AS id');
        $query->select('id AS round_id');
        $query->select('roundcode');
        // From the table
		$query->from('#__sportsmanagement_round');
        $query->where('project_id = '.$projectid);  
        $query->order('roundcode ASC, id ASC');  

		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		if ( !$result = $db->loadAssocList() )
		{
			//sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
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
		$app = JFactory::getApplication();
$option = $app->input->getCmd('option');        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_which_database<br><pre>'.print_r($cfg_which_database,true).'</pre>'),'');
        
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS( \':\', id, alias ) AS id');
        $query->select('id AS round_id');
        $query->select('roundcode');
        // From the table
		$query->from('#__sportsmanagement_round');
        $query->where('project_id = '.$projectid);  
        $query->order('roundcode DESC, id DESC');  
        		
		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		if (!$result=$db->loadAssocList())
		{
			//sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
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
		$app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS( \':\', id, alias ) AS id');
        $query->select('roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->order('id ASC');  
        

		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			//sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
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
		$app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
       // Select some fields
       $query->select('CONCAT_WS( \':\', id, alias ) AS id');
        $query->select('roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$projectid);  
        $query->order('id ASC');  
        
	
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			//sportsmanagementModeldatabasetool::writeErrorLog(__CLASS__, __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
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
	  // Select some fields
      $this->jsmquery->clear();
        $this->jsmquery->select('id, roundcode, round_date_first , round_date_last');
        // From the table
		$this->jsmquery->from('#__sportsmanagement_round');
        $this->jsmquery->where('project_id = '.$projectid);  
        $this->jsmquery->where('DATEDIFF(CURDATE(), DATE(round_date_first)) < 0');
        $this->jsmquery->order('round_date_first ASC'); 
	try{
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadAssocList();
		return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }		
	}
    
    /**
	 * return project rounds as array of objects(roundid as value, name as text)
	 *
	 * @param string $ordering
	 * @return array
	 */
	public static function getRoundsOptions($project_id, $ordering='ASC',$cfg_which_database = 0)
	{
		$app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'   ),'');
        
      // Select some fields
        //$query->select('id as value,name as text, id, name, round_date_first, round_date_last, roundcode');
        $query->select('CONCAT_WS( \':\', id, alias ) AS value');
        $query->select('name AS text');
        $query->select('id, name, round_date_first, round_date_last, roundcode');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = '.$project_id);  
        $query->order('roundcode '.$ordering); 

		$db->setQuery($query);
        $result = $db->loadObjectList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'   ),'');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'result <pre>'.print_r($result,true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        return $result;
	}
	
	

	
}
?>
