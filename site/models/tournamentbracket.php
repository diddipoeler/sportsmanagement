<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       tournamentbracket.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Component\ComponentHelper;


class sportsmanagementModeltournamentbracket extends BaseDatabaseModel
{

function gettournamentbracket($project_id = 0)
{
$db    = $this->getDbo();
$query = $db->getQuery(true);
$result = array();
$tempmannschaften = array();
$team1_result = NULL;
$team2_result = NULL;
$teamsreturn = '';
$select_heim = 0;
$select_gast = 0;
//echo $project_id;

/** alle runden */
$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_round');
$query->where('project_id = ' . $project_id);
$query->where('tournement = 1');
$query->order('roundcode DESC');
$db->setQuery($query);
//$roundresult = $db->loadObjectList('id');
$roundresult = $db->loadObjectList();

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult2,true).'</pre>'  , '');

$start = 0;
$startselektion = 1;
$startergebnisse = 0;
$ergebnisse = array();
$mannschaften = array();

/** schleife über die runde anfang */
foreach ( $roundresult as $key => $value )
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. 'round key <pre>'.print_r($key,true).'</pre>'  , '');
$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_match');
$query->where('round_id = ' . $value->id);
$db->setQuery($query);
$matches = $db->loadObjectList();

if ( $matches && $startselektion )
{
$start = $key + 1;
/** schleife über die erste runde spiele anfang */
foreach ( $matches as $keymatch => $valuematch )
{

$mannschaften[$key][] = $valuematch->projectteam1_id;
$mannschaften[$key][] = $valuematch->projectteam2_id;

if ( !is_null($valuematch->team1_result_so) )
{
$team1_result = $valuematch->team1_result_so;
$team2_result = $valuematch->team2_result_so;
}

if ( !is_null($valuematch->team1_result_ot) && is_null($team1_result) )
{
$team1_result = $valuematch->team1_result_ot;
$team2_result = $valuematch->team2_result_ot;
}

if ( !is_null($valuematch->team1_result) && is_null($team1_result) )
{
$team1_result = $valuematch->team1_result;
$team2_result = $valuematch->team2_result;
}

$ergebnisse[$key][] = '['.$team1_result.','. $team2_result.']';
$team1_result = NULL;
$team2_result = NULL;


}
/** schleife über die erste runde spiele ende */
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '.$value->name. '<pre>'.print_r($matches,true).'</pre>'  , '');
$startselektion = 0;
}


}
/** schleife über die runde ende */


//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' ergebnisse'. '<pre>'.print_r($ergebnisse,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften'. '<pre>'.print_r($mannschaften,true).'</pre>'  , '');

for($a = $start; $a < sizeof($roundresult); $a++ )
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' variable a'. '<pre>'.print_r($a,true).'</pre>'  , '');

$startroundteams = $a - 1;

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften '. '<pre>'.print_r($mannschaften[$startroundteams],true).'</pre>'  , '');
  
  
foreach($mannschaften[$startroundteams] as $keystartteams => $valuestarteams )
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' valuestarteams'. '<pre>'.print_r($valuestarteams,true).'</pre>'  , '');

$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_match');
$query->where('round_id = ' . $roundresult[$a]->id );
$query->where('( projectteam1_id = ' . $valuestarteams .' OR projectteam2_id = '.$valuestarteams .' )' );
$db->setQuery($query);
try {
$singlematch = $db->loadObjectList();
} catch (Exception $e) {
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
Factory::getApplication()->enqueueMessage($msg, 'error'); // commonly to still display that error
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. '<pre>'.print_r($query->dump(),true).'</pre>'  , 'error');
}

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' valuestarteams -> '.$valuestarteams. '<pre>'.print_r($singlematch,true).'</pre>'  , '');  

  
$doppelrunde = sizeof($singlematch) > 1 ? 1 : 0 ;  
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' doppelrunde -> '.'<pre>'.print_r($doppelrunde,true).'</pre>'  , '');  
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' doppelrunde anzahl spiele  -> '.$roundresult[$a]->name .' projectteam-id -> ' .$valuestarteams.'<pre>'.print_r(sizeof($singlematch),true).'</pre>'  , '');  
  
