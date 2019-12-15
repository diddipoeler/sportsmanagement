<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      jltournementtree.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

JLoader::import('components.com_sportsmanagement.models.treetonode', JPATH_SITE);

jimport( 'joomla.utilities.utility' );


/**
 * sportsmanagementModeljltournamenttree
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljltournamenttree extends BaseDatabaseModel
{
var $projectid = 0;
var $project_art_id = 0;
var $from = 0;
var $to = 0;
var $team_strlen = 0;
var $round = 0;
var $count_tournament_round = 0;
var $menue_itemid = 0;
var $request = array();
var $allmatches = array();
var $bracket = array();
var $menue_params = array();
var $exist_result = array();
var $debug_info = false;
var $club_logo = 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
var $color_from = '#FFFFFF';
var $color_to = '#0000FF';
var $which_first_round = 'scrollLeft()';
var $font_size = 14;
var $jl_tree_bracket_round_width = 100;
var $jl_tree_bracket_teamb_width = 70;
var $jl_tree_bracket_width = 140;
var $jl_tree_jquery_version = '1.7.1';


/**
 * sportsmanagementModeljltournamenttree::__construct()
 * 
 * @return void
 */
function __construct( )
	{
//        $menu =  Factory::getApplication()->getMenu();
		$this->projectid = Factory::getApplication()->input->getInt( "p", 0 );
// 		$this->from  = Factory::getApplication()->input->getInt( 'from', 0 );
// 		$this->to	 = Factory::getApplication()->input->getInt( 'to', 0 );
// 		$this->round = Factory::getApplication()->input->getVar( "r");
//        $this->request = Factory::getApplication()->input->get();
//        $this->menue_itemid = Factory::getApplication()->input->getInt( "Itemid", 0 );
//        
//        $this->request['r'] = (int) $this->request['r'];
//        if ( isset($this->request['from']) )
//        {
//        $this->request['from'] = (int) $this->request['from'];
//        }
//        else
//        {
//        $this->request['from'] = (int) $this->request['r'];
//        }
//        
//        if ( isset($this->request['to']) )
//        {
//        $this->request['to'] = (int) $this->request['to'];
//        }
//        else
//        {
//        $this->request['to'] = (int) $this->request['r'];
//        }
//        
//        $item = $menu->getItem($this->menue_itemid);
//        $this->menue_params = new Registry();
//        
//        if ( $this->menue_params->get('jl_tree_jquery_version') )
//        {
//        $this->jl_tree_jquery_version = $this->menue_params->get('jl_tree_jquery_version');    
//        }
//        
//        if ( $this->menue_params->get('jl_tree_color_from') )
//        {
//        $this->color_from = $this->menue_params->get('jl_tree_color_from');    
//        }
//        
//        if ( $this->menue_params->get('jl_tree_color_to') )
//        {
//        $this->color_to = $this->menue_params->get('jl_tree_color_to');    
//        }
//        
//        
//        if ( $this->menue_params->get('jl_tree_font_size') )
//        {
//        $this->font_size = $this->menue_params->get('jl_tree_font_size');    
//        }
//        
// 		
//        if ( $this->menue_params->get('jl_tree_bracket_round_width') )
//        {
//        $this->jl_tree_bracket_round_width = $this->menue_params->get('jl_tree_bracket_round_width');    
//        }
//        
//        
//        if ( $this->menue_params->get('which_first_round') )
//        {
//        $this->which_first_round = $this->menue_params->get('which_first_round');    
//        }
//        
//        
//  if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//  {
//  $this->debug_info = true;
//  }
//  else
//  {
//  $this->debug_info = false;
//  }
// 
// // Reference global application object
//        $this->jsmapp = Factory::getApplication();
//        // JInput object
//        $this->jsmjinput = $this->jsmapp->input;
//        $this->jsmoption = $this->jsmjinput->getCmd('option');


		parent::__construct( );
	}

/**
 * sportsmanagementModeljltournamenttree::getWhichJQuery()
 * 
 * @return
 */
function getWhichJQuery()
{
return $this->jl_tree_jquery_version;    
}

/**
 * sportsmanagementModeljltournamenttree::getWhichShowFirstRound()
 * 
 * @return
 */
function getWhichShowFirstRound()
{
return $this->which_first_round;
}

/**
 * sportsmanagementModeljltournamenttree::getTreeBracketRoundWidth()
 * 
 * @return
 */
function getTreeBracketRoundWidth()
{
global $app;
    
//-- Einlesen der Feldnamen
if ( $this->project_art_id == 3 )
{
$tableName = '#__sportsmanagement_person';    
}
else
{
$tableName = '#__sportsmanagement_team';    
}

$tFields = $this->_db->getTableFields($tableName, false);
                    
$this->jl_tree_bracket_round_width = 16 + ( $this->team_strlen * 4 ) + 25 + 100;  
// laenge des zu selektierenden feldes

$fieldName = $this->request['tree_name'];
$fieldtype = $tFields[$tableName][$fieldName]->Type;
  
return $this->jl_tree_bracket_round_width;    
}

/**
 * sportsmanagementModeljltournamenttree::getTreeBracketTeambWidth()
 * 
 * @return
 */
function getTreeBracketTeambWidth()
{
$this->jl_tree_bracket_teamb_width = ( 70 * $this->jl_tree_bracket_round_width / 100);
return $this->jl_tree_bracket_teamb_width;
}

