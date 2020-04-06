<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage clubplan
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
JLoader::import('components.com_sportsmanagement.models.clubinfo', JPATH_SITE);

/**
 * sportsmanagementViewClubPlan
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewClubPlan extends sportsmanagementView
{

    /**
     * sportsmanagementViewClubPlan::init()
     *
     * @return void
     */
    function init()
    {

        $this->document->addScript(Uri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js');
      
        $js = "window.addEvent('domready', function() {"."\n";
        $js .= "hideclubplandate()".";\n";
        $js .= "})"."\n";
        $this->document->addScriptDeclaration($js);
      
        $this->favteams = sportsmanagementModelProject::getFavTeams(sportsmanagementModelClubPlan::$cfg_which_database);
        $this->club = sportsmanagementModelClubInfo::getClub();
      
        $this->type = sportsmanagementModelClubPlan::$type;
        $this->teamartsel = sportsmanagementModelClubPlan::$teamartsel;
        $this->teamprojectssel = sportsmanagementModelClubPlan::$teamprojectssel;
      
        if ($this->teamprojectssel > 0 ) {
            sportsmanagementModelClubPlan::$project_id = $this->teamprojectssel;
        }
        $this->teamseasonssel = sportsmanagementModelClubPlan::$teamseasonssel;
      
        if ($this->teamseasonssel > 0 ) {
            sportsmanagementModelClubPlan::$project_id = 0;
        }
        if ($this->teamartsel != '' ) {
            sportsmanagementModelClubPlan::$project_id = 0;
        }
      
        if ($this->type == '' ) {
            $this->type = $this->config['type_matches'];
        }
        else
        {
            $this->config['type_matches'] = $this->type;
        }
      
        switch ($this->config['type_matches'])
        {
        case 0 :
        case 3 :
        case 4 : // all matches
            $this->allmatches = $this->model->getAllMatches($this->config['MatchesOrderBy'], $this->config['type_matches']);
            break;
        case 1 : // home matches
            $this->homematches = $this->model->getAllMatches($this->config['MatchesOrderBy'], $this->config['type_matches']);

            break;
        case 2 : // away matches
            $this->awaymatches = $this->model->getAllMatches($this->config['MatchesOrderBy'], $this->config['type_matches']);

            break;
        default: // home+away matches
            $this->homematches = $this->model->getAllMatches($this->confignfig['MatchesOrderBy'], $this->config['type_matches']);
            $this->awaymatches = $this->model->getAllMatches($this->config['MatchesOrderBy'], $this->config['type_matches']);

            break;
        }
      
        $this->startdate = $this->model->getStartDate();
        $this->enddate = $this->model->getEndDate();
        $this->teams = $this->model->getTeams();
      
        $this->teamart = $this->model->getTeamsArt();
        $this->teamprojects = $this->model->getTeamsProjects();
        $this->teamseasons = $this->model->getTeamsSeasons();
      
        $fromteamart[] = HTMLHelper::_('select.option', '', Text :: _('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAMART'));
        if ($this->teamart ) {
            $fromteamart = array_merge($fromteamart, $this->teamart);
        }
        $lists['fromteamart'] = $fromteamart;
      
        $fromteamprojects[] = HTMLHelper::_('select.option', '0', Text :: _('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PROJECT'));
        if ($this->teamprojects ) {
            $fromteamprojects = array_merge($fromteamprojects, $this->teamprojects);
        }
        $lists['fromteamprojects'] = $fromteamprojects;
      
        $fromteamseasons[] = HTMLHelper::_('select.option', '0', Text :: _('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASON'));
        if ($this->teamseasons ) {
            $fromteamseasons = array_merge($fromteamseasons, $this->teamseasons);
        }
        $lists['fromteamseasons'] = $fromteamseasons;

        /**
 * auswahl welche spiele
 */      
          $opp_arr = array ();
          $opp_arr[] = HTMLHelper::_('select.option', "0", Text :: _('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_ALL'));
        $opp_arr[] = HTMLHelper::_('select.option', "1", Text :: _('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_HOME'));
        $opp_arr[] = HTMLHelper::_('select.option', "2", Text :: _('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_AWAY'));

        $lists['type'] = $opp_arr;
          $this->lists = $lists;

        // Set page title
        $pageTitle=Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_TITLE');
        if (isset($this->club)) {
            $pageTitle .= ': '.$this->club->name;
        }
        $this->document->setTitle($pageTitle);
        /**
 * build feed links
 */
        $project_id = (!empty($this->project->id)) ? '&p='.$this->project->id : '';
        $club_id = (!empty($this->club->id)) ? '&cid='.$this->club->id : '';
        $rssVar = (!empty($this->club->id)) ? $club_id : $project_id;

        $feed = 'index.php?option=com_sportsmanagement&view=clubplan'.$rssVar.'&format=feed';
        $rss = array('type' => 'application/rss+xml','title' => Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_RSSFEED'));

        // add the links
        $this->document->addHeadLink(Route::_($feed.'&type=rss'), 'alternate', 'rel', $rss);
     
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_PAGE_TITLE').' '.$this->club->name;

        $this->config['table_class'] = isset($this->config['table_class']) ? $this->config['table_class'] : 'table';
      
    }

}
?>