/** einzelspiele anfang */  
$team1_result = NULL;
$team2_result = NULL;  
foreach ( $singlematch as $keymatch => $valuematch )
{
  
if ( $doppelrunde )
{
/**
if ( is_array($mannschaften[$a]) )
{
if (in_array($valuestarteams, $mannschaften[$a])) {
    echo $valuestarteams." enthalten<br>";
}
}
else
{
$mannschaften[$a][] = $valuematch->projectteam1_id;
$mannschaften[$a][] = $valuematch->projectteam2_id;
}
*/
}
else
{
$mannschaften[$a][] = $valuematch->projectteam1_id;
$mannschaften[$a][] = $valuematch->projectteam2_id;  
}
  
/** ergebnisse verarbeiten */  
if ( $doppelrunde )
{

if ( $valuestarteams == $valuematch->projectteam1_id )  
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' teamsuchen -> '.$valuestarteams.' projectteam id 1 '.   $valuematch->projectteam1_id.''  , '');   
if ( !is_null($valuematch->team1_result_so) )
{
$team1_result += $valuematch->team1_result_so;
$team2_result += $valuematch->team2_result_so;
}

if ( !is_null($valuematch->team1_result_ot)  )
{
$team1_result += $valuematch->team1_result_ot;
$team2_result += $valuematch->team2_result_ot;
}

if ( !is_null($valuematch->team1_result)  )
{
$team1_result += $valuematch->team1_result;
$team2_result += $valuematch->team2_result;
}  
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' team1_result'. '<pre>'.print_r($team1_result,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' team2_result'. '<pre>'.print_r($team2_result,true).'</pre>'  , '');  
}
  
if ( $valuestarteams == $valuematch->projectteam2_id )  
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' teamsuchen -> '.$valuestarteams.' projectteam id 2 '.   $valuematch->projectteam2_id.''  , '');     
if ( !is_null($valuematch->team1_result_so) )
{
$team2_result += $valuematch->team1_result_so;
$team1_result += $valuematch->team2_result_so;
}

if ( !is_null($valuematch->team1_result_ot)  )
{
$team2_result += $valuematch->team1_result_ot;
$team1_result += $valuematch->team2_result_ot;
}

if ( !is_null($valuematch->team1_result)  )
{
$team2_result += $valuematch->team1_result;
$team1_result += $valuematch->team2_result;
}  
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' team1_result'. '<pre>'.print_r($team1_result,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' team2_result'. '<pre>'.print_r($team2_result,true).'</pre>'  , '');  
}  
  
  
  
  
  
  
}
else
{  
if ( !is_null($valuematch->team1_result_so) )
{
$team1_result = $valuematch->team1_result_so;
$team2_result = $valuematch->team2_result_so;
}

if ( !is_null($valuematch->team1_result_ot) && is_null($team1_result) )
{
$team1_result = $valuematch->team1_result_ot;
$team2_result = $valuematch->team2_result_ot;
}

if ( !is_null($valuematch->team1_result) && is_null($team1_result) )
{
$team1_result = $valuematch->team1_result;
$team2_result = $valuematch->team2_result;
}

  

$ergebnisse[$a][] = '['.$team1_result.','. $team2_result.']';
  
$team1_result = NULL;
$team2_result = NULL;
}
  

$select_heim = $valuematch->projectteam1_id;
$select_gast = $valuematch->projectteam2_id;
}
/** einzelspiele ende */
if ( $a == sizeof($roundresult) - 1 )
{
 //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften'   , '');

$query->clear();
$query->select('t.name,c.logo_big,c.country');
$query->from('#__sportsmanagement_team AS t');
$query->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
$query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
$query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id ');
$query->where('pt.id = ' . $select_heim);
$db->setQuery($query);
$team_name_heim = $db->loadObject();

$query->clear();
$query->select('t.name,c.logo_big,c.country');
$query->from('#__sportsmanagement_team AS t');
$query->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
$query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
$query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id ');
$query->where('pt.id = ' . $select_gast);
$db->setQuery($query);
$team_name_gast = $db->loadObject();


//{name: "' . $key->firstname . '", flag: "' . $key->firstlogo . '"}
/** logo name */
//$tempmannschaften[] = '[ " <img src=\"' . Uri::base() .$team_name_heim->logo_big . '\" width=\"16\"> '.$team_name_heim->name.'"," <img src=\"' . Uri::base() .$team_name_gast->logo_big . '\" width=\"16\"> '. $team_name_gast->name.'"]';

/** flagge logo name */
$tempmannschaften[] = '[ "<img src=\"' . Uri::base() .'images/com_sportsmanagement/database/flags/' . strtolower(JSMCountries::convertIso3to2($team_name_heim->country)). '\" width=\"16\"> <img src=\"' . Uri::base() .$team_name_heim->logo_big . '\" width=\"16\"> '.$team_name_heim->name.'","<img src=\"' . Uri::base() .'images/com_sportsmanagement/database/flags/' . strtolower(JSMCountries::convertIso3to2($team_name_gast->country)). '\" width=\"16\"> <img src=\"' . Uri::base() .$team_name_gast->logo_big . '\" width=\"16\"> '. $team_name_gast->name.'"]';
} 
  
