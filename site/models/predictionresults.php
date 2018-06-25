<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      predictionresults.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage predictionresults
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
//require_once(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'prediction.php' );

/**
 * sportsmanagementModelPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionResults extends JSMModelList {

    var $predictionGameID = 0;

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
    var $config = array();
    var $configavatar = array();
    static $roundID = 0;
static $limitstart = 0;
static $limit = 0;
    /**
     * sportsmanagementModelPredictionResults::__construct()
     * 
     * @return
     */
    function __construct() {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

//    $this->predictionGameID		= $jinput->getInt('prediction_id',		0);
//		$this->predictionMemberID	= $jinput->getInt('uid',	0);
//		$this->joomlaUserID			= $jinput->getInt('juid',	0);
//		self::$roundID				= $jinput->request->get('r', '0', 'STR');
//        $this->pggroup				= $jinput->getInt('pggroup',		0);
//        $this->pggrouprank			= $jinput->getInt('pggrouprank',		0);
//		$this->pjID					= $jinput->getInt('pj',		0);
//		$this->isNewMember			= $jinput->getInt('s',		0);
//		$this->tippEntryDone		= $jinput->getInt('eok',	0);
//
//		$this->from  				= $jinput->getInt('from',self::$roundID);
//		$this->to	 				= $jinput->getInt('to',	self::$roundID);
//		$this->type  				= $jinput->getInt('type',	0);
//
//		$this->page  				= $jinput->getInt('page',	1);

        $prediction = new sportsmanagementModelPrediction();

//        sportsmanagementModelPrediction::$predictionGameID = $this->predictionGameID;
//        sportsmanagementModelPrediction::$predictionMemberID = $this->predictionMemberID;
//        sportsmanagementModelPrediction::$joomlaUserID = $this->joomlaUserID;
//        sportsmanagementModelPrediction::$roundID = self::$roundID;
//        sportsmanagementModelPrediction::$pggroup = $this->pggroup;
//        sportsmanagementModelPrediction::$pggrouprank = $this->pggrouprank;
//        sportsmanagementModelPrediction::$pjID = $this->pjID;
//        sportsmanagementModelPrediction::$isNewMember = $this->isNewMember;
//        sportsmanagementModelPrediction::$tippEntryDone = $this->tippEntryDone;
//        sportsmanagementModelPrediction::$from = $this->from;
//        sportsmanagementModelPrediction::$to = $this->to;
//        sportsmanagementModelPrediction::$type = $this->type;
//        sportsmanagementModelPrediction::$page = $this->page;

        sportsmanagementModelPrediction::$roundID = $jinput->getVar('r', '0');
        sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj', '0');
        sportsmanagementModelPrediction::$from = $jinput->getVar('from', $jinput->getVar('r', '0'));
        sportsmanagementModelPrediction::$to = $jinput->getVar('to', $jinput->getVar('r', '0'));

        sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id', '0');

        sportsmanagementModelPrediction::$predictionMemberID = $jinput->getVar('uid', '0');
        sportsmanagementModelPrediction::$joomlaUserID = $jinput->getVar('juid', '0');

        sportsmanagementModelPrediction::$pggroup = $jinput->getVar('pggroup', '0');
        sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank', 0);

        sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s', 0);
        sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok', 0);

        sportsmanagementModelPrediction::$type = $jinput->getInt('type', 0);
        sportsmanagementModelPrediction::$page = $jinput->getInt('page', 1);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' roundID<br><pre>'.print_r(self::$roundID,true).'</pre>'),'');

        if ((int) sportsmanagementModelPrediction::$pjID == 0) {
            $pred_proj = sportsmanagementModelPrediction::getPredictionProjectNames(sportsmanagementModelPrediction::$predictionGameID, 'ASC', 1);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pred_proj<br><pre>'.print_r($pred_proj,true).'</pre>'),'');

            if (isset($pred_proj[0])) {
                sportsmanagementModelPrediction::$pjID = $pred_proj[0]->slug;
            }
        }

        sportsmanagementModelPrediction::checkRoundID(sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$roundID);

        parent::__construct();

        //$this->pggrouprank			= JFactory::getApplication()->input->getInt('pggrouprank',		0);
        //$this->predictionGameID	= $jinput->getInt('prediction_id',0);