/**
 * sportsmanagementModeljltournamenttree::getTreeBracketWidth()
 * 
 * @return
 */
function getTreeBracketWidth()
{
//$this->jl_tree_bracket_width =  $this->jl_tree_bracket_teamb_width * 2;
//$this->jl_tree_bracket_width =  ( 40 * $this->jl_tree_bracket_round_width / 100) + $this->jl_tree_bracket_round_width;
//$this->jl_tree_bracket_width = ( $this->count_tournament_round * $this->jl_tree_bracket_round_width ) + 40;
$this->jl_tree_bracket_width = $this->jl_tree_bracket_round_width + 40;

//$this->jl_tree_bracket_width = ( $this->team_strlen * 12 ) + 40;

return $this->jl_tree_bracket_width;
}

/**
 * sportsmanagementModeljltournamenttree::getFontSize()
 * 
 * @return
 */
function getFontSize()
{
return $this->font_size;    
}

/**
 * sportsmanagementModeljltournamenttree::getColorFrom()
 * 
 * @return
 */
function getColorFrom()
{
return $this->color_from;    
}

/**
 * sportsmanagementModeljltournamenttree::getColorTo()
 * 
 * @return
 */
function getColorTo()
{
return $this->color_to;    
}

///**
// * sportsmanagementModeljltournamenttree::getTournamentName()
// * 
// * @return
// */
//function getTournamentName()
//{
//$option = Factory::getApplication()->input->getCmd('option');
//$app = Factory::getApplication();
//$user = Factory::getUser();
//
//$db = Factory::getDBO();
//$query = $db->getQuery(true);
////$subQuery = $db->getQuery(true);
////$subQuery2 = $db->getQuery(true);
//
//$query->select("name,project_art_id");
//$query->from('#__sportsmanagement_project');
//$query->where('id = '.$this->projectid);
//    
//$db->setQuery($query);
//$result = $db->loadObject();
//$this->project_art_id = $result->project_art_id;
//$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
//return $result->name;
//		
//}

	
/**
 * sportsmanagementModeljltournamenttree::getTournamentRounds()
 * 
 * @return
 */
function getTournamentRounds()
{
//$option = Factory::getApplication()->input->getCmd('option');
//$app = Factory::getApplication();
//$user = Factory::getUser();
$db = Factory::getDBO();
$query = $db->getQuery(true);

//if ( ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend') )
//{
//
//}
//
//// wurden die wichtigen einstellungen �ben ?
//if ( !array_key_exists('tree_name', $this->request) )
//{
//    $this->request['tree_name'] = 'middle_name';
//}
//if ( !array_key_exists('tree_logo', $this->request) )
//{
//    $this->request['tree_logo'] = '1';
//}

///**
// * als erstes lesen wir den roundcode der runde,
// * damit wir danach alle runden selektieren, die zum turnier gehören
// */
//$query->clear();
//$query->select("ro.roundcode");
//$query->from('#__sportsmanagement_round as ro');
//$query->join('INNER','#__sportsmanagement_match as ma on ma.round_id = ro.id');
//$query->where('ro.project_id = '.$this->projectid);
//$query->where('ro.id = '. (int)$this->round);
//$db->setQuery($query);
//$result_roundcode = $db->loadResult();



$query->clear();
$query->select("ro.*");
$query->from('#__sportsmanagement_round as ro');
$query->join('INNER','#__sportsmanagement_match as ma on ma.round_id = ro.id');
$query->where('ro.project_id = '.$this->projectid);
//$query->where("ro.roundcode <= ".$result_roundcode);

/*
// von bis runde gesetzt
if ( array_key_exists('from', $this->request) 
&& array_key_exists('to', $this->request) 
&& !empty( $this->request['from']) 
&& !empty($this->request['to']) )
{
$query->where("( ro.id between ".$this->request['from']." and ".$this->request['to']." )");
}
*/

/*
// nur von runde gesetzt
if ( array_key_exists('from', $this->request) 
&& !array_key_exists('to', $this->request) 
&& !empty($this->request['from']) )
{
$query->where("( ro.id between ".$this->request['from']." and ".$this->request['from']." )");
}
*/

/*
// nur bis runde gesetzt
if ( !array_key_exists('from', $this->request) 
&& array_key_exists('to', $this->request) 
&& !empty($this->request['to']) )
{
$query->where("( ro.id between ".$this->request['to']." and ".$this->request['from']." )");
}
*/

//// array an runden gesetzt
//if ( array_key_exists('r', $this->request) && !empty($this->request['r'] ) )
//{
////$temprounds = explode("|",$this->request['r']);
////$selectrounds = implode(",",$temprounds);
////$query->where("ro.id IN (".$selectrounds.")");
//$query->where("ro.tournement = 1");
//}

$query->where("ro.tournement = 1");

//// keine runden gesetzt
//if ( !array_key_exists('from', $this->request) 
//&& !array_key_exists('to', $this->request) 
//&& !array_key_exists('r', $this->request) )
//{
//$query->where("ma.count_result = 0");
//}

$query->group("ro.roundcode");
$query->order("ro.roundcode DESC");

$db->setQuery($query);

//if ( ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend') )
//{
//
//}

$this->count_tournament_round = count($db->loadObjectList());	
$result = $db->loadObjectList();
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
return $result;
		

}

