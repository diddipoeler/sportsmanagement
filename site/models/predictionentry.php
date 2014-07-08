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
class sportsmanagementModelPredictionEntry extends JModelLegacy
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
    
	/**
	 * sportsmanagementModelPredictionEntry::__construct()
	 * 
	 * @return void
	 */
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

        $prediction = new sportsmanagementModelPrediction();  

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
       
		parent::__construct();
	}

  
  
	/**
	 * sportsmanagementModelPredictionEntry::newMemberCheck()
	 * 
	 * @return
	 */
	function newMemberCheck()
	{
		if ($this->isNewMember==0)
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
		if ($this->tippEntryDone==0)
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
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');

        
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
    
    /**
     * sportsmanagementModelPredictionEntry::getTippCount()
     * 
     * @param mixed $predictionProjectID
     * @param mixed $matchID
     * @param integer $total
     * @return
     */
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

	/**
	 * sportsmanagementModelPredictionEntry::getMatchesDataForPredictionEntry()
	 * 
	 * @param mixed $predictionGameID
	 * @param mixed $predictionProjectID
	 * @param mixed $projectRoundID
	 * @param mixed $userID
	 * @param mixed $match_ids
	 * @return
	 */
	function getMatchesDataForPredictionEntry($predictionGameID,$predictionProjectID,$projectRoundID,$userID,$match_ids=NULL,$round_ids=NULL)
	{
		    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $query->select('m.id,m.round_id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result,m.team1_result_decision,m.team2_result_decision');
        $query->select('r.id AS roundcode');
        $query->select('pr.tipp,pr.tipp_home,pr.tipp_away,pr.joker,pr.id AS prid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id');
        
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result AS pr ON pr.match_id = m.id
                     AND pr.prediction_id = '.$predictionGameID.' AND pr.user_id = '.$userID.' AND pr.project_id = '.$predictionProjectID);
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game AS pg ON pg.id = '.$predictionGameID);

		$query->where('r.project_id = '.$predictionProjectID);
        $query->where('r.id = '.$projectRoundID);
       
        $query->where('m.published = 1');
        $query->where('m.match_date <> \'0000-00-00 00:00:00\'');
        $query->where('(m.cancel IS NULL OR m.cancel = 0)');   
        				
		if ( $match_ids )
    {
    $query->where('m.id IN (' . implode(',', $match_ids) . ')');   
    }

//    if ( $round_ids )
//    {
//    $query->where('r.id IN (' . implode(',', $round_ids) . ')');   
//    }
//    else
//    {
//        $query->where('r.id = '.$projectRoundID);
//    }
    
    $query->order('m.match_date ASC');
    				
		$db->setQuery($query);
		$results = $db->loadObjectList();
        
        if (!$results)
		{
		  $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
          $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
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
    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $starttime = microtime(); 

    $result	= true;

		$post	= JRequest::get('post');

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');

		$pids = JRequest::getVar('pids',array(),'post','array');
		JArrayHelper::toInteger($pids);

		$cids = JRequest::getVar('cids',array(),'post','array');
		$prids = JRequest::getVar('prids',array(),'post','array');
		$homes = JRequest::getVar('homes',array(),'post','array');
		$aways = JRequest::getVar('aways',array(),'post','array');
		$tipps = JRequest::getVar('tipps',array(),'post','array');
		$jokers	= JRequest::getVar('jokers',array(),'post','array');
		$mID = JRequest::getVar('memberID',0,'post','int');
		
    $RoundID = JRequest::getVar('r',0,'post','int');
    $ProjectID = JRequest::getVar('pjID',0,'post','int');

		$predictionGameID = JRequest::getVar('prediction_id','','post','int');
		$joomlaUserID = JRequest::getVar('user_id','','post','int');
 
    // _predictionMember
    $configavatar = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
    $predictionMemberInfo = sportsmanagementModelPrediction::getPredictionMember($configavatar);
    
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
				if ((!isset($homes[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dHome==''))){$dHome = "NULL";}else{$dHome = $dHome;}
				//echo 'Home:~'.$dHome.'~ ';

				$dAway = $aways[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dAway = $dAway;
				if ((!isset($aways[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dAway==''))){$dAway = "NULL";}else{$dAway = $dAway;}
				//echo 'Away:~'.$dAway.'~ ';

				/*
				$dJoker = (	isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]]) &&
							!empty($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? "'1'" : 'NULL';
				*/
				$dJoker = (isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? "1" : 'NULL';
				//echo 'Joker:~'.$dJoker.'~ ';

				$dTipp = $tipps[$pids[$x]][$cids[$pids[$x]][$y]]; $tmp_dTipp = $dTipp;
				if ((!isset($tipps[$pids[$x]][$cids[$pids[$x]][$y]]))||(trim($dTipp==''))){$dTipp = "NULL";}else{$dTipp = $dTipp;}
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
						if ($tmp_dHome > $tmp_dAway){$dTipp = "1";}elseif($tmp_dHome < $tmp_dAway){$dTipp = "2";}else{$dTipp = "0";}
					}

					if (!empty($dprID))
					{
                        $query	= $db->getQuery(true);
                        $query->clear();
		                $query->update('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result');
		                $query->set(' tipp_home = '.$dHome );
                        $query->set(' tipp_away = '.$dAway );
                        $query->set(' tipp = '.$dTipp );
                        $query->set(' joker = '.$dJoker );
		                $query->where(' id = ' . (int) $dprID );
                		$db->setQuery((string)$query);
                        //$db->query();
                        
                        if( !$db->query() )
					{

                        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
						$result = false;

					}
                                            
//                        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//                        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' update<br><pre>'.print_r($object,true).'</pre>'),'');

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
                        // Insert the object
                        $resultquery = JFactory::getDbo()->insertObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result', $temp);
                        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' insert<br><pre>'.print_r($temp,true).'</pre>'),'');

					if ( !$resultquery )
					{
                        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
						$result = false;
					}
                    
                    
                    }

					

				}
				else
				{
                    
                    /* Der Query wird erstellt */
                    $query->clear();
                    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result');
                    $query->delete();
                    $query->where('prediction_id = ' . $predictionGameID);
                    $query->where('user_id = ' . $joomlaUserID);
                    $query->where('project_id = ' . $pids[$x]);
                    $query->where('match_id = ' . $cids[$pids[$x]][$y]);

					$db->setQuery( $query );
                    
                    if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
					if( !$db->query() )
					{

                        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
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
        $resultquery = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_member', $object, 'id');

		if (!$resultquery)
		{
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;

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