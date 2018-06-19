<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      predictionranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionranking
 */
 
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.model' );
//jimport('joomla.application.component.modelitem');
jimport('joomla.filesystem.file');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper') ;
jimport( 'joomla.utilities.utility' );

//require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
//require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'prediction.php' );

/**
 * sportsmanagementModelPredictionRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionRanking extends JSMModelList
{
	var $_roundNames = null;
    var $predictionGameID = 0;
static $limitstart = 0;
static $limit = 0;
	
   /**
   * Items total
   * @var integer
   */
  var $_total = null;
 
  /**
   * Pagination object
   * @var object
   */
  var $_pagination = null;
  
  
	/**
	 * sportsmanagementModelPredictionRanking::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
		parent::__construct();
                
        $this->ranking_array = $jinput->getVar('ranking_array','');
        
//        $app->enqueueMessage(JText::_(__METHOD__.' ranking_array<br><pre>'.print_r($this->ranking_array,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' pggroup<br><pre>'.print_r($this->pggroup,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' _REQUEST<br><pre>'.print_r($_REQUEST,true).'</pre>'),'');
        
        $prediction = new sportsmanagementModelPrediction();  


        sportsmanagementModelPrediction::$roundID = $jinput->getVar('r','0');
       sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj','0');
       sportsmanagementModelPrediction::$from = $jinput->getVar('from',$jinput->getVar('r','0'));
       sportsmanagementModelPrediction::$to = $jinput->getVar('to',$jinput->getVar('r','0'));
       
        sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id','0');
        
        sportsmanagementModelPrediction::$predictionMemberID = $jinput->getInt('uid',0);
        sportsmanagementModelPrediction::$joomlaUserID = $jinput->getInt('juid',0);
        
        sportsmanagementModelPrediction::$pggroup = $jinput->getInt('pggroup',0);
        sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank',0);
        
        sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s',0);
        sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok',0);
        
        sportsmanagementModelPrediction::$type = $jinput->getInt('type',0);
        sportsmanagementModelPrediction::$page = $jinput->getInt('page',1);

	//self::$limitstart = $jinput->getVar('limitstart', 0, '', 'int');
		
/*
if ( $jinput->getVar( "view") == 'predictionranking' )
{
	// Get pagination request variables
	//$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
	//$limitstart = $jinput->getVar('limitstart', 0, '', 'int');

	self::$limit = $jinput->getInt('limit',$app->getCfg('list_limit'));
	self::$limitstart = $jinput->getInt('start',0);
	// In case limit has been changed, adjust it
	//$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
	$this->setState('limit', self::$limit);
	$this->setState('limitstart', self::$limitstart);
}
*/
  
    $getDBConnection = sportsmanagementHelper::getDBConnection();
    parent::setDbo($getDBConnection);
    
	}

	
public function getStart()
{
    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
    //$limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
    $this->setState('list.start', self::$limitstart );
    
    $store = $this->getStoreId('getstart');
    // Try to load the data from internal storage.
    if (isset($this->cache[$store]))
    {
        return $this->cache[$store];
    }
    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if ($start > $total - $limit)
    {
        $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
    }
    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limitstart<br><pre>'.print_r($limitstart,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->limitstart<br><pre>'.print_r($this->limitstart,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' store<br><pre>'.print_r($store,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.start<br><pre>'.print_r($this->getState('list.start'),true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.limit<br><pre>'.print_r($this->getState('list.limit'),true).'</pre>'),'');
    // Add the total to the internal cache.
    $this->cache[$store] = $start;
    return $this->cache[$store];
}	

protected function populateState($ordering = null, $direction = null)
	{
$app = JFactory::getApplication();
$value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
self::$limit = $value;
$this->setState('list.limit', self::$limit);
$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
self::$limitstart = (self::$limit != 0 ? (floor($value / self::$limit) * self::$limit) : 0);
$this->setState('list.start', self::$limitstart);



//$app->enqueueMessage(JText::_(__METHOD__.' limit <br><pre>'.print_r(self::$limit,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' limitstart <br><pre>'.print_r(self::$limitstart,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' limit cfg<br><pre>'.print_r($app->getCfg('list_limit', 0),true).'</pre>'),'');
		
	
}
	
	
	function getLimit()
	{
		return $this->getState('list.limit');
	}
	
	function getLimitStart()
	{
		return $this->getState('list.start');
	}
