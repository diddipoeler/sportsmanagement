<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      predictionentry.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage predictionentry
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * sportsmanagementModelPredictionEntry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionEntry extends JModelLegacy
{

public $_predictionGame	= null;
	public $predictionGameID = 0;

	public $_predictionMember = null;
	public $predictionMemberID = 0;

	public $_predictionProjectS	= null;
	public $predictionProjectSIDs = null;

	public $_predictionProject = null;
	public $predictionProjectID	= null;
	public $show_debug_info	= false;
    
    public $joomlaUserID = 0;
    static $roundID	= 0;
    public $pggroup	= 0;
    public $pggrouprank	= 0;
    static $pjID = 0;
    public $isNewMember	= 0;
    
    public $tippEntryDone = 0;
    public $from = 0;
    public $to = 0;
    public $type = 0;
    public $page = 0;
    
	/**
	 * sportsmanagementModelPredictionEntry::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

        $prediction = new sportsmanagementModelPrediction();  
       
        $post = $jinput->post->getArray(array());
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
       
       self::$pjID = $jinput->getVar('pj','0');

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pjID<br><pre>'.print_r(self::$pjID,true).'</pre>'),'');
        
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
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pjID<br><pre>'.print_r(sportsmanagementModelPrediction::$pjID,true).'</pre>'),'');
               
       sportsmanagementModelPrediction::checkRoundID(sportsmanagementModelPrediction::$pjID,sportsmanagementModelPrediction::$roundID);
       
		parent::__construct();
	}

  

  
	/**
	 * sportsmanagementModelPredictionEntry::newMemberCheck()
	 * 
	 * @return
	 */
	function newMemberCheck()
	{
		if ( $this->isNewMember == 0 )
        {
            return false;
            }
            else
            {
                return true;
                }
	}

	/**
	 * sportsmanagementModelPredictionEntry::tippEntryDoneCheck()
	 * 
	 * @return
	 */
	function tippEntryDoneCheck()
	{
		if ( $this->tippEntryDone == 0 )
        {
            return false;
            }
            else
            {
                return true;
                }
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
		$app = JFactory::getApplication('site');
    // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
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
	public function getTable($type = 'predictionentry', $prefix = 'sportsmanagementtable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * sportsmanagementModelPredictionEntry::store()
	 * 
	 * @param mixed $data
	 * @return
	 */
	function store($data)
{
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');

        
        // get the table
        $row = self::getTable();
 
        // Bind the form fields to the hello table
        if (!$row->bind($data)) {
                $this->setError($this->_db->getErrorMsg());
                return false;
        }
 
        // Make sure the hello record is valid
        if (!$row->check()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
        }
 
        // Store the web link table to the database
        if (!$row->store()) {
                $this->setError( $row->getErrorMsg() );
                return false;
        }
 
        return true;         
}

	/**
	 * sportsmanagementModelPredictionEntry::createHelptText()
	 * 
	 * @param integer $gameMode
	 * @return
	 */
	public static function createHelptText($gameMode=0)
	{
  //$option = JFactory::getApplication()->input->getCmd('option').'_';
		$gameModeStr = ($gameMode==0) ? JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_STANDARD_MODE') : JText::_($option.'JL_PRED_ENTRY_TOTO_MODE');

		$helpText = '<hr><h3>'.JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_TITLE').'</h3>';

		$helpText .= '<ul>';
			$helpText .= '<li>';
				$helpText .= JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_01','<b>'.$gameModeStr.'</b>');
			$helpText .= '</li>';
			$helpText .= '<li>';
				$helpText .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_02');
			$helpText .= '</li>';
			$helpText .= '<li>';
				$helpText .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_03');
			$helpText .= '</li>';
			$helpText .= '<li>';
				$helpText .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_04');
			$helpText .= '</li>';
		$helpText .= '</ul>';
		$helpText .= '<hr>';

		return $helpText;
	}
    
    /**
     * sportsmanagementModelPredictionEntry::getTippCount()
     * 
     * @param mixed $predictionProjectID
     * @param mixed $matchID
     * @param integer $total
     * @return
     */
    public static function getTippCount($predictionProjectID,$matchID,$total=3)
	{
    $app = JFactory::getApplication();
    // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        // Select some fields
        $query->select('count(tipp)');
        $query->from('#__sportsmanagement_prediction_result');
		$query->where('prediction_id = ' . (int) sportsmanagementModelPrediction::$predictionGameID );
        $query->where('project_id = ' . (int) $predictionProjectID  );
        $query->where('match_id = ' . (int) $matchID  );
        
        switch ($total)
        {
            case 0:
            case 1:
            case 2:
            $query->where('tipp = ' . $total);
            break;
            case 3:
            break;
        }

		$db->setQuery( $query );
		$result = $db->loadResult();
		return $result;
	}

	
	/**
	 * sportsmanagementModelPredictionEntry::getMatchesDataForPredictionEntry()
	 * 
	 * @param mixed $predictionGameID
	 * @param mixed $predictionProjectID
	 * @param mixed $projectRoundID
	 * @param mixed $userID
	 * @param mixed $match_ids
	 * @param mixed $round_ids
	 * @param mixed $proteams_ids
	 * @return
	 */
	public static function getMatchesDataForPredictionEntry($predictionGameID,$predictionProjectID,$projectRoundID,$userID,$match_ids=NULL,$round_ids=NULL,$proteams_ids=NULL)
	{

    $app = JFactory::getApplication();
    // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
    // Create a new query object.		
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);
		
try{		
    $query->select('m.id,m.round_id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result,m.team1_result_decision,m.team2_result_decision');
    $query->select('r.id AS roundcode');
    $query->select('pr.tipp,pr.tipp_home,pr.tipp_away,pr.joker,pr.id AS prid');
    $query->from('#__sportsmanagement_match AS m');
    $query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
    $query->join('LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = m.id
                  AND pr.prediction_id = '.(int)$predictionGameID.' AND pr.user_id = '.$userID.' AND pr.project_id = '.(int)$predictionProjectID);
    $query->join('LEFT', '#__sportsmanagement_prediction_game AS pg ON pg.id = '.(int)$predictionGameID);
	$query->where('r.project_id = '.(int)$predictionProjectID);
    $query->where('r.id = '.(int)$projectRoundID);
    $query->where('m.published = 1');
    $query->where('m.match_date <> \'0000-00-00 00:00:00\'');
    $query->where('(m.cancel IS NULL OR m.cancel = 0)');   

/**
 * bestimmte spiele selektieren
 */		
	if ( $match_ids )
    {
    $query->where('m.id IN (' . implode(',', $match_ids) . ')');   
    }
/**
 * bestimmte mannschaften selektieren
 */    
    if ( $proteams_ids )
    {
    $query->where('( m.projectteam1_id IN (' . implode(',', $proteams_ids) . ')'.' OR '.'m.projectteam2_id IN (' . implode(',', $proteams_ids) . ') )' );    
    }
    
    $query->order('m.match_date ASC');
    				
		$db->setQuery($query);
		$results = $db->loadObjectList();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
JFactory::getApplication()->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'error');
}
		
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        if (!$results)
		{
		  $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_PROJECT'),'Notice');
//		  $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//          $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
		}  
        
		return $results;
	}

	/**
	 * sportsmanagementModelPredictionEntry::savePredictions()
	 * 
	 * @param bool $allowedAdmin
	 * @return
	 */
	function savePredictions($allowedAdmin=false)
	{
    $document	= JFactory::getDocument();
    $app = JFactory::getApplication();
    // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $starttime = microtime(); 

    $result	= true;

		$post = $jinput->post->getArray();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');

		//$pids = JFactory::getApplication()->input->getVar('pids',array(),'post','array');
        $pids = $jinput->getVar('pids', null, 'post', 'array');
		JArrayHelper::toInteger($pids);

		$cids = $jinput->getVar('cids',array(),'post','array');
		$prids = $jinput->getVar('prids',array(),'post','array');
		$homes = $jinput->getVar('homes',array(),'post','array');
		$aways = $jinput->getVar('aways',array(),'post','array');
		$tipps = $jinput->getVar('tipps',array(),'post','array');
		$jokers	= $jinput->getVar('jokers',array(),'post','array');
		$mID = $jinput->get('memberID',0,'int');
		$ptippmode = $jinput->getVar('ptippmode',array(),'post','array');
    $RoundID = $jinput->get('r',0,'int');
    $ProjectID = $jinput->get('pjID',0,'int');

		$predictionGameID = $jinput->get('prediction_id',0,'int');
		$joomlaUserID = $jinput->get('user_id',0,'int');
 
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pids<br><pre>'.print_r($pids,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prids<br><pre>'.print_r($prids,true).'</pre>'),''); 
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' homes<br><pre>'.print_r($homes,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' aways<br><pre>'.print_r($aways,true).'</pre>'),'');
 
    // _predictionMember
    $configavatar = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
    $predictionMemberInfo = sportsmanagementModelPrediction::getPredictionMember($configavatar);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' configavatar<br><pre>'.print_r($configavatar,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionMemberInfo<br><pre>'.print_r($predictionMemberInfo,true).'</pre>'),'');
    
		$changedResultArray	= array();

		for ($x=0; $x < count($pids); $x++)
		{
			for ($y=0; $y < count($cids[$pids[$x]]); $y++)
			{
$tippmode = $ptippmode[$pids[$x]];				
				//echo 'PredictionGameID:~'.$predictionGameID.'~ ';

				$dProjectID = $pids[$x];
				//echo 'PredictionProjectID:~'.$dProjectID.'~ ';

				$dMatchID = $cids[$pids[$x]][$y];
				//echo 'MatchID:~'.$dMatchID.'~ ';

				$dprID = $prids[$pids[$x]][$dMatchID];
				//echo 'prID:~'.$dprID.'~ ';

				$dHome = $homes[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dHome = $dHome;
				if ((!isset($homes[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dHome=='')))
                {
                    $dHome = "NULL";
                    }
                    else
                    {
                        $dHome = $dHome;
                        }
				//echo 'Home:~'.$dHome.'~ ';

				$dAway = $aways[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dAway = $dAway;
				if ((!isset($aways[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dAway=='')))
                {
                    $dAway = "NULL";
                    }
                    else
                    {
                        $dAway = $dAway;
                        }
				//echo 'Away:~'.$dAway.'~ ';

				/*
				$dJoker = (	isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]]) &&
							!empty($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? "'1'" : 'NULL';
				*/
				$dJoker = (isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? "1" : 'NULL';
				//echo 'Joker:~'.$dJoker.'~ ';

				$dTipp = $tipps[$pids[$x]][$cids[$pids[$x]][$y]]; 
                $tmp_dTipp = $dTipp;
				if ((!isset($tipps[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dTipp=='')))
                {
                    $dTipp = "NULL";
                    }
                    else
                    {
                        $dTipp = $dTipp;
                        }
				//echo 'Tipp:~'.$dTipp.'~ ';
				//echo '<br />';

				if 	(
						(
							(isset($homes[$pids[$x]][$cids[$pids[$x]][$y]])) &&
							(trim($dHome)!="NULL") &&

							(isset($aways[$pids[$x]][$cids[$pids[$x]][$y]])) &&
							(trim($dAway) != "NULL")
						) ||
						($dTipp!="NULL")
					)
				{

					//if ($dTipp=="NULL")
					//{
						if ($tmp_dHome > $tmp_dAway)
                        {
                            $dTipp = "1";
                            }
                            elseif($tmp_dHome < $tmp_dAway)
                            {
                                $dTipp = "2";
                                }
                                else
                                {
                                    $dTipp = "0";
                                    }
					//}

if ( $tippmode )
{
$dHome = NULL;
$dAway = NULL;
$dTipp = $tipps[$pids[$x]][$cids[$pids[$x]][$y]]; 
}
					
					
					if (!empty($dprID))
					{
					   // Create and populate an object.
                        
                        $temp = new stdClass();
                        $temp->id = (int) $dprID ;
                        $temp->tipp = $dTipp;
                        $temp->tipp_home = $dHome;
                        $temp->tipp_away = $dAway;
                        $temp->joker = $dJoker;
$temp->modified_by = $joomlaUserID;
                        $temp->modified = JFactory::getDate()->toSql();						
                        // Update the object
                        try{
                        $resultquery = JFactory::getDbo()->updateObject('#__sportsmanagement_prediction_result', $temp, 'id',true);
                        }
catch (Exception $e) {
//    // catch any database errors.
//    $db->transactionRollback();
//    JErrorPage::render($e);
}
//                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' update<br><pre>'.print_r($temp,true).'</pre>'),'');
                        
//                        $query	= $db->getQuery(true);
//                        $query->clear();
//		                $query->update('#__sportsmanagement_prediction_result');
//		                $query->set(' tipp_home = '.$dHome );
//                        $query->set(' tipp_away = '.$dAway );
//                        $query->set(' tipp = '.$dTipp );
//                        $query->set(' joker = '.$dJoker );
//		                $query->where(' id = ' . (int) $dprID );
//                		$db->setQuery($query);
                        
                        
                        if( !$resultquery )
                        //if( !$db->query() )
					{

                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
						$result = false;

					}
                                            
//                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' update<br><pre>'.print_r($object,true).'</pre>'),'');

					}
					else
					{
						// Create and populate an object.
                        $temp = new stdClass();
                        $temp->prediction_id = $predictionGameID;
                        $temp->user_id = $joomlaUserID;
                        $temp->project_id = $dProjectID;
                        $temp->match_id = $dMatchID;
                        $temp->tipp = $dTipp;
                        $temp->tipp_home = $dHome;
                        $temp->tipp_away = $dAway;
                        $temp->joker = $dJoker;
$temp->modified_by = $joomlaUserID;
                        $temp->modified = JFactory::getDate()->toSql();						
                        // Insert the object
try{
                        $resultquery = JFactory::getDbo()->insertObject('#__sportsmanagement_prediction_result', $temp);
                        }
catch (Exception $e) {
//    // catch any database errors.
//    $db->transactionRollback();
//    JErrorPage::render($e);
}
//                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' insert<br><pre>'.print_r($temp,true).'</pre>'),'');

					if ( !$resultquery )
					{
                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
						$result = false;
					}
                    
                    
                    }

					

				}
				else
				{
                    
                    /* Der Query wird erstellt */
                    $query->clear();
                    $query->from('#__sportsmanagement_prediction_result');
                    $query->delete();
                    $query->where('prediction_id = ' . $predictionGameID);
                    $query->where('user_id = ' . $joomlaUserID);
                    $query->where('project_id = ' . $pids[$x]);
                    $query->where('match_id = ' . $cids[$pids[$x]][$y]);

					$db->setQuery( $query );
                    
                    if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
					if( !$db->query() )
					{

                        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
						$result = false;

					}

				}
			}
		}
        
        // Must be a valid primary key value.
        $object = new stdClass();
        $object->id = $mID;
        $object->last_tipp = date('Y-m-d H:i:s');
        // Update their details in the table using id as the primary key.
        $resultquery = JFactory::getDbo()->updateObject('#__sportsmanagement_prediction_member', $object, 'id');

		if (!$resultquery)
		{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;

		}

/**
 * email mit tippergebnissen senden
 */
    if ( $predictionMemberInfo->receipt )
    {
    sportsmanagementModelPrediction::sendMemberTipResults($mID,$predictionGameID,$RoundID,$ProjectID,$joomlaUserID);
    }
    
		return $result;
	}

}
?>
