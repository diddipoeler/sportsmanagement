<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_randomplayer
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * modJSMRandomplayerHelper
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMRandomplayerHelper
{

    /**
     * Method to get the list
     *
     * @access public
     * @return array
     */
    public static function getData(&$params)
    {
        $mainframe = Factory::getApplication();
        $usedp = $params->get('p');
        $usedtid = $params->get('teams', '0');
        $season_id = (int) $params->get('s', '0');
        $usedp = array_map('intval', $usedp);
        $projectstring = (is_array($usedp)) ? implode(",", $usedp)  : (int) $usedp;
        $teamstring = (is_array($usedtid)) ? implode(",", $usedtid) : (int) $usedtid;

        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
      
        $query->select('tt.id');
        $query->from('#__sportsmanagement_project_team tt ');
        $query->where('tt.project_id > 0');
                  
        if($projectstring != "" && $projectstring > 0 ) {
            $query->where('tt.project_id IN ('.$projectstring.')');
        }
        if($teamstring != "" && $teamstring > 0 ) {
            $query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.id = tt.team_id ');
            $query->where('st1.team_id IN ('.$teamstring.')');
        }

        $query->order('rand()');
        $query->setLimit('1');
      
        $db->setQuery($query);
        $projectteamid = $db->loadResult();
     
        if ($params['debug_modus'] ) {      

        }

     
        $query = $db->getQuery(true);
      
              
        $query->clear();

        $query->select('stp1.person_id');
        $query->select('pt.project_id');
        $query->select('stp1.id');
        $query->from('#__sportsmanagement_season_team_person_id as stp1 ');
        $query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.team_id = stp1.team_id ');
        $query->join('INNER', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
        $query->where('pt.id = ' . $projectteamid);
      
        $query->where('stp1.season_id = ' . $season_id);
        $query->where('st1.season_id = ' . $season_id);
      
        $query->order('rand()');
      
        $db->setQuery($query, 0, 1);
        $res = $db->loadRow();

        if (!$res ) {
            Log::add('Keine Spieler vorhanden');    
        }
        else
        {
        }

        if ($params['debug_modus'] ) {      

        }
      

        if (!class_exists('sportsmanagementModelPlayer')) {
            JLoader::import('components.com_sportsmanagement.models.player', JPATH_SITE);
        }
        if (!class_exists('sportsmanagementModelPerson')) {
            JLoader::import('components.com_sportsmanagement.models.person', JPATH_SITE);
        }
      
        sportsmanagementModelProject::$projectid = $res[1];
        sportsmanagementModelPerson::$projectid = $res[1];
        sportsmanagementModelPerson::$personid = $res[0];
        sportsmanagementModelPlayer::$projectid = $res[1];
        sportsmanagementModelPlayer::$personid = $res[0];

        $person     = sportsmanagementModelPerson::getPerson($res[0]);
        $project    = sportsmanagementModelProject::getProject();
        $info        = sportsmanagementModelPlayer::getTeamPlayer($res[1], $res[0], $res[2]);
        $infoteam    = sportsmanagementModelProject::getTeaminfo($projectteamid);
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

        $playerresult = array('project' => $project,
         'player' => $person,
         'inprojectinfo'    => is_array($info) && count($info) ? $info[0] : $info,
         'infoteam' => $infoteam);
        if ($params['debug_modus'] ) {      

        }
        return $playerresult;
    
        //      $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
    
    }
}