/**
 * sportsmanagementModeljltournamenttree::getTournamentBracketRounds()
 * 
 * @param mixed $rounds
 * @return
 */
function getTournamentBracketRounds($rounds)
{
$temp_rounds = array();

foreach ( $rounds as $round )
{
$temp_rounds[$round->roundcode] = '{roundname: "'.$round->name.'"}';   
}    

ksort($temp_rounds);

return '['.implode(",",$temp_rounds).']';
    
}



/**
 * sportsmanagementModeljltournamenttree::getTournamentMatches()
 * 
 * @param mixed $rounds
 * @return
 */
function getTournamentMatches($rounds=null)
{
$option = Factory::getApplication()->input->getCmd('option');
$app = Factory::getApplication();
$user = Factory::getUser();
$db = Factory::getDBO();
$query = $db->getQuery(true);

$mdl = BaseDatabaseModel::getInstance("Treetonode", "sportsmanagementModel");
$mdl->projectid = $this->projectid;
$result = $mdl->getTreetonode();
usort($result , function($a, $b) {return $a->node > $b->node ;});
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' result <pre>'.print_r($result ,true).'</pre>'  , '');

foreach ( $result as $key => $value  ) if ( $value->match_id > 0 )
{
//$query->clear();     
//$query->select('r.roundcode,m.team1_result,m.team2_result');   
//$query->from('#__sportsmanagement_match AS m');        
//$query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id');    
//$query->where('m.id = '.$value->match_id);
//$db->setQuery($query);
//$matchresult = $db->loadObject();    
//
//$value->team1_result = $matchresult->team1_result;
//$value->team2_result = $matchresult->team2_result;
//$value->roundcode = $matchresult->roundcode;


$match = sportsmanagementModelMatch::getMatchData($value->match_id,Factory::getApplication()->input->getInt( "cfg_which_database", 0 ));
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' match <pre>'.print_r($match ,true).'</pre>'  , '');
//$this->allmatches[$value->match_id] = $match;


$temp = new stdClass();
$temp->match_id = $value->match_id;
$temp->projectteam1_id = $match->projectteam1_id;
$temp->projectteam2_id = $match->projectteam2_id;
$temp->team1_result = $match->team1_result;
$temp->team2_result = $match->team2_result;
//$temp->firstname = 'FREILOS';
//$temp->secondname = $key->secondname;
//$temp->firstcountry = 'DEU';
//$temp->secondcountry = $key->secondcountry;
//$temp->firstlogo = Uri::base().'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
//$temp->secondlogo = $key->secondlogo;
$export[] = $temp;
$this->bracket[$match->roundcode] = array_merge($export);


    
}

foreach ( $result as $key => $value  ) 
{


}

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' result <pre>'.print_r($result ,true).'</pre>'  , '');
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' bracket <pre>'.print_r($this->bracket ,true).'</pre>'  , '');

/*
$temp = new stdClass();
$temp->projectteam1_id = '';
$temp->projectteam2_id = $key->projectteam2_id;
$temp->team1_result = '0';
$temp->team2_result = '1';
$temp->firstname = 'FREILOS';
$temp->secondname = $key->secondname;
$temp->firstcountry = 'DEU';
$temp->secondcountry = $key->secondcountry;
$temp->firstlogo = Uri::base().'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
$temp->secondlogo = $key->secondlogo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);
*/
/** jetzt die teams und ergebnisse zusammenstellen */
$varteams = array();
$this->request['tree_logo'] = 1;
//if ( $this->exist_result[$roundcode] )
//{
/** die mannschaften */
foreach ( $this->bracket[$roundcode] as $key  )
{
switch ( $this->request['tree_logo'] )
{
case 1:
$varteams[] = '[{name: "'.$key->firstname.'", flag: "'.$key->firstlogo.'"}, {name: "'.$key->secondname.'", flag: "'.$key->secondlogo.'"}]';
break;
case 2:
$varteams[] = '[{name: "'.$key->firstname.'", flag: "'.Uri::base().'images/com_sportsmanagement/database/flags/'.strtolower(JSMCountries::convertIso3to2($key->firstcountry)).'.png"}, {name: "'.$key->secondname.'", flag: "'.Uri::base().'images/com_sportsmanagement/database/flags/'.strtolower(JSMCountries::convertIso3to2($key->secondcountry)).'.png"}]';
break;
}
}

//}

Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' varteams <pre>'.print_r($varteams ,true).'</pre>'  , '');
return implode(",",$varteams);
/**
 * erst einmal nicht ausgeprägt
 * zu lange laufzeit
 */
//return false;
	
$db = Factory::getDBO();
$query = $db->getQuery(true);

$subQuery = $db->getQuery(true);
$subQuery2 = $db->getQuery(true);
        
$round_matches = array();
$round_matches[1] = 1;
$round_matches[2] = 2;
$round_matches[4] = 4;
$round_matches[8] = 8;
$round_matches[16] = 16;
$round_matches[32] = 32;
$round_matches[64] = 64;
$round_matches[128] = 128;
$round_matches[256] = 256;
$round_matches[512] = 512;
$round_matches[1024] = 1024;
$round_matches[2048] = 2048;

$this->request['tree_name'] = 'name';