/*
        if (JFactory::getApplication()->input->getVar("view") == 'predictionresults') {
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
     * sportsmanagementModelPredictionResults::getPagination()
     * 
     * @return
     */
	/*
    function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }
*/
    /**
     * sportsmanagementModelPredictionResults::getTotal()
     * 
     * @return
     */
    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            //$query = $this->_buildQuery();
            $query = sportsmanagementModelPrediction::getPredictionMembersList($this->config, $this->configavatar, true);
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    /**
     * sportsmanagementModelPredictionResults::getData()
     * 
     * @return
     */
    function getData() {
        // if data hasn't already been obtained, load it
        if (empty($this->_data)) {
            //$query = $this->_buildQuery();
            $query = sportsmanagementModelPrediction::getPredictionMembersList($this->config, $this->configavatar, true);
            $this->_data = $this->_getList($query);
        }
        return $this->_data;
    }

    /**
     * sportsmanagementModelPredictionResults::getMatches()
     * 
     * @param mixed $roundID
     * @param mixed $project_id
     * @param mixed $match_ids
     * @param mixed $round_ids
     * @param mixed $proteams_ids
     * @return
     */
    static function getMatches($roundID, $project_id, $match_ids, $round_ids, $proteams_ids, $show_logo_small_overview = '') {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = JFactory::getDocument();

//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' roundID<br><pre>'.print_r($roundID,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' match_ids<br><pre>'.print_r($match_ids,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round_ids<br><pre>'.print_r($round_ids,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' proteams_ids<br><pre>'.print_r($proteams_ids,true).'</pre>'),'');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);

        if ((int) $roundID == 0) {
            $roundID = 1;
        }

        $query->select('m.id AS mID,m.match_date,m.team1_result AS homeResult,m.team2_result AS awayResult,m.team1_result_decision AS homeDecision,m.team2_result_decision AS awayDecision');
        $query->select('t1.name AS homeName,t1.short_name AS homeShortName,t1.id as homeid');
        $query->select('t2.name AS awayName,t2.short_name AS awayShortName,t2.id as awayid');

        switch ($show_logo_small_overview) {
            case 'logo_small':
            case 'logo_middle':
            case 'logo_big':
                $query->select('c1.' . $show_logo_small_overview . ' AS homeLogo,c1.country AS homeCountry');
                $query->select('c2.' . $show_logo_small_overview . ' AS awayLogo,c2.country AS awayCountry');
                break;
            case 'country_flag':
                $query->select('c1.country AS homeCountry');
                $query->select('c2.country AS awayCountry');
                break;
        }
        //$query->select('c1.logo_small AS homeLogo,c1.country AS homeCountry');
        //$query->select('c2.logo_small AS awayLogo,c2.country AS awayCountry');
//        // das grosse logo muss auch noch selektiert werden
//        $query->select('c1.logo_big AS homeLogobig');
//        $query->select('c2.logo_big AS awayLogobig');

        $query->from('#__sportsmanagement_match AS m');
        $query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');

        $query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id');
        $query->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id');
        $query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 on st1.id = pt1.team_id');
        $query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 on st2.id = pt2.team_id');

        $query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');

        $query->join('LEFT', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');
        $query->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');


        $query->where('r.project_id = ' . (int) $project_id);
        //$query->where('r.id = '.$roundID);

        $query->where('(m.cancel IS NULL OR m.cancel = 0)');
        $query->where('m.published = 1');

        // bestimmte mannschaften selektieren
        if ($proteams_ids) {
            $query->where('( m.projectteam1_id IN (' . implode(',', $proteams_ids) . ')' . ' OR ' . 'm.projectteam2_id IN (' . implode(',', $proteams_ids) . ') )');
        }

        // bestimmte runden selektieren
        if ($round_ids) {
            $query->where('r.id IN (' . implode(',', $round_ids) . ')');
        } else {
            $query->where('r.id = ' . (int) $roundID);
        }

        // bestimmte spiele selektieren
        if ($match_ids) {
            $query->where('m.id IN (' . implode(',', $match_ids) . ')');
        }

        $query->order('m.match_date, m.id ASC');

        $db->setQuery($query);
        $results = $db->loadObjectList();
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $results;
    }

    /**
     * sportsmanagementModelPredictionResults::showClubLogo()
     * 
     * @param mixed $clubLogo
     * @param mixed $teamName
     * @return
     */
    function showClubLogo($clubLogo, $teamName) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }


        $output = '';
        if ((!isset($clubLogo)) || ($clubLogo == '') || (!file_exists($clubLogo))) {
            $clubLogo = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
        }
        $imgTitle = JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_LOGO_OF', $teamName);
        $output .= JHTML::image($clubLogo, $imgTitle, array(' width' => 20, ' title' => $imgTitle));
        return $output;
    }

}

?>
