<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      predictionmember.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionmember
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'models' . DS . 'prediction.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'models' . DS . 'predictionentry.php');

/**
 * sportsmanagementModelpredictionmember
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelpredictionmember extends JSMModelAdmin
{
	
    /**
     * sportsmanagementModelpredictionmember::save_memberlist()
     * 
     * @return void
     */
    function save_memberlist()
	{
	// Reference global application object
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
	
  $post	= JFactory::getApplication()->input->post->getArray(array());
	$cid	= JFactory::getApplication()->input->getVar('cid', array(0), 'post', 'array');
  $prediction_id = (int) $cid[0];
  //echo '<br />save_memberlist post<pre>~' . print_r($post,true) . '~</pre><br />';
  
  //$app->enqueueMessage(JText::_('<br />save_memberlist post<pre>~' . print_r($post,true) . '~</pre><br />'),'Notice');
  //$app->enqueueMessage(JText::_('<br />prediction id<pre>~' . print_r($prediction_id,true) . '~</pre><br />'),'Notice');
  
  
  foreach ( $post['prediction_members'] as $key => $value )
  {
   $query->clear();
  $query->select('pm.id'); 
  $query->from('#__sportsmanagement_prediction_member AS pm '); 
  $query->where('pm.prediction_id = ' . $prediction_id); 
  $query->where('pm.user_id = ' . $value); 
  $db->setQuery($query); 
  $result = $db->loadResult(); 

if ( !$result )
{
	
  //$app->enqueueMessage(JText::_('<br />memberlist id<pre>~' . print_r($value,true) . '~</pre><br />'),'Notice');
  //$table = 'predictionmember';
  $table = 'predictionentry';
  $rowproject = JTable::getInstance( $table, 'sportsmanagementTable' );
  //$rowproject->load( $value );
  $rowproject->prediction_id = $prediction_id;
  $rowproject->user_id = $value;
  $rowproject->registerDate = JHtml::date(time(),'%Y-%m-%d %H:%M:%S');
  $rowproject->approved = 1;
  $rowproject->modified = $date->toSql();
  $rowproject->modified_by = $user->get('id');
  if ( !$rowproject->store() )
  {
  //echo 'project -> '.$value. ' nicht gesichert <br>';
  }
  else
  {
  //echo 'project -> '.$value. ' gesichert <br>';
  }
   
}

  }
  
  }
 
   
  /**
   * sportsmanagementModelpredictionmember::sendEmailtoMembers()
   * 
   * @param mixed $cid
   * @param mixed $prediction_id
   * @return void
   */
  function sendEmailtoMembers($cid,$prediction_id)
  {
        $config = JFactory::getConfig();
  
$language = JFactory::getLanguage();
$language->load($this->jsmoption, JPATH_SITE, $language->getTag(), true);

sportsmanagementModelPrediction::$predictionGameID = $prediction_id;
$configprediction = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
$overallConfig = sportsmanagementModelPrediction::getPredictionOverallConfig();
$configprediction = array_merge($overallConfig,$configprediction);
 
  $pred_reminder_mail_text = JComponentHelper::getParams($this->jsmoption)->get('pred_reminder_mail_text',0);

/**
 * add the sender Information.
 */
if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$sender = array( 
    $config->get( 'config.mailfrom' ),
    $config->get( 'config.fromname' ) );
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$sender = array( 
    $config->getValue( 'config.mailfrom' ),
    $config->getValue( 'config.fromname' ) );
}

$mdlPredictionGame = JModelLegacy::getInstance('PredictionGame', 'sportsmanagementModel');
$mdlPredictionGames = JModelLegacy::getInstance('PredictionGames', 'sportsmanagementModel');

$predictiongame = $mdlPredictionGame->getPredictionGame($prediction_id);
$predictionproject = $mdlPredictionGame->getPredictionProjectIDs($prediction_id);