if ( $this->project_art_id == 3 )
{

// Select some fields
$query->select('m.id,m.round_id,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');   
$query->select("concat(c1.lastname,' - ',c1.firstname,'' ) AS firstname,c1.country as firstcountry,c1.picture as firstlogo");
$query->select("concat(c2.lastname,' - ',c2.firstname,'' ) AS secondname,c2.country as secondcountry,c2.picture as secondlogo");

// From the table
$query->from('#__sportsmanagement_match AS m');        
$query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id');  

$subQuery->select("tt1.id as team_id,c1.lastname,c1.firstname,c1.country,c1.picture");
$subQuery->from('#__sportsmanagement_person AS c1');
$subQuery->join('INNER','#__sportsmanagement_season_person_id AS tp1 ON c1.id = tp1.person_id');
$subQuery->join('INNER','#__sportsmanagement_project_team AS tt1 ON tt1.team_id = tp1.id');  
$query->join('LEFT','(' . $subQuery . ') AS c1 on m.projectteam1_id = tp1.id ');

$subQuery2->select("tt2.id as team_id,c2.lastname,c2.firstname,c2.country,c2.picture");
$subQuery2->from('#__sportsmanagement_person AS c2');
$subQuery2->join('INNER','#__sportsmanagement_season_person_id AS tp2 ON c2.id = tp2.person_id');
$subQuery2->join('INNER','#__sportsmanagement_project_team AS tt2 ON tt2.team_id = tp2.id');  
$query->join('LEFT','(' . $subQuery2 . ') AS c2 on m.projectteam2_id = tp2.id ');
//$query2 = $query;       
}
else
{    
// Select some fields
$query->select('m.id,m.round_id,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');   
$query->select("c1.teamname AS firstname,c1.country as firstcountry,c1.logo_big as firstlogo");
$query->select("c2.teamname AS secondname,c2.country as secondcountry,c2.logo_big as secondlogo");

// From the table
$query->from('#__sportsmanagement_match AS m');        
$query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id');  

$subQuery->select("tt1.id as team_id,t1.".$this->request['tree_name']." as teamname,c1.country,c1.logo_big");
$subQuery->select("tt1.project_id as tt1_project_id");
$subQuery->from('#__sportsmanagement_team AS t1');
$subQuery->join('INNER','#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');
$subQuery->join('INNER','#__sportsmanagement_season_team_id AS tp1 ON t1.id = tp1.team_id');
$subQuery->join('INNER','#__sportsmanagement_project_team AS tt1 ON tt1.team_id = tp1.id');  
$subQuery->where('tt1.project_id = '.$this->projectid);
$query->join('LEFT','(' . $subQuery . ') AS c1 on m.projectteam1_id = c1.team_id ');

$subQuery2->select("tt2.id as team_id,t2.".$this->request['tree_name']." as teamname,c2.country,c2.logo_big");
$subQuery2->select("tt2.project_id as tt2_project_id");
$subQuery2->from('#__sportsmanagement_team AS t2');
$subQuery2->join('INNER','#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');
$subQuery2->join('INNER','#__sportsmanagement_season_team_id AS tp2 ON t2.id = tp2.team_id');
$subQuery2->join('INNER','#__sportsmanagement_project_team AS tt2 ON tt2.team_id = tp2.id');
$subQuery->where('tt2.project_id = '.$this->projectid);  
$query->join('LEFT','(' . $subQuery2 . ') AS c2 on m.projectteam2_id = c2.team_id  ');
//$query2 = $query;
}

$query->group('m.id ');  
$query->order('m.match_date ASC,m.match_number ASC');  

$matches = array();
$durchlauf = count($rounds);
$zaehler = 1;
$roundcode = '';