/**
 * sportsmanagementModelPredictionRanking::_buildQuery()
 * 
 * @return
 */
function _buildQuery()
{
    $option = JFactory::getApplication()->input->getCmd('option');    
    $app = JFactory::getApplication();
    // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
    
    // Select some fields
    $query->select('pm.id AS pmID,pm.user_id AS user_id,pm.picture AS avatar,pm.group_id,pm.show_profile AS show_profile,pm.champ_tipp AS champ_tipp,pm.aliasName as aliasName');
    $query->select('u.name AS name');
    $query->select('pg.id as pg_group_id,pg.name as pg_group_name');
    $query->from('#__sportsmanagement_prediction_member AS pm');
    $query->join('INNER', '#__users AS u ON u.id = pm.user_id');
    $query->join('LEFT', '#__sportsmanagement_prediction_groups as pg on pg.id = pm.group_id');
    $query->where('pm.prediction_id = '.(int)sportsmanagementModelPrediction::$predictionGameID);
            

    if ( (int)sportsmanagementModelPrediction::$pggrouprank )
    {
    $query->group('pm.group_id');
    $query->order('pm.group_id ASC');  
    }   
    else
    {
    $query->order('pm.id ASC');     
    }         
	
                
return $query;                            
}

/**
 * sportsmanagementModelPredictionRanking::getData()
 * 
 * @return
 */
function getData() 
  {
 	// if data hasn't already been obtained, load it
 	if (empty($this->_data)) 
     {
 	    $query = self::_buildQuery();
        //$query = $this->getPredictionMember();
 	    //$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));	
	$this->_data = $this->_getList($query);		
 	}
 	return $this->_data;
  }
  
/**
 * sportsmanagementModelPredictionRanking::getTotal()
 * 
 * @return
 */
function getTotal()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_total)) 
     {
 	    $query = self::_buildQuery();
        //$query = $this->getPredictionMember();
 	    $this->_total = $this->_getListCount($query);	
 	}
 	return $this->_total;
  }

/**
 * sportsmanagementModelPredictionRanking::getPagination()
 * 
 * @return
 */
	/*
function getPagination()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_pagination)) 
     {
 	    jimport('joomla.html.pagination');
 	    $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
 	}
 	return $this->_pagination;
  }    
 */
	
    /**
     * sportsmanagementModelPredictionRanking::getChampLogo()
     * 
     * @param mixed $ProjectID
     * @param mixed $champ_tipp
     * @return
     */
    function getChampLogo($ProjectID,$champ_tipp)
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $projectteamid = 0;
    
    if ( $champ_tipp )
    {
    $sChampTeamsList = explode(';',$champ_tipp);
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sChampTeamsList'.'<pre>'.print_r($sChampTeamsList,true).'</pre>' ),'');
foreach ($sChampTeamsList AS $key => $value)
    {
    $dChampTeamsList[] = explode(',',$value);
    }
foreach ($dChampTeamsList AS $key => $value)
    {
    $champTeamsList[$value[0]] = $value[1];
    }    
    
if ( isset($champTeamsList[(int)$ProjectID]) )
{
    $projectteamid = $champTeamsList[(int)$ProjectID];  
    if ( $projectteamid )
    {
    $teaminfo = sportsmanagementModelProject::getTeaminfo($projectteamid,0);
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectteamid'.'<pre>'.print_r($projectteamid,true).'</pre>' ),'');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teaminfo'.'<pre>'.print_r($teaminfo,true).'</pre>' ),'');
    return $teaminfo;
    }
    }
	    
    }
    else
    {
        return false;
    }
      
    }
   


	/**
	 * sportsmanagementModelPredictionRanking::createMatchdayList()
	 * 
	 * @param mixed $project_id
	 * @param mixed $round_ids
	 * @return
	 */
	function createMatchdayList($project_id, $round_ids = NULL)
	{
		$from_matchday = array();
		$from_matchday[]= JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'));
		$from_matchday = array_merge($from_matchday,sportsmanagementModelPrediction::getRoundNames($project_id,'ASC', $round_ids));
		return $from_matchday;
	}
    
    

}
?>