/**
 * schleife über die tippspieler anfang
 */
  foreach ( $cid as $key => $value )
    {
$mailer = JFactory::getMailer();
/**
 * als html
 */
$mailer->isHTML(TRUE);
$mailer->setSender($sender); 
          
$body = '';
/**
  * jetzt die ergebnisse
  */  
$body .= "<html>";         
    $member_email = $this->getPredictionMemberEMailAdress( $value );
    $fromdate = ''; 
    $predictionlink = '';
    $projectcount = 0;
    foreach ( $predictionproject as $project_key => $project_value )
    {
    $predictiongamematches = $mdlPredictionGames->getPredictionGamesMatches($prediction_id,$project_value,$member_email->user_id);
   
    $body .= "<table class='table' width='100%' cellpadding='0' cellspacing='0'>";
	$body .= "<tr>";
	$body .= "<th class='sectiontableheader' style='text-align:center;'>" . JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DATE_TIME') . "</th>";
	$body .= "<th class='sectiontableheader' style='text-align:center;' colspan='5' >" . JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MATCH') . "</th>";
	$body .= "<th class='sectiontableheader' style='text-align:center;'>" . JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_RESULT') . "</th>";
	$body .= "<th class='sectiontableheader' style='text-align:center;'>" . JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_YOURS') . "</th>";
	$body .= "<th class='sectiontableheader' style='text-align:center;'>" . JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_POINTS') . "</th>";
	$body .= "</tr>";
/**
 * schleife über die ergebnisse in der runde
 */	
	foreach ($predictiongamematches AS $result)
	{
	$class = ($k==0) ? 'sectiontableentry1' : 'sectiontableentry2';

	$resultHome = (isset($result->team1_result)) ? $result->team1_result : '-';
	if (isset($result->team1_result_decision))
    {
        $resultHome = $result->team1_result_decision;
        }
	$resultAway = (isset($result->team2_result)) ? $result->team2_result : '-';
	if (isset($result->team2_result_decision))
    {
        $resultAway = $result->team2_result_decision;
        }
  $closingtime = $configprediction['closing_time'] ;//3600=1 hour
	$matchTimeDate = sportsmanagementHelper::getTimestamp($result->match_date,1,$predictionProjectSettings->timezone);
	$thisTimeDate = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"),1,$predictionProjectSettings->timezone);
	$matchTimeDate = $matchTimeDate - $closingtime;
    $body .= "<tr class='" . $class ."'>";
	$body .= "<td class='td_c'>";
	$body .= JHtml::date($result->match_date, 'd.m.Y H:i', false);
	$body .= " - ";
	//$body .= JHTML::date(date("Y-m-d H:i:s",$matchTimeDate),$configprediction['time_format']); 
	$body .= "</td>";
/**
 * clublogo oder vereinsflagge hometeam	
 */
$body .= "<td nowrap='nowrap' class='td_r'>";
$body .= $result->home_name;
$body .= "</td>";
$body .= "<td nowrap='nowrap' class='td_c'>";
$imgTitle = JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $result->home_name);
$body .=  JSMCountries::getCountryFlag($result->home_country);
if	(($result->home_logo_big == '') || (!file_exists($result->home_logo_big)))
{
$result->home_logo_big = 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
}
$body .=  JHTML::image(JURI::root().$result->home_logo_big,$imgTitle,array(' title' => $imgTitle,' width' => 30));
$body .=  ' ' ;   
$body .= "</td>";	
$body .= "<td nowrap='nowrap' class='td_c'>";	
$body .= "<b>" . "-" . "</b>";
$body .= "</td>";    
/**
 * clublogo oder vereinsflagge awayteam
 */
$body .= "<td nowrap='nowrap' class='td_c'>";
$imgTitle = JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $result->away_name);
$body .=  ' ';
$body .=  JSMCountries::getCountryFlag($result->away_country);
if	(($result->away_logo_big == '') || (!file_exists($result->away_logo_big)))
{
$result->away_logo_big = 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
}
$body .=  JHTML::image(JURI::root().$result->away_logo_big,$imgTitle,array(' title' => $imgTitle,' width' => 30));
$body .= "</td>";				
$body .= "<td nowrap='nowrap' class='td_l'>";
$body .= $result->away_name;
$body .= "</td>";	
/**
 * spielergebnisse
 */
$body .= "<td class='td_c'>";
$body .= $result->tipp_home . '-' . $result->tipp_away;
$body .= "</td>";
/**
 * tippergebnisse
 */
$body .= "<td class='td_c'>";
/**
 * Tipp in normal mode
 */
if ( $predictionProject->mode == '0' )	
{
$body .= $result->tipp_home . $configprediction['seperator'] . $result->tipp_away;
}
/**
 * Tipp in toto mode
 */
if ( $predictionProject->mode == '1' )	
{
$body .= $result->tipp;
}
$body .= "</td>";

/**
 * punkte
 */
$body .= "<td class='td_c'>";
$points = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
$totalPoints = $totalPoints+$points;
$body .=  $points;
$body .= "</td>";
$body .= "</tr>";
/**
 * tendencen im tippspiel  
 */
if ( $configprediction['show_tipp_tendence'] )
{

$body .= "<tr class='tipp_tendence'>";
$body .= "<td class='td_c'>";
$body .= "&nbsp;"; 
$body .= "</td>";

$body .= "<td class='td_l' colspan='8'>";

$totalCount = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 3);
$homeCount = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 1);
$awayCount = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 2);
$drawCount = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 0);