foreach ( $rounds as $round )
{
unset($export);

switch ($zaehler)
{
case 1:
$query->clear('where');
$query->where('m.published = 1 ');  
$query->where('r.id = '.$round->id);
$query->where('r.project_id = '.$this->projectid);
//$query->where('c2.tt2_project_id = '.$this->projectid);
//$query->where('c1.tt1_project_id = '.$this->projectid);
$db->setQuery($query);
$result = $db->loadObjectList();

 
foreach ( $result as $row )
{

if ( $this->debug_info )
{
echo 'bild heim -> '.Uri::base().$row->firstlogo. '<br />';
}

if( !File::exists(Uri::base().$row->firstlogo) )
{
$row->firstlogo = Uri::base().$this->club_logo;
}
else
{
$row->firstlogo = Uri::base().$row->firstlogo;
}

if( !File::exists(Uri::base().$row->secondlogo) )
{
$row->secondlogo = Uri::base().$this->club_logo;
}
else
{
$row->secondlogo = Uri::base().$row->secondlogo;
}

// pr�b die ergebnisse in der runde schon gesetzt sind
// die runde kann ja auch hin- und r�el beinhalten
if ( array_key_exists($round->roundcode, $this->bracket) && count($result) > 1 )
{


$finished = true;
//foreach ( $this->bracket[$round->roundcode] as $value )
foreach ( $this->bracket[$round->roundcode] as $key => $value )
//for ($a=0; $a < sizeof($this->bracket[$round->roundcode]); $a++  )
{

//$value = $this->bracket[$round->roundcode][$a];

//echo '<b>1 value -> '.$value.'</b><br />'; 


//foreach ( $key as $value )
//{
if ( $value->projectteam1_id == $row->projectteam1_id &&  $value->projectteam2_id == $row->projectteam2_id )
{
//echo '1 a.) paarung gefunden -> '.$row->firstname.' - '.$row->secondname.'<br />';    
$value->team1_result += $row->team1_result;
$value->team2_result += $row->team2_result;
$finished = true;
break; 
}
elseif ( $value->projectteam1_id == $row->projectteam2_id &&  $value->projectteam2_id == $row->projectteam1_id )
{
//echo '1 b.) paarung gefunden -> '.$row->firstname.' - '.$row->secondname.'<br />';    
$value->team1_result += $row->team2_result;
$value->team2_result += $row->team1_result;
$finished = true;
break;
}
else
{
//echo '1 c.) paarung nicht gefunden -> '.$row->firstname.' - '.$row->secondname.'<br />'; 
//echo '1 c.) row id ->   '.$row->projectteam1_id.' - '.$row->projectteam2_id.'<br />';
//echo '1 c.) value id -> '.$value->projectteam1_id.' - '.$value->projectteam2_id.'<br />';

$finished = false;
//break;
}

}

if ( !$finished )
{
$temp = new stdClass();
$temp->projectteam1_id = $row->projectteam1_id;
$temp->projectteam2_id = $row->projectteam2_id;
$temp->team1_result = $row->team1_result;
$temp->team2_result = $row->team2_result;    
$temp->firstname = $row->firstname;
$temp->secondname = $row->secondname;
$temp->firstcountry = $row->firstcountry;
$temp->secondcountry = $row->secondcountry;
$temp->firstlogo = $row->firstlogo;
$temp->secondlogo = $row->secondlogo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);

if ( strlen($row->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($row->firstname);
}
if ( strlen($row->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($row->secondname);
}

}

}
else
{
$temp = new stdClass();
$temp->projectteam1_id = $row->projectteam1_id;
$temp->projectteam2_id = $row->projectteam2_id;
$temp->team1_result = $row->team1_result;
$temp->team2_result = $row->team2_result;    
$temp->firstname = $row->firstname;
$temp->secondname = $row->secondname;
$temp->firstcountry = $row->firstcountry;
$temp->secondcountry = $row->secondcountry;
$temp->firstlogo = $row->firstlogo;
$temp->secondlogo = $row->secondlogo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);
$this->exist_result[$round->roundcode] = true;
if ( strlen($row->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($row->firstname);
}
if ( strlen($row->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($row->secondname);
}
}


}

$roundcode = $round->roundcode;		



// passt die erste gew�te runde zu einem turnierbaum ?
$count_matches = sizeof( $this->bracket[$round->roundcode] );



$i = $count_matches;
if ( $count_matches > 4 )
{

if ( !array_key_exists($count_matches, $round_matches) ) 
{ 



$finished = false;
while ( ! $finished ):
if ( array_key_exists($i, $round_matches) )
{

$finished = true;     
}
else
{

$i++;    
}
endwhile; 
 
}
else
{
    
} 

}

$new_matches = $i - $count_matches;
 

for ($a=1; $a <= $new_matches; $a++)
{
$temp = new stdClass();
$temp->projectteam1_id = '';
$temp->projectteam2_id = '';
$temp->team1_result = '';
$temp->team2_result = '';    
$temp->firstname = 'FREI';
$temp->secondname = 'FREI';
$temp->firstcountry = 'DEU';
$temp->secondcountry = 'DEU';
$temp->firstlogo = Uri::base().$this->club_logo;
$temp->secondlogo = Uri::base().$this->club_logo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);

if ( strlen($temp->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($temp->firstname);
}
if ( strlen($temp->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($temp->secondname);
}

}


break;

//case 2:
//case 3:
default:

foreach ( $this->bracket[$roundcode] as $key  )
{
//unset($query);
//$query = $query2;
$query->clear('where');
$query->where('m.published = 1 ');
$query->where('r.id = '.$round->id);    
$query->where('(m.projectteam1_id = '.$key->projectteam1_id.' or m.projectteam2_id = '.$key->projectteam1_id.' )');
$query->where('r.project_id = '.$this->projectid);
//$query->where('c2.tt2_project_id = '.$this->projectid);
//$query->where('c1.tt1_project_id = '.$this->projectid);

//$query_WHERE = ' WHERE m.published = 1 
//			  AND r.id = '.$round->id.'
//              and (m.projectteam1_id = '.$key->projectteam1_id.' or m.projectteam2_id = '.$key->projectteam1_id.' ) 
//			  AND r.project_id = '.$this->projectid;
		
//$query = $query_SELECT.$query_FROM.$query_WHERE.$query_END;
$db->setQuery($query);
$result = $db->loadObjectList();





if ( $result  )
{
if ( count($result) > 1  )
{
$durchlauf = 1;
$temp = new stdClass();

foreach ( $result as $rowresult )
{

/*
if( !File::exists(JPATH_ROOT.'/'.$rowresult->firstlogo) )
{
$rowresult->firstlogo = $this->club_logo;
}
if( !File::exists(JPATH_ROOT.'/'.$rowresult->secondlogo) )
{
$rowresult->secondlogo = $this->club_logo;
}
*/

if( !File::exists(Uri::base().$rowresult->firstlogo) )
{
$rowresult->firstlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->firstlogo = Uri::base().$rowresult->firstlogo;
}

if( !File::exists(Uri::base().$rowresult->secondlogo) )
{
$rowresult->secondlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->secondlogo = Uri::base().$rowresult->secondlogo;
}

switch ($durchlauf)
{
case 1:
$temp->projectteam1_id = $rowresult->projectteam1_id;
$temp->projectteam2_id = $rowresult->projectteam2_id;
$temp->team1_result = $rowresult->team1_result;
$temp->team2_result = $rowresult->team2_result;
$temp->firstname = $rowresult->firstname;
$temp->secondname = $rowresult->secondname;
$temp->firstcountry = $rowresult->firstcountry;
$temp->secondcountry = $rowresult->secondcountry;
$temp->firstlogo = $rowresult->firstlogo;
$temp->secondlogo = $rowresult->secondlogo;

if ( strlen($rowresult->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->firstname);
}
if ( strlen($rowresult->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->secondname);
}

break;

default:
$temp->team1_result += $rowresult->team2_result;
$temp->team2_result += $rowresult->team1_result;
break;

}


$durchlauf++;
}
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);

}
else
{

foreach ( $result as $rowresult )
{

/*
if( !File::exists(JPATH_ROOT.'/'.$rowresult->firstlogo) )
{
$rowresult->firstlogo = $this->club_logo;
}
if( !File::exists(JPATH_ROOT.'/'.$rowresult->secondlogo) )
{
$rowresult->secondlogo = $this->club_logo;
}
*/

if( !File::exists(Uri::base().$rowresult->firstlogo) )
{
$rowresult->firstlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->firstlogo = Uri::base().$rowresult->firstlogo;
}

if( !File::exists(Uri::base().$rowresult->secondlogo) )
{
$rowresult->secondlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->secondlogo = Uri::base().$rowresult->secondlogo;
}
    
$temp = new stdClass();
$temp->projectteam1_id = $rowresult->projectteam1_id;
$temp->projectteam2_id = $rowresult->projectteam2_id;
$temp->team1_result = $rowresult->team1_result;
$temp->team2_result = $rowresult->team2_result;
$temp->firstname = $rowresult->firstname;
$temp->secondname = $rowresult->secondname;
$temp->firstcountry = $rowresult->firstcountry;
$temp->secondcountry = $rowresult->secondcountry;
$temp->firstlogo = $rowresult->firstlogo;
$temp->secondlogo = $rowresult->secondlogo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);
if ( strlen($rowresult->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->firstname);
}
if ( strlen($rowresult->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->secondname);
}
}

}

}
else
{

if ( count($this->bracket[$roundcode]) > 1 )
{
$temp = new stdClass();
$temp->projectteam1_id = $key->projectteam1_id;
$temp->projectteam2_id = '';
$temp->team1_result = '1';
$temp->team2_result = '0';
$temp->firstname = $key->firstname;
$temp->secondname = 'FREILOS';
$temp->firstcountry = $key->firstcountry;
$temp->secondcountry = 'DEU';
$temp->firstlogo = $key->firstlogo;
$temp->secondlogo = Uri::base().'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);

if ( strlen($temp->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($temp->firstname);
}
if ( strlen($temp->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($temp->secondname);
}

}

}
//unset($query);
//$query = $query2;
$query->clear('where');
$query->where('m.published = 1 ');  
$query->where('(m.projectteam1_id = '.$key->projectteam2_id.' or m.projectteam2_id = '.$key->projectteam2_id.' )');
$query->where('r.id = '.$round->id);
$query->where('r.project_id = '.$this->projectid);
//$query->where('c2.tt2_project_id = '.$this->projectid);
//$query->where('c1.tt1_project_id = '.$this->projectid);

$db->setQuery($query);
$result = $db->loadObjectList();





if ( $result )
{
if ( count($result) > 1  )
{
$durchlauf = 1;
$temp = new stdClass();
foreach ( $result as $rowresult )
{

/*
if( !File::exists(JPATH_ROOT.'/'.$rowresult->firstlogo) )
{
$rowresult->firstlogo = $this->club_logo;
}
if( !File::exists(JPATH_ROOT.'/'.$rowresult->secondlogo) )
{
$rowresult->secondlogo = $this->club_logo;
}
*/

if( !File::exists(Uri::base().$rowresult->firstlogo) )
{
$rowresult->firstlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->firstlogo = Uri::base().$rowresult->firstlogo;
}

if( !File::exists(Uri::base().$rowresult->secondlogo) )
{
$rowresult->secondlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->secondlogo = Uri::base().$rowresult->secondlogo;
}

switch ($durchlauf)
{
case 1:
$temp->projectteam1_id = $rowresult->projectteam1_id;
$temp->projectteam2_id = $rowresult->projectteam2_id;
$temp->team1_result = $rowresult->team1_result;
$temp->team2_result = $rowresult->team2_result;
$temp->firstname = $rowresult->firstname;
$temp->secondname = $rowresult->secondname;
$temp->firstcountry = $rowresult->firstcountry;
$temp->secondcountry = $rowresult->secondcountry;
$temp->firstlogo = $rowresult->firstlogo;
$temp->secondlogo = $rowresult->secondlogo;
if ( strlen($rowresult->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->firstname);
}
if ( strlen($rowresult->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->secondname);
}
break;

default:
$temp->team1_result += $rowresult->team2_result;
$temp->team2_result += $rowresult->team1_result;
break;

}

$durchlauf++;

}

$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);

}
else
{

foreach ( $result as $rowresult )
{

/*    
if( !File::exists(JPATH_ROOT.'/'.$rowresult->firstlogo) )
{
$rowresult->firstlogo = $this->club_logo;
}
if( !File::exists(JPATH_ROOT.'/'.$rowresult->secondlogo) )
{
$rowresult->secondlogo = $this->club_logo;
}
*/

if( !File::exists(Uri::base().$rowresult->firstlogo) )
{
$rowresult->firstlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->firstlogo = Uri::base().$rowresult->firstlogo;
}

if( !File::exists(Uri::base().$rowresult->secondlogo) )
{
$rowresult->secondlogo = Uri::base().$this->club_logo;
}
else
{
$rowresult->secondlogo = Uri::base().$rowresult->secondlogo;
}
    
$temp = new stdClass();
$temp->projectteam1_id = $rowresult->projectteam1_id;
$temp->projectteam2_id = $rowresult->projectteam2_id;
$temp->team1_result = $rowresult->team1_result;
$temp->team2_result = $rowresult->team2_result;
$temp->firstname = $rowresult->firstname;
$temp->secondname = $rowresult->secondname;
$temp->firstcountry = $rowresult->firstcountry;
$temp->secondcountry = $rowresult->secondcountry;
$temp->firstlogo = $rowresult->firstlogo;
$temp->secondlogo = $rowresult->secondlogo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);
if ( strlen($rowresult->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->firstname);
}
if ( strlen($rowresult->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($rowresult->secondname);
}
}

}

}
else
{

if ( count($this->bracket[$roundcode]) > 1 )
{    
$temp = new stdClass();
$temp->projectteam1_id = '';
$temp->projectteam2_id = $key->projectteam2_id;
$temp->team1_result = '0';
$temp->team2_result = '1';
$temp->firstname = 'FREILOS';
$temp->secondname = $key->secondname;
$temp->firstcountry = 'DEU';
$temp->secondcountry = $key->secondcountry;
$temp->firstlogo = Uri::base().'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
$temp->secondlogo = $key->secondlogo;
$export[] = $temp;
$this->bracket[$round->roundcode] = array_merge($export);

if ( strlen($temp->firstname) > $this->team_strlen )
{
$this->team_strlen = strlen($temp->firstname);
}
if ( strlen($temp->secondname) > $this->team_strlen )
{
$this->team_strlen = strlen($temp->secondname);
}

$result = true;
}

}

}

// m�freilose angelegt werden ?
$count_matches = sizeof ( $this->bracket[$round->roundcode] );




if ( $result )
{
$roundcode = $round->roundcode;
$this->exist_result[$round->roundcode] = true;
}
else
{
$this->exist_result[$round->roundcode] = false;
}

break;

}

$zaehler++;    

}





// jetzt die teams und ergebnisse zusammenstellen
$varteams = array();
$varresults = array();

if ( $this->exist_result[$roundcode] )
{
// die mannschaften
foreach ( $this->bracket[$roundcode] as $key  )
{
// mit flagge
// [{name: "Tschechien", flag: 'cz'}, {name: "Portugal", flag: 'pt'}]

switch ( $this->request['tree_logo'] )
{
case 1:
//$varteams[] = '[{name: "'.substr($key->firstname,0,10).'", flag: "'.$key->firstlogo.'"}, {name: "'.substr($key->secondname,0,10).'", flag: "'.$key->secondlogo.'"}]';
$varteams[] = '[{name: "'.$key->firstname.'", flag: "'.$key->firstlogo.'"}, {name: "'.$key->secondname.'", flag: "'.$key->secondlogo.'"}]';
break;

case 2:
//$varteams[] = '[{name: "'.substr($key->firstname,0,10).'", flag: "media/com_sportsmanagement/flags/'.strtolower(JSMCountries::convertIso3to2($key->firstcountry)).'.png"}, {name: "'.substr($key->secondname,0,10).'", flag: "media/com_sportsmanagement/flags/'.strtolower(JSMCountries::convertIso3to2($key->secondcountry)).'.png"}]';
$varteams[] = '[{name: "'.$key->firstname.'", flag: "'.Uri::base().'images/com_sportsmanagement/database/flags/'.strtolower(JSMCountries::convertIso3to2($key->firstcountry)).'.png"}, {name: "'.$key->secondname.'", flag: "'.Uri::base().'images/com_sportsmanagement/database/flags/'.strtolower(JSMCountries::convertIso3to2($key->secondcountry)).'.png"}]';
break;
    
}


// ohne flagge ist das 
// ["Tschechien", "Portugal"]
//$varteams[] = '["'.$key->firstname.'", "'.$key->secondname.'"]';
}

}

return implode(",",$varteams);

}

/**
 * sportsmanagementModeljltournamenttree::getTournamentResults()
 * 
 * @param mixed $rounds
 * @return
 */
function getTournamentResults($rounds)
{
    
$option = Factory::getApplication()->input->getCmd('option');
$app = Factory::getApplication();
$user = Factory::getUser();

$db = Factory::getDBO();
$query = $db->getQuery(true);
//$query2 = $db->getQuery(true);
$subQuery = $db->getQuery(true);
$subQuery2 = $db->getQuery(true);
    
if ( $this->project_art_id == 3 )
{
// Select some fields
$query->select('m.id,m.round_id,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');   
$query->select("concat(c1.lastname,' - ',c1.firstname,'' ) AS firstname,c1.country as firstcountry,c1.picture as firstlogo");
$query->select("concat(c2.lastname,' - ',c2.firstname,'' ) AS secondname,c2.country as secondcountry,c2.picture as secondlogo");

// From the table
$query->from('#__sportsmanagement_match AS m');        
$query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id');  

$subQuery->select("tt1.id as team_id,c1.lastname,c1.firstname,c1.country,c1.picture");
$subQuery->from('#__sportsmanagement_person AS c1');
$subQuery->join('INNER','#__sportsmanagement_season_person_id AS tp1 ON c1.id = tp1.person_id');
$subQuery->join('INNER','#__sportsmanagement_project_team AS tt1 ON tt1.team_id = tp1.id');
$subQuery->where('tt1.project_id = '.$this->projectid);  
$query->join('LEFT','(' . $subQuery . ') AS c1 on m.projectteam1_id = c1.team_id ');

$subQuery2->select("tt2.id as team_id,c2.lastname,c2.firstname,c2.country,c2.picture");
$subQuery2->from('#__sportsmanagement_person AS c2');
$subQuery2->join('INNER','#__sportsmanagement_season_person_id AS tp2 ON c2.id = tp2.person_id');
$subQuery2->join('INNER','#__sportsmanagement_project_team AS tt2 ON tt2.team_id = tp2.id');
$subQuery2->where('tt2.project_id = '.$this->projectid);  
$query->join('LEFT','(' . $subQuery2 . ') AS c2 on m.projectteam2_id = c2.team_id ');

//$query = $query2;        
}
else
{
// Select some fields
$query->select('m.id,m.round_id,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');   
$query->select("c1.teamname AS firstname,c1.country as firstcountry,c1.logo_big as firstlogo");
$query->select("c2.teamname AS secondname,c2.country as secondcountry,c2.logo_big as secondlogo");

// From the table
$query->from('#__sportsmanagement_match AS m');        
$query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id');  

$subQuery->select("tt1.id as team_id,t1.".$this->request['tree_name']." as teamname,c1.country,c1.logo_big");
$subQuery->from('#__sportsmanagement_team AS t1');
$subQuery->join('INNER','#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');
$subQuery->join('INNER','#__sportsmanagement_season_team_id AS tp1 ON t1.id = tp1.team_id');
$subQuery->join('INNER','#__sportsmanagement_project_team AS tt1 ON tt1.team_id = tp1.id');
$subQuery->where('tt1.project_id = '.$this->projectid);  
$query->join('LEFT','(' . $subQuery . ') AS c1 on m.projectteam1_id = c1.team_id ');

$subQuery2->select("tt2.id as team_id,t2.".$this->request['tree_name']." as teamname,c2.country,c2.logo_big");
$subQuery2->from('#__sportsmanagement_team AS t2');
$subQuery2->join('INNER','#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');
$subQuery2->join('INNER','#__sportsmanagement_season_team_id AS tp2 ON t2.id = tp2.team_id');
$subQuery2->join('INNER','#__sportsmanagement_project_team AS tt2 ON tt2.team_id = tp2.id');  
$subQuery2->where('tt2.project_id = '.$this->projectid);
$query->join('LEFT','(' . $subQuery2 . ') AS c2 on m.projectteam2_id = c2.team_id ');

//$query = $query2;
}


$query->group('m.id ');  
$query->order('m.match_date ASC,m.match_number ASC');
    
// die ergebnisse
// ergebnisse
// [[0,1], [2,0], [4,2], [2,4] ]
foreach ( $rounds as $round )
{
$vartempresults = array();

if ( $this->exist_result[$round->roundcode] )
{

foreach ( $this->bracket[$round->roundcode] as $key  )
{
$vartempresults[] = '['.$key->team1_result.','.$key->team2_result.']';
}



$varresults[$round->roundcode] = '['.implode(",",$vartempresults).']';

}
else
{

if ( sizeof( $this->bracket[$round->roundcode] ) == 0 )
{    
// es wurde der dritte platz ausgespielt
$query->where('m.published = 1 ');  
$query->where('r.id = '.$round->id);
$query->where('r.project_id = '.$this->projectid);

$db->setQuery($query);
$result = $db->loadObjectList();
		


foreach ( $result as $row )
{
$roundcode = $round->roundcode + 1;
foreach ( $this->bracket[$roundcode] as $key  )
{
$vartempresults[] = '['.$key->team1_result.','.$key->team2_result.']';
$vartempresults[] = '['.$row->team1_result.','.$row->team2_result.']';
}

}

if ( $vartempresults )
{
$varresults[$roundcode] = '['.implode(",",$vartempresults).']';
}

}

}

}

ksort($varresults);




return implode(",",$varresults);

}

/**
 * sportsmanagementModeljltournamenttree::checkStartExtension()
 * 
 * @return void
 */
function checkStartExtension()
{
$application = Factory::getApplication();
}

}

?>