if ( $doppelrunde )
{
  
if ( $select_heim == $valuestarteams  )
{
$ergebnisse[$a][] = '['.$team1_result.','. $team2_result.']';   
$mannschaften[$a][] = $select_heim;
$mannschaften[$a][] = $select_gast;
}
  
if ( $select_gast == $valuestarteams  )
{
$ergebnisse[$a][] = '['.$team2_result.','. $team1_result.']';   
$mannschaften[$a][] = $select_heim;
$mannschaften[$a][] = $select_gast;
}
  
  /**
if ( is_array($mannschaften[$a]) )
{
if (in_array($select_heim, $mannschaften[$a])) {
    //echo $select_heim." enthalten<br>";
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' enthalten'. '<pre>'.print_r($select_heim,true).'</pre>'  , '');  
}
}
else
{
$mannschaften[$a][] = $select_heim;
$mannschaften[$a][] = $select_gast;
}  
*/  
  
  /**
$mannschaften[$a][] = $select_heim;
$mannschaften[$a][] = $select_gast;
*/  
  /**
  if ( $select_gast == $valuematch->projectteam2_id  )
  {
$ergebnisse[$a][] = '['.$team2_result.','. $team1_result.']';    
    $mannschaften[$a][] = $select_gast;
$mannschaften[$a][] = $select_heim;
  }
  elseif ( $select_gast == $valuematch->projectteam1_id  )
  {
$ergebnisse[$a][] = '['.$team1_result.','. $team2_result.']';    
    $mannschaften[$a][] = $select_heim;
$mannschaften[$a][] = $select_gast;
  }
  elseif ( $select_heim == $valuematch->projectteam1_id  )
  {
$ergebnisse[$a][] = '['.$team1_result.','. $team2_result.']';   
    $mannschaften[$a][] = $select_heim;
$mannschaften[$a][] = $select_gast;
  }
  elseif ( $select_heim == $valuematch->projectteam2id  )
  {
$ergebnisse[$a][] = '['.$team2_result.','. $team1_result.']';   
     $mannschaften[$a][] = $select_gast;
$mannschaften[$a][] = $select_heim;
  }
  */
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' projectteam1_id '.$a. '<pre>'.print_r($valuematch->projectteam1_id,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' projectteam2_id '.$a. '<pre>'.print_r($valuematch->projectteam2_id,true).'</pre>'  , '');  
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' team2_result'. '<pre>'.print_r($team2_result,true).'</pre>'  , '');  

  
$team1_result = NULL;
$team2_result = NULL;  
}
  
}



}


//}

//if ( $a == sizeof($roundresult) - 1 )
//  {
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften'. '<pre>'.print_r($tempmannschaften,true).'</pre>'  , '');
$teamsreturn = '['.implode(",",$tempmannschaften).']';
 // }

krsort($ergebnisse);
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' ergebnisse'. '<pre>'.print_r($ergebnisse,true).'</pre>'  , '');
$ergebnisreturn = array();
foreach ( $ergebnisse as $key => $value )
{
$ergebnisreturn[] = '['.implode(",",$value).']';

}
  //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' ergebnisreturn'. '<pre>'.print_r($ergebnisreturn,true).'</pre>'  , '');
  //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' teamsreturn'. '<pre>'.print_r($teamsreturn,true).'</pre>'  , '');

$result['teams'] = $teamsreturn;
$result['results'] = '['.implode(",",$ergebnisreturn).']';
return $result;
}


}

?>