if ($totalCount > 0)
{
$percentageH = round(( $homeCount * 100 / $totalCount ),2);
$percentageD = round(( $drawCount * 100 / $totalCount ),2);
$percentageA = round(( $awayCount * 100 / $totalCount ),2);
}
else
{
$percentageH = 0;
$percentageD = 0;
$percentageA = 0;
}

$body .= "<span style='color:" . $configprediction['color_home_win'] ."' >";
$body .= JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN',$percentageH,$homeCount) . "</span><br />";
$body .= "<span style='color:" . $configprediction['color_draw'] ."'>";
$body .= JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW',$percentageD,$drawCount) . "</span><br />";
$body .= "<span style='color:" . $configprediction['color_guest_win'] ."'>";
$body .= JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN',$percentageA,$awayCount) . "</span>";
$body .= "</td>";
//$body .= "<td colspan='8'>&nbsp;</td>";
$body .= "</tr>";
}
else
{
$k = (1-$k);							
}
       
       
    }  

if ( $projectcount )
   {
   $fromdate .= ' und '; 
   }    
$fromdate .= JHtml::date($result->round_date_first, 'd.m.Y', false).'-'.JHtml::date($result->round_date_last, 'd.m.Y', false);
   
$body .= "<tr>";
$body .= "<td colspan='8'>&nbsp;</td>";
$body .= "<td class='td_c'>" . JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTAL_POINTS_COUNT',$totalPoints) ."</td>";
$body .= "</tr>";            	
$body .= "<table>";   
 
 $projectcount++;
    }
$body .= "</html><br>";
   
/**
 * add the recipient. $recipient = $user_email;
 */
    $mailer->addRecipient($member_email->email); 
/**
 * add the subject
 */
     $subject = addslashes(
				sprintf(
				JText::_( "COM_SPORTSMANAGEMENT_EMAIL_PREDICTION_REMINDER_TIPS_RESULTS" ),
				$predictiongame->name ) );
    $mailer->setSubject($subject);
/**
 * add body
 */
$message = $pred_reminder_mail_text;

$message = str_replace('[PREDICTIONMEMBER]', $member_email->username, $message);
$message = str_replace('[PREDICTIONRESULTS]', $body, $message);
$message = str_replace('[FROMDATE]', $fromdate, $message);


$message = str_replace('[PREDICTIONENTRIES]', $predictionlink, $message);

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$message = str_replace('[PREDICTIONADMIN]', $config->get( 'sitename' ), $message);
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$message = str_replace('[PREDICTIONADMIN]', $config->getValue( 'sitename' ), $message);
}

$mailer->setBody($message);
$send = $mailer->Send();

if ( $send !== true ) {
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($send->message,true).'</pre>'),'Error');
} else {
    $this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_SEND_OK', $member_email->email),'notice');
}

    }
/**
 * schleife über die tippspieler ende
 */
   
  }



	/**
	 * sportsmanagementModelpredictionmember::getSystemAdminsEMailAdresses()
	 * 
	 * @return
	 */
	function getSystemAdminsEMailAdresses()
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
		$query =	'	SELECT u.email
						FROM #__users AS u
						WHERE	u.sendEmail = 1 AND
								u.block = 0 AND
								u.usertype = "Super Administrator"
						ORDER BY u.email';
//echo $query . '<br />';
		$this->_db->setQuery( $query );
    if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}
		return $records;
	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionGameAdminsEMailAdresses()
	 * 
	 * @param mixed $predictionGameID
	 * @return
	 */
	function getPredictionGameAdminsEMailAdresses( $predictionGameID )
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
        
		$query =	'	SELECT u.email
						FROM #__users AS u
						INNER JOIN #__sportsmanagement_prediction_admin AS pa ON	pa.prediction_id = ' . (int) $predictionGameID . ' AND
																			pa.user_id = u.id
						WHERE	u.sendEmail = 1 AND
								u.block = 0
						ORDER BY u.email';
