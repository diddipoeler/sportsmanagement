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
use Joomla\CMS\Utility\Utility;
JLoader::import('components.com_sportsmanagement.models.treetonode', JPATH_SITE);

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
var $count_tournament_round = 0;
var $menue_itemid = 0;
var $request = array();
var $allmatches = array();
var $bracket = array();
var $color_from = '#FFFFFF';
var $color_to = '#0000FF';
var $which_first_round = 'scrollLeft()';
var $font_size = 14;
var $jl_tree_bracket_round_width = 300;
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
		$this->projectid = Factory::getApplication()->input->getInt( "p", 0 );
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
$this->jl_tree_bracket_width = $this->jl_tree_bracket_round_width + 40;

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

/**
 * sportsmanagementModeljltournamenttree::getTournamentRounds()
 * 
 * @return
 */
function getTournamentRounds()
{
$db = Factory::getDBO();
$query = $db->getQuery(true);

$query->clear();
$query->select("ro.*");
$query->from('#__sportsmanagement_round as ro');
$query->join('INNER','#__sportsmanagement_match as ma on ma.round_id = ro.id');
$query->where('ro.project_id = '.$this->projectid);
$query->where("ro.tournement = 1");
$query->group("ro.roundcode");
$query->order("ro.roundcode DESC");
$db->setQuery($query);

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
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' result <pre>'.print_r($result ,true).'</pre>'  , '');

$query->clear();
$query->select('MIN(r.roundcode)');
$query->from('#__sportsmanagement_match AS m');
$query->join('INNER','#__sportsmanagement_round AS r ON r.id = m.round_id');
$query->where('r.project_id = ' . $this->projectid);
$query->where('r.tournement = 1');
$query->order('r.roundcode DESC');
$db->setQuery($query);
$minresult = $db->loadResult();

foreach ( $result as $key => $value  ) if ( $value->match_id != 0 )
{
$match = sportsmanagementModelMatch::getMatchData($value->match_id,Factory::getApplication()->input->getInt( "cfg_which_database", 0 ));
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' match <pre>'.print_r($match ,true).'</pre>'  , '');
//$this->allmatches[$value->match_id] = $match;


$temp = new stdClass();
$temp->match_id = $value->match_id;
$temp->projectteam1_id = $match->projectteam1_id;
$temp->projectteam2_id = $match->projectteam2_id;
$temp->team1_result = $match->team1_result;
$temp->team2_result = $match->team2_result;
$this->bracket[$match->roundcode][$value->match_id] = $temp;
    
}

foreach ( $this->bracket as $keybracket => $valuebracket  ) 
{
foreach ( $valuebracket as $bracket  ) 
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' bracket  <pre>'.print_r($bracket  ,true).'</pre>'  , '');
foreach ( $result as $key => $value  ) if ( $bracket->match_id == $value->match_id )
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' match_id<pre>'.print_r($value->match_id,true).'</pre>'  , '');
if ( $value->match_id < 0 )
{
$this->bracket[$keybracket][$bracket->match_id]->firstname = 'freilos'; 
$this->bracket[$keybracket][$bracket->match_id]->secondname = 'freilos';
$this->bracket[$keybracket][$bracket->match_id]->firstlogo = 'freilos'; 
$this->bracket[$keybracket][$bracket->match_id]->secondlogo = 'freilos';
$this->bracket[$keybracket][$bracket->match_id]->firstcountry = $value->country;
$this->bracket[$keybracket][$bracket->match_id]->secondcountry = $value->country;
$this->bracket[$keybracket][$bracket->match_id]->team1_result = 0;
$this->bracket[$keybracket][$bracket->match_id]->team2_result = 1;
}
if ( $value->team_id == $bracket->projectteam1_id )
{
$this->bracket[$keybracket][$bracket->match_id]->firstname = $value->team_name;   
$this->bracket[$keybracket][$bracket->match_id]->firstcountry = $value->country;
$this->bracket[$keybracket][$bracket->match_id]->firstlogo = Uri::base().$value->logo_big;    
}    
if ( $value->team_id == $bracket->projectteam2_id )
{
$this->bracket[$keybracket][$bracket->match_id]->secondname = $value->team_name;   
$this->bracket[$keybracket][$bracket->match_id]->secondcountry = $value->country;
$this->bracket[$keybracket][$bracket->match_id]->secondlogo = Uri::base().$value->logo_big;    
}    
}    
}
}

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' result <pre>'.print_r($result ,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' bracket <pre>'.print_r($this->bracket ,true).'</pre>'  , '');
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' rounds <pre>'.print_r($rounds ,true).'</pre>'  , '');

/** jetzt die teams und ergebnisse zusammenstellen */
$varteams = array();
$this->request['tree_logo'] = 1;

foreach ( $rounds as $keyround ) if ( $keyround->roundcode == $minresult )
{    
/** die mannschaften */
foreach ( $this->bracket[$keyround->roundcode] as $key  )
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
}

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' varteams <pre>'.print_r($varteams ,true).'</pre>'  , '');
return implode(",",$varteams);

}


/**
 * sportsmanagementModeljltournamenttree::getTournamentResults()
 * 
 * @param mixed $rounds
 * @return
 */
function getTournamentResults($rounds=NULL)
{

foreach ( $rounds as $round )
{
$vartempresults = array();

foreach ( $this->bracket[$round->roundcode] as $key  )
{
$vartempresults[] = '['.$key->team1_result.','.$key->team2_result.']';
}

$varresults[$round->roundcode] = '['.implode(",",$vartempresults).']';


}
$varresults[$round->roundcode] = '['.implode(",",$vartempresults).']';
ksort($varresults);

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' varresults <pre>'.print_r($varresults ,true).'</pre>'  , '');

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
