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
 * @subpackage mod_sportsmanagement_eventsranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
 * modSMEventsrankingHelper
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class modSMEventsrankingHelper
{
  
    /**
     * modSMEventsrankingHelper::getData()
     *
     * @param  mixed $params
     * @return
     */
    public static function getData(&$params)
    {
    
          $app = Factory::getApplication();

        if (!class_exists('sportsmanagementModelEventsRanking')) {
            JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);
            JLoader::import('components.com_sportsmanagement.models.eventsranking', JPATH_SITE);
        }
      
        $usedp = $params->get('p');
        $projectstring = (is_array($usedp)) ? implode(",", array_map('intval', $usedp))  : (int) $usedp;
      
        $usedteam = $params->get('tid');
        $teamstring = (is_array($usedteam)) ? implode(",", array_map('intval', $usedteam))  : (int) $usedteam ;
      
        sportsmanagementModelProject::$cfg_which_database = $params->get('cfg_which_database');
        sportsmanagementModelProject::setProjectId($projectstring, $params->get('cfg_which_database'));
      
        $project = sportsmanagementModelProject::getProject($params->get('cfg_which_database'), __METHOD__);
      
        sportsmanagementModelEventsRanking::$cfg_which_database = $params->get('cfg_which_database');
      
        sportsmanagementModelEventsRanking::$projectid = $projectstring;
        sportsmanagementModelEventsRanking::$divisionid = 0;
        sportsmanagementModelEventsRanking::$matchid = 0;
        sportsmanagementModelEventsRanking::$teamid = $teamstring;
        sportsmanagementModelEventsRanking::$eventid = $params->get('evid');
        sportsmanagementModelEventsRanking::$limit = $params->get('limit');
        sportsmanagementModelEventsRanking::$limitstart = 0;      
              
        $eventtypes = sportsmanagementModelEventsRanking::getEventTypes();
        if ($project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART' ) {
            $events = sportsmanagementModelEventsRanking::_getEventsRanking($params->get('evid'), $params->get('ranking_order'), 20, 0, true);
        }
        else
        {
            $events = sportsmanagementModelEventsRanking::_getEventsRanking($params->get('evid'), $params->get('ranking_order'), 20, 0, false);
        }
        $teams = sportsmanagementModelProject::getTeamsIndexedById();

        return array('project' => $project, 'ranking' => $events, 'eventtypes' => $eventtypes, 'teams' => $teams);
    }

    /**
     * get id from the module configuration parameters
     * (the parameter can either be the id by itself or a complete slug).
     *
     * @param  object configuration parameters for the module
     * @param  string name of the configuration parameter
     * @return id string for the requested parameter (e.g. project id or statistics id)
     */
    function getId($params, $paramName)
    {
        $id = $params->get($paramName);
        preg_match('/(?P<id>\d+):.*/', $id, $matches);
        if (array_key_exists('id', $matches)) {
            $id = $matches['id'];
        }
        return $id;
    }

  
    /**
     * get img for team
     *
     * @param  object ranking row
     * @param  int type = 1 for club small logo, 2 for country
     * @return html string
     */
    public static function getLogo($item, $type = 1)
    {
        if ($type == 1) // club small logo
        {
            if (!empty($item->logo_big)) {
                return HTMLHelper::_('image', $item->logo_big, $item->short_name, array('width' => '20', 'class' => 'teamlogo'));
            }
        }      
        else if ($type == 2 && !empty($item->country)) {
            return JSMCountries::getCountryFlag($item->country, 'class="teamcountry"');
        }
      
        return '';
    }

    /**
     * modSMEventsrankingHelper::getTeamLink()
     *
     * @param  mixed $team
     * @param  mixed $params
     * @param  mixed $project
     * @return
     */
    public static function getTeamLink($team, $params, $project)
    {

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $project->slug;
             
        switch ($params->get('teamlink'))
        {
        case 'teaminfo':
            $routeparameter['tid'] = $team->team_slug;
            $routeparameter['ptid'] = 0;
            return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);;
        case 'roster':
            $routeparameter['tid'] = $team->team_slug;
               $routeparameter['ptid'] = 0;
               $routeparameter['division'] = 0;
            return sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
        case 'teamplan':
            $routeparameter['tid'] = $team->team_slug;
               $routeparameter['division'] = 0;
               $routeparameter['mode'] = 0;
               $routeparameter['ptid'] = 0;
            return sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);;
        case 'clubinfo':
          
            return sportsmanagementHelperRoute::getClubInfoRoute($project->slug, $team->club_slug);              
        }
    }
  
    /**
     * modSMEventsrankingHelper::printName()
     *
     * @param  mixed $item
     * @param  mixed $team
     * @param  mixed $params
     * @param  mixed $project
     * @return void
     */
    public static function printName($item, $team, $params, $project)
    {
          $name = sportsmanagementHelper::formatName(
              null, $item->fname,
              $item->nname,
              $item->lname,
              $params->get("name_format")
          );
                                                  
        if ($params->get('show_player_link')) {      
                  
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
            $routeparameter['s'] = $params->get('s');
            $routeparameter['p'] = $project->slug;
            $routeparameter['tid'] = $item->team_slug;
            $routeparameter['pid'] = $item->person_slug;                  
                                  
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

            echo HTMLHelper::link($link, $name);
                  
        }
        else
          {
            echo $name;
        }              

    }
  
  
    /**
         * modSMEventsrankingHelper::getEventIcon()
         *
         * @param  mixed $event
         * @return
         */
    public static function getEventIcon($event)
    {
        if ($event->icon == 'media/com_sportsmanagement/event_icons/event.gif') {
             $txt = $event->name;
        }
        else
        {
             $imgTitle = Text::_($event->name);
             $imgTitle2 = array(' title' => $imgTitle, ' alt' => $imgTitle, ' width' => 20);
             $txt = HTMLHelper::image($event->icon, $imgTitle, $imgTitle2);
        }
        return $txt;
    }
  
  
}