//echo $query . '<br />';
		$this->_db->setQuery( $query );
    
     if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}

		return $records;
	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionMembersEMailAdresses()
	 * 
	 * @param mixed $cids
	 * @return
	 */
	function getPredictionMembersEMailAdresses( $cids )
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
        
		//echo '<br /><pre>~' . print_r( $cids, true ) . '~</pre><br />';
		$query =	'	SELECT user_id
						FROM #__sportsmanagement_prediction_member
						WHERE	id IN (' . $cids . ')';
		//echo $query . '<br />';
		$db->setQuery( $query );
		if ( !$cids = $this->_db->loadResultArray() ) { return false; }
		//echo '<br /><pre>~' . print_r( $cids, true ) . '~</pre><br />';

		JArrayHelper::toInteger( $cids );
		$cids = implode( ',', $cids );
		$query =	'	SELECT u.email
						FROM #__users AS u
						WHERE	
								u.block = 0 AND
								u.id IN (' . $cids . ')
						ORDER BY u.email';
		//echo $query . '<br />';
		$db->setQuery( $query );
    
     if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}

		return $records;
	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionMemberEMailAdress()
	 * 
	 * @param mixed $predictionMemberID
	 * @return
	 */
	function getPredictionMemberEMailAdress( $predictionMemberID )
	{
	// Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('user_id');
        $this->jsmquery->from('#__sportsmanagement_prediction_member');
        $this->jsmquery->where('id = ' . $predictionMemberID);


    $this->jsmdb->setQuery( $this->jsmquery );
		if ( !$user_id = $this->jsmdb->loadResult() ) 
        { 
            return false; 
            }
		
	// Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('email,username,id as user_id');
        $this->jsmquery->from('#__users AS u');
        $this->jsmquery->where('u.block = 0');
        $this->jsmquery->where('u.id = ' . $user_id);
        $this->jsmquery->order('u.email');
		
    $this->jsmdb->setQuery( $this->jsmquery );
		return $this->jsmdb->loadObject();
	}
    
    /**
     * sportsmanagementModelpredictionmember::getPredictionGroups()
     * 
     * @return
     */
    function getPredictionGroups()
    {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        
        $query = 'SELECT id, name as text FROM #__sportsmanagement_prediction_groups ORDER BY name ASC ';
		$this->_db->setQuery($query);
		if (!$result = $this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return array();
		}
		return $result;
    }

	/**
	 * Method to (un)publish / (un)approve a prediction member
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5.0a
	 */
	function publishpredmembers( $cid = array(), $publish = 1, $predictionGameID )
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        
		$user =& JFactory::getUser();
		if ( count( $cid ) )
		{
			$cids = implode( ',', $cid );

			$query =	'	UPDATE #__sportsmanagement_prediction_member
							SET approved = ' . (int) $publish . '
							WHERE id IN ( ' . $cids . ' )
							AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get( 'id' ) . ' ) )';

			$this->_db->setQuery( $query );
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' publish<br><pre>'.print_r($publish,true).'</pre>'),'');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionGameID<br><pre>'.print_r($predictionGameID,true).'</pre>'),'');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'');
            
			if ( !$this->_db->execute() )
			{
				//$this->setError( $this->_db->getErrorMsg() );
                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
				return false;
			}

			// create and send mail about approving member here

			$systemAdminsMails = $this->getSystemAdminsEMailAdresses();
			//echo '<br /><pre>~' . print_r( $systemAdminsMails, true ) . '~</pre><br />';

			$predictionGameAdminsMails = $this->getPredictionGameAdminsEMailAdresses( $predictionGameID );
			//echo '<br /><pre>~' . print_r( $predictionGameAdminsMails, true ) . '~</pre><br />';

			$predictionGameMembersMails = $this->getPredictionMembersEMailAdresses( $cids );
			//echo '<br /><pre>~' . print_r( $predictionGameMembersMails, true ) . '~</pre><br />';

			foreach ( $cid as $predictionMemberID )
			{
				//echo '<br /><pre>~' . print_r( $predictionMemberID, true ) . '~</pre><br />';

				$predictionGameMemberMail = $this->getPredictionMemberEMailAdress( $predictionMemberID );
				//echo '<br /><pre>~' . print_r( $predictionGameMemberMail, true ) . '~</pre><br />';

				if ( count( $predictionGameMemberMail ) > 0 )
				{
					//Fetch the mail object
					$mailer =& JFactory::getMailer();

					//Set a sender
					$config =& JFactory::getConfig();
					$sender = array( $config->getValue( 'config.mailfrom' ), $config->getValue( 'config.fromname' ) );
					//echo '<br /><pre>~' . print_r( $sender, true ) . '~</pre><br />';
					$mailer->setSender( $sender );

					//set Member as recipient
					$lastMailAdress = '';
					$recipient = array();
					foreach ( $predictionGameMemberMail AS $predictionGameMember_EMail )
					{
						if ( $lastMailAdress != $predictionGameMember_EMail )
						{
							$recipient[] = $predictionGameMember_EMail;
							$lastMailAdress = $predictionGameMember_EMail;
						}
					}
					//echo '<br />recipient<pre>~' . print_r( $recipient, true ) . '~</pre><br />';
					$mailer->addRecipient( $recipient );
					//unset( $recipient );

					//set system admins as BCC recipients
					$lastMailAdress = '';
					$recipientAdmins = array();
					foreach ( $systemAdminsMails AS $systemAdminMail )
					{
						if ( $lastMailAdress != $systemAdminMail )
						{
							$recipientAdmins[] = $systemAdminMail;
							$lastMailAdress = $systemAdminMail;
						}
					}
					$lastMailAdress = '';
					//echo '<br />recipientAdmins<pre>~' . print_r( $recipientAdmins, true ) . '~</pre><br />';

					//set predictiongame admins as BCC recipients
					foreach ( $predictionGameAdminsMails AS $predictionGameAdminMail )
					{
						if ( $lastMailAdress != $predictionGameAdminMail )
						{
							$recipientAdmins[] = $predictionGameAdminMail;
							$lastMailAdress = $predictionGameAdminMail;
						}
					}
					//echo '<br />recipientAdmins<pre>~' . print_r( $recipientAdmins, true ) . '~</pre><br />';
					$mailer->addBCC( $recipientAdmins );
					unset( $recipientAdmins );

					//Create the mail
					//$body = "Your body string\nin double quotes if you want to parse the \nnewlines etc";
					if ( $publish == 1 )
					{
						$mailer->setSubject( JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVED') );
						$body = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REQ_APPROVED');
					}
					else
					{
						$mailer->setSubject( JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REJECTED') );
						$body = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVEMENT_REJECTED');
					}
					$mailer->setBody( $body );
					echo '<br /><pre>~' . print_r( $mailer, true ) . '~</pre><br />';

					// Optional file attached
					//$mailer->addAttachment(PATH_COMPONENT.DS.'assets'.DS.'document.pdf');
					//echo '<br /><pre>~' . print_r( $mailer, true ) . '~</pre><br />';

					//Sending the mail
					$send =& $mailer->Send();
					if ( $send !== true )
					{
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_ERROR_SEND') . print_r( $recipient, true ) . '<br />';
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_ERROR_MSG') . $send->message;
					}
					else
					{
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_MAIL_SENT');
					}
					echo '<br /><br />';
				}
				else
				{
					// joomla_user is blocked or has set sendEmail to off
					// can't send email
					//return false;
				}
			}
		}

		return true;
	}

	

	/**
	 * sportsmanagementModelpredictionmember::deletePredictionMembers()
	 * 
	 * @param mixed $cid
	 * @return
	 */
	function deletePredictionMembers( $cid = array() )
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        
		if ( count( $cid ) )
		{
			JArrayHelper::toInteger( $cid );
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__sportsmanagement_prediction_member WHERE id IN (' . $cids . ')';
			$this->_db->setQuery( $query );
			if ( !$this->_db->execute() )
			{
				//$this->setError( $this->_db->getErrorMsg() );
                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
				return false;
			}
		}
		return true;
	}

	

	/**
	 * sportsmanagementModelpredictionmember::deletePredictionResults()
	 * 
	 * @param mixed $cid
	 * @param integer $prediction_id
	 * @return
	 */
	function deletePredictionResults($cid=array(),$prediction_id=0)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
    $db = sportsmanagementHelper::getDBConnection();
        
		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',',$cid);
			$query = 'SELECT user_id FROM #__sportsmanagement_prediction_member WHERE id IN (' . $cids . ') AND prediction_id = ' . $prediction_id;
			//echo $query . '<br />';
			$db->setQuery($query);
			$db->execute();

       if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}

			if (!$result = $records)
			{
				return true;
			}
			//echo '<pre>'; print_r($result); echo '</pre>';

			JArrayHelper::toInteger($result);
			$cids = implode(',',$result);
			$query = 'DELETE FROM #__sportsmanagement_prediction_result WHERE user_id IN (' . $cids . ') AND prediction_id = ' . $prediction_id;
			//echo $query . '<br />'; return true;
			$this->_db->setQuery($query);
			if (!$this->_db->execute())
			{
				//$this->setError($this->_db->getErrorMsg());
                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
				return false;
			}
		}
		return true;
	}
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $app = JFactory::getApplication();
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JFactory::getApplication()->input->post->getArray(array());
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
        
       // zuerst sichern, damit wir bei einer neuanlage die id haben
       if ( parent::save($data) )
       {
			$id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
            
            if ( $isNew )
            {
                //Here you can do other tasks with your newly saved record...
                $app->enqueueMessage(JText::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id),'');
            }
           
		}
        
        return true;    
    }  

}
?>
