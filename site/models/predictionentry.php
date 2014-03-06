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

jimport('joomla.application.component.model');

require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'prediction.php' );

/**
 * sportsmanagementModelPredictionEntry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionEntry extends JModel
{

public $_predictionGame		= null;
	public $predictionGameID		= 0;

	public $_predictionMember		= null;
	public $predictionMemberID		= 0;

	public $_predictionProjectS	= null;
	public $predictionProjectSIDs	= null;

	public $_predictionProject		= null;
	public $predictionProjectID	= null;
	public $show_debug_info	= false;
    
    public $joomlaUserID		= 0;
    public $roundID		= 0;
    public $pggroup		= 0;
    public $pggrouprank		= 0;
    public $pjID		= 0;
    public $isNewMember		= 0;
    
    public $tippEntryDone		= 0;
    public $from		= 0;
    public $to		= 0;
    public $type		= 0;
    public $page		= 0;
    
	function __construct()
	{
	   $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    
    $this->predictionGameID		= JRequest::getInt('prediction_id',		0);
		$this->predictionMemberID	= JRequest::getInt('uid',	0);
		$this->joomlaUserID			= JRequest::getInt('juid',	0);
		$this->roundID				= JRequest::getInt('r',		0);
        $this->pggroup				= JRequest::getInt('pggroup',		0);
        $this->pggrouprank			= JRequest::getInt('pggrouprank',		0);
		$this->pjID					= JRequest::getInt('p',		0);
		$this->isNewMember			= JRequest::getInt('s',		0);
		$this->tippEntryDone		= JRequest::getInt('eok',	0);

		$this->from  				= JRequest::getInt('from',	$this->roundID);
		$this->to	 				= JRequest::getInt('to',	$this->roundID);
		$this->type  				= JRequest::getInt('type',	0);

		$this->page  				= JRequest::getInt('page',	1);
        
        //$prediction = JModel::getInstance("Prediction","sportsmanagementModel");
        $prediction = new sportsmanagementModelPrediction();  
        //$prediction->predictionGameID = $this->predictionGameID	;
        sportsmanagementModelPrediction::$predictionGameID = $this->predictionGameID;
        
        sportsmanagementModelPrediction::$predictionMemberID = $this->predictionMemberID;
        sportsmanagementModelPrediction::$joomlaUserID = $this->joomlaUserID;
        sportsmanagementModelPrediction::$roundID = $this->roundID;
        sportsmanagementModelPrediction::$pggroup = $this->pggroup;
        sportsmanagementModelPrediction::$pggrouprank = $this->pggrouprank;
        sportsmanagementModelPrediction::$pjID = $this->pjID;
        sportsmanagementModelPrediction::$isNewMember = $this->isNewMember;
        sportsmanagementModelPrediction::$tippEntryDone = $this->tippEntryDone;
        sportsmanagementModelPrediction::$from = $this->from;
        sportsmanagementModelPrediction::$to = $this->to;
        sportsmanagementModelPrediction::$type = $this->type;
        sportsmanagementModelPrediction::$page = $this->page;
        
	   //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionGameID<br><pre>'.print_r($this->predictionGameID,true).'</pre>'),'');
       
		parent::__construct();
	}

  
  
	function newMemberCheck()
	{
		if ($this->isNewMember==0){return false;}else{return true;}
	}

	function tippEntryDoneCheck()
	{
		if ($this->tippEntryDone==0){return false;}else{return true;}
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
	
	function store($data)
{
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');

        
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

	function createHelptText($gameMode=0)
	{
  //$option = JRequest::getCmd('option').'_';
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
    
    function getTippCount($predictionProjectID,$matchID,$total=3)
	{
	   $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        // Select some fields
        $query->select('count(tipp)');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result');
		$query->where('prediction_id = ' . intval( $this->predictionGameID ));
        $query->where('project_id = ' . intval( $predictionProjectID ));
        $query->where('match_id = ' . intval( $matchID ));
        
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

//	function getTippCountHome($predictionProjectID,$matchID)
//	{
//	   $option = JRequest::getCmd('option');    
//    $mainframe = JFactory::getApplication();
//    // Create a new query object.		
//		$db = JFactory::getDBO();
//		$query = $db->getQuery(true);
//        // Select some fields
//        $query->select('count(tipp)');
//        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result');
//		$query->where('prediction_id = ' . intval( $this->predictionGameID ));
//        $query->where('project_id = ' . intval( $predictionProjectID ));
//        $query->where('match_id = ' . intval( $matchID ));
//        $query->where('tipp = 1');
//        
//        
////        $query = 'SELECT count(tipp) FROM #__sportsmanagement_prediction_result
////					WHERE	prediction_id = ' . intval( $this->predictionGameID ) . ' AND
////							project_id = ' . intval( $predictionProjectID ) . ' AND
////							match_id = ' . intval( $matchID ) . ' AND
////							tipp = 1';
//		$db->setQuery( $query );
//		$result = $db->loadResult();
//		return $result;
//	}

//	function getTippCountDraw($predictionProjectID,$matchID)
//	{
//	   $option = JRequest::getCmd('option');    
//    $mainframe = JFactory::getApplication();
//    // Create a new query object.		
//		$db = JFactory::getDBO();
//		$query = $db->getQuery(true);
//        
//		$query = 'SELECT count(tipp) FROM #__sportsmanagement_prediction_result
//					WHERE	prediction_id = ' . intval( $this->predictionGameID ) . ' AND
//							project_id = ' . intval( $predictionProjectID ) . ' AND
//							match_id = ' . intval( $matchID ) . ' AND
//							tipp = 0';
//		$this->_db->setQuery( $query );
//		$result = $this->_db->loadResult();
//		return $result;
//	}

//	function getTippCountAway($predictionProjectID,$matchID)
//	{
//	   $option = JRequest::getCmd('option');    
//    $mainframe = JFactory::getApplication();
//    // Create a new query object.		
//		$db = JFactory::getDBO();
//		$query = $db->getQuery(true);
//        
//		$query = 'SELECT count(tipp) FROM #__sportsmanagement_prediction_result
//					WHERE	prediction_id = ' . intval( $this->predictionGameID ) . ' AND
//							project_id = ' . intval( $predictionProjectID ) . ' AND
//							match_id = ' . intval( $matchID ) . ' AND
//							tipp = 2';
//		$this->_db->setQuery( $query );
//		$result = $this->_db->loadResult();
//		return $result;
//	}

//	function getTippCountTotal($predictionProjectID,$matchID)
//	{
//	   $option = JRequest::getCmd('option');    
//    $mainframe = JFactory::getApplication();
//    // Create a new query object.		
//		$db = JFactory::getDBO();
//		$query = $db->getQuery(true);
//        
//		$query = 'SELECT count(tipp) FROM #__sportsmanagement_prediction_result
//					WHERE	prediction_id = ' . intval( $this->predictionGameID ) . ' AND
//							project_id = ' . intval( $predictionProjectID ) . ' AND
//							match_id = ' . intval( $matchID );
//		$this->_db->setQuery( $query );
//		$result = $this->_db->loadResult();
//		return $result;
//	}

	function getMatchesDataForPredictionEntry($predictionGameID,$predictionProjectID,$projectRoundID,$userID,$match_ids)
	{
		    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
    $query = 	"	SELECT	m.id,
								m.round_id,
								m.match_date,
								m.projectteam1_id,
								m.projectteam2_id,
								m.team1_result,
								m.team2_result,
								m.team1_result_decision,
								m.team2_result_decision,
								r.id AS roundcode,
								pr.tipp,
								pr.tipp_home,
								pr.tipp_away,
								pr.joker,
								pr.id AS prid

						FROM #__sportsmanagement_match AS m
						INNER JOIN #__sportsmanagement_round AS r ON	r.id=m.round_id AND
																r.project_id=$predictionProjectID AND
																r.id=$projectRoundID

						LEFT JOIN #__sportsmanagement_prediction_game AS pg ON pg.id=$predictionGameID

						LEFT JOIN #__sportsmanagement_prediction_result AS pr ON	pr.prediction_id=$predictionGameID AND
																			pr.user_id=$userID AND
																			pr.project_id=$predictionProjectID AND
																			pr.match_id=m.id
						WHERE	m.published=1 AND
								m.match_date <> '0000-00-00 00:00:00'
						AND (m.cancel IS NULL OR m.cancel = 0)";
						
		if ( $match_ids )
    {
    $convert = array (
      '|' => ','
        );
    $match_ids = str_replace(array_keys($convert), array_values($convert), $match_ids );
    $query .= "AND m.id IN (" . $match_ids . ")";    
    }
    
    $query .= "ORDER BY m.match_date ASC";
    				
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		return $results;
	}

	function savePredictions($allowedAdmin=false)
	{
//		global $mainframe, $option;
    $document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

    $result	= true;
		
	//	$show_debug = $this->getDebugInfo();
		

		$post	= JRequest::get('post');
		
	//	if ( $show_debug )
//		{
//    echo '<br />savePredictions post<pre>~' . print_r($post,true) . '~</pre><br />';
//    }
    
    //$mainframe->enqueueMessage(JText::_('post -> <pre> '.print_r($post,true).'</pre><br>' ),'Notice');

		$pids	= JRequest::getVar('pids',array(),'post','array');
		JArrayHelper::toInteger($pids);

		$cids	= JRequest::getVar('cids',	array(),'post','array');
		$prids	= JRequest::getVar('prids',	array(),'post','array');
		$homes	= JRequest::getVar('homes',	array(),'post','array');
		$aways	= JRequest::getVar('aways',	array(),'post','array');
		$tipps	= JRequest::getVar('tipps',	array(),'post','array');
		$jokers	= JRequest::getVar('jokers',array(),'post','array');
		$mID	= JRequest::getVar('memberID',0,'post','int');
		
    $RoundID	= JRequest::getVar('r',0,'post','int');
    $ProjectID	= JRequest::getVar('pjID',0,'post','int');
		
		
		//echo '<br /><pre>~' . print_r($jokers,true) . '~</pre><br />';

		$predictionGameID	= JRequest::getVar('prediction_id',	'','post','int');
		$joomlaUserID		= JRequest::getVar('user_id',		'','post','int');

    //$mainframe->enqueueMessage(JText::_('predictionGameID -> '.$predictionGameID),'');
    //$mainframe->enqueueMessage(JText::_('joomlaUserID -> '.$joomlaUserID),'');
    //$mainframe->enqueueMessage(JText::_('predictionMemberID -> '.$mID),'');
    
    
    // _predictionMember
    $configavatar			= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
    $predictionMemberInfo = sportsmanagementModelPrediction::getPredictionMember($configavatar);
    
    //$mainframe->enqueueMessage(JText::_('predictionMemberInfo -> <pre> '.print_r($predictionMemberInfo,true).'</pre><br>' ),'Notice');
    
    //$mainframe->enqueueMessage(JText::_('predictionMember reminder -> '.$predictionMemberInfo->reminder),'');
    //$mainframe->enqueueMessage(JText::_('predictionMember email -> '.$predictionMemberInfo->email),'');
    
		$changedResultArray	= array();

		for ($x=0; $x < count($pids); $x++)
		{
			for ($y=0; $y < count($cids[$pids[$x]]); $y++)
			{
				//echo 'PredictionGameID:~'.$predictionGameID.'~ ';

				$dProjectID = $pids[$x];
				//echo 'PredictionProjectID:~'.$dProjectID.'~ ';

				$dMatchID = $cids[$pids[$x]][$y];
				//echo 'MatchID:~'.$dMatchID.'~ ';

				$dprID = $prids[$pids[$x]][$dMatchID];
				//echo 'prID:~'.$dprID.'~ ';

				$dHome = $homes[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dHome = $dHome;
				if ((!isset($homes[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dHome==''))){$dHome = "NULL";}else{$dHome = "'".$dHome."'";}
				//echo 'Home:~'.$dHome.'~ ';

				$dAway = $aways[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dAway = $dAway;
				if ((!isset($aways[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dAway==''))){$dAway = "NULL";}else{$dAway = "'".$dAway."'";}
				//echo 'Away:~'.$dAway.'~ ';

				/*
				$dJoker = (	isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]]) &&
							!empty($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? "'1'" : 'NULL';
				*/
				$dJoker = (isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? "'1'" : 'NULL';
				//echo 'Joker:~'.$dJoker.'~ ';

				$dTipp = $tipps[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dTipp = $dTipp;
				if ((!isset($tipps[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dTipp==''))){$dTipp = "NULL";}else{$dTipp = "'".$dTipp."'";}
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

					if ($dTipp=="NULL")
					{
						if ($tmp_dHome > $tmp_dAway){$dTipp = "'1'";}elseif($tmp_dHome < $tmp_dAway){$dTipp = "'2'";}else{$dTipp = "'0'";}
					}

					if (!empty($dprID))
					{
						$query =	"	UPDATE #__sportsmanagement_prediction_result
										SET
											tipp=$dTipp,
											tipp_home=$dHome,
											tipp_away=$dAway,
											joker=$dJoker
										WHERE id='$dprID'
									";
					}
					else
					{
						$query = "INSERT IGNORE INTO #__sportsmanagement_prediction_result
									(
										prediction_id,
										user_id,
										project_id,
										match_id,
										tipp,
										tipp_home,
										tipp_away,
										joker
									)
									VALUES
									(
										'$predictionGameID',
										'$joomlaUserID',
										'$dProjectID',
										'$dMatchID',
										$dTipp,
										$dHome,
										$dAway,
										$dJoker
									)";
					}
					//echo $query . '<br />';
					/**/
					$this->_db->setQuery( $query );
					if ( !$this->_db->query() )
					{
						$this->setError( $this->_db->getErrorMsg() );
						$result= false;
						//echo '<br />ERROR~' . $query . '~<br />';
					}
					/**/
				}
				else
				{
					$query = 'DELETE FROM #__sportsmanagement_prediction_result WHERE prediction_id=' . $predictionGameID;
					$query .= ' AND user_id=' . $joomlaUserID;
					$query .= ' AND project_id=' . $pids[$x];
					$query .= ' AND match_id=' . $cids[$pids[$x]][$y];
					//echo '<br />~' . $query . '~<br />';
					/**/
					$this->_db->setQuery( $query );
					if( !$this->_db->query() )
					{
						$this->setError( $this->_db->getErrorMsg() );
						$result = false;
						//echo '<br />ERROR~' . $query . '~<br />';
					}
					/**/
				}
			}
		}

		$query = "UPDATE #__sportsmanagement_prediction_member SET last_tipp='" . date('Y-m-d H:i:s') . "' WHERE id=$mID";
		//echo $query . '<br />';
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			$result = false;
			//echo '<br />ERROR~' . $query . '~<br />';
		}

    // email mit tippergebnissen senden
    if ( $predictionMemberInfo->reminder )
    {
    sportsmanagementModelPrediction::sendMemberTipResults($mID,$predictionGameID,$RoundID,$ProjectID,$joomlaUserID);
    }
    
		return $result;
	}

}
?>