<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmopenligadb
 * @file       jsmopenligadb.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

$maxImportTime = 480;

if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = '350M';

if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	@ini_set('memory_limit', $maxImportMemory);
}

/**
 * sportsmanagementModeljsmopenligadb
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModeljsmopenligadb extends BaseDatabaseModel
{
	static public $success_text = '';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
	var $success_text_teams = '';
	var $success_text_results = '';
    
    /**
     * sportsmanagementModeljsmopenligadb::getMatchLink()
     * 
     * @param mixed $projectid
     * @return
     */
    function getMatchLink($projectid)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$post   = Factory::getApplication()->input->post->getArray(array());

		if ($app->isClient('administrator'))
		{
			$view = Factory::getApplication()->input->getVar('view');
		}
		else
		{
			$view = 'jsmopenligadb';
		}

		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
try{
		$query->select('ev.fieldvalue');
		$query->from('#__sportsmanagement_user_extra_fields_values as ev ');
		$query->join('INNER', '#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');
		$query->where('ev.jl_id = ' . $projectid);
		$query->where('ef.name LIKE ' . $db->Quote('' . $view . ''));
		$query->where('ef.template_backend LIKE ' . $db->Quote('' . 'project' . ''));
		$query->where('ef.field_type LIKE ' . $db->Quote('' . 'link' . ''));
		$db->setQuery($query);
		$derlink = $db->loadResult();
        }
catch (Exception $e)
{
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>' . print_r($query->dump(),true) .'</pre>' ), Log::ERROR, 'jsmerror');	
}

		return $derlink;
	}
    
    
    /**
     * sportsmanagementModeljsmopenligadb::getdata()
     * 
     * @param mixed $projectlink
     * @return void
     */
    function getdata($projectlink)
	{
$http = HttpFactory::getHttp(null, array('curl', 'stream'));
	   try{ 
$result  = $http->get($projectlink );
$matches = json_decode($result->body, true);
Log::add(Text::_('Datei erfolgreich eingelesen'), Log::NOTICE, 'jsmerror');		   
 }
catch (Exception $e)
{
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
}

foreach ( $matches as $key => $value )
{
$newobject = new stdClass;  
$newobject->id = $value['Team1']['TeamId'];
$newobject->name = $value['Team1']['TeamName'];  
$teams[$value['Team1']['TeamId']] = $newobject;  
$newobject = new stdClass;  
$newobject->id = $value['Team2']['TeamId'];
$newobject->name = $value['Team2']['TeamName'];    
$teams[$value['Team2']['TeamId']] = $newobject;    
$newobject = new stdClass;  
$newobject->id = $value['Location']['LocationID'];
$newobject->name = $value['Location']['LocationStadium']; 
$newobject->location = $value['Location']['LocationCity'];  
$playgrounds[$value['Location']['LocationID']] = $newobject;  
  
  
$newobject = new stdClass;   
$newobject->id = $value['MatchID'];  
$newobject->league_id = $value['LeagueId'];   
$newobject->round_id = $value['Group']['GroupID'];  
$newobject->round_name = $value['Group']['GroupName'];  
$newobject->round_code = $value['Group']['GroupOrderID'];    
  
$newobject->home_id = $value['Team1']['TeamId'];   
$newobject->away_id = $value['Team2']['TeamId'];   
  
$newobject->team1_result  = $value['MatchResults']['0']['PointsTeam1'];  
$newobject->team2_result  = $value['MatchResults']['0']['PointsTeam2'];    
  
$matchesopenliga[$value['MatchID']] = $newobject;   
  
foreach ( $value['Goals'] as $keygoal => $valuegoal )
{  
$newobject = new stdClass; 
$newobject->match_id = $value['MatchID'];  
$newobject->person_id = $valuegoal['GoalGetterID'];  
$newobject->person_name = $valuegoal['GoalGetterName'];    
$newobject->minute = $valuegoal['MatchMinute'];   
$newobject->wert = 1;     
$newobject->notice = $valuegoal['ScoreTeam1'].'-'.$valuegoal['ScoreTeam2'];   
$goals[$value['MatchID']][] = $newobject;   
  
  
} 
  
  
}

      echo 'teams<pre>'.print_r($teams,true).'</pre>';
      echo 'playgrounds<pre>'.print_r($playgrounds,true).'</pre>';
      echo 'matchesopenliga<pre>'.print_r($matchesopenliga,true).'</pre>';
      echo 'goals<pre>'.print_r($goals,true).'</pre>';

return $matches;
	}
    
    
}
    
?>
