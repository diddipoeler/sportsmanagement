<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'clubinfo.php' );

/**
 * sportsmanagementViewClubPlan
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
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

        $this->document->addScript ( JUri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js' );
        
        $js = "window.addEvent('domready', function() {"."\n";
        $js .= "hideclubplandate()".";\n";
        $js .= "})"."\n";
        $this->document->addScriptDeclaration( $js );
        
		$this->favteams = sportsmanagementModelProject::getFavTeams(sportsmanagementModelClubPlan::$cfg_which_database);
		$this->club = sportsmanagementModelClubInfo::getClub();
        
        $this->type = sportsmanagementModelClubPlan::$type;
        $this->teamartsel = sportsmanagementModelClubPlan::$teamartsel;
        $this->teamprojectssel = sportsmanagementModelClubPlan::$teamprojectssel;
        
        if ( $this->teamprojectssel > 0 )
        {
            sportsmanagementModelClubPlan::$project_id = $this->teamprojectssel;
        }
        $this->teamseasonssel = sportsmanagementModelClubPlan::$teamseasonssel;
        
        if ( $this->teamseasonssel > 0 )
        {
            sportsmanagementModelClubPlan::$project_id = 0;
        }
        if ( $this->teamartsel != '' )
        {
            sportsmanagementModelClubPlan::$project_id = 0;
        }
        
        
        if ( $this->type == '' )
        {
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
				$this->allmatches = $this->model->getAllMatches($this->config['MatchesOrderBy'],$this->config['type_matches']);
				break;
			case 1 : // home matches
				$this->homematches = $this->model->getAllMatches($this->config['MatchesOrderBy'],$this->config['type_matches']);

				break;
			case 2 : // away matches
				$this->awaymatches = $this->model->getAllMatches($this->config['MatchesOrderBy'],$this->config['type_matches']);

				break;
			default: // home+away matches
				$this->homematches = $this->model->getAllMatches($this->confignfig['MatchesOrderBy'],$this->config['type_matches']);
				$this->awaymatches = $this->model->getAllMatches($this->config['MatchesOrderBy'],$this->config['type_matches']);

				break;
		}
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' allmatches <br><pre>'.print_r($this->allmatches,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' homematches <br><pre>'.print_r($this->homematches,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' awaymatches <br><pre>'.print_r($this->awaymatches,true).'</pre>'),'');
        
        
		$this->startdate = $this->model->getStartDate();
		$this->enddate = $this->model->getEndDate();
		$this->teams = $this->model->getTeams();
        
        $this->teamart = $this->model->getTeamsArt();
        $this->teamprojects = $this->model->getTeamsProjects();
        $this->teamseasons = $this->model->getTeamsSeasons();
        
        $fromteamart[] = JHTML :: _('select.option', '', JText :: _('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAMART'));
		$fromteamart = array_merge($fromteamart, $this->teamart);
		$lists['fromteamart'] = $fromteamart;
        
        $fromteamprojects[] = JHTML :: _('select.option', '0', JText :: _('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PROJECT'));
		$fromteamprojects = array_merge($fromteamprojects, $this->teamprojects);
		$lists['fromteamprojects'] = $fromteamprojects;
        
        $fromteamseasons[] = JHTML :: _('select.option', '0', JText :: _('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASON'));
		$fromteamseasons = array_merge($fromteamseasons, $this->teamseasons);
		$lists['fromteamseasons'] = $fromteamseasons;

/**
 * auswahl welche spiele
 */        
    $opp_arr = array ();
    $opp_arr[] = JHTML :: _('select.option', "0", JText :: _('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_ALL'));
	$opp_arr[] = JHTML :: _('select.option', "1", JText :: _('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_HOME'));
	$opp_arr[] = JHTML :: _('select.option', "2", JText :: _('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_AWAY'));

	$lists['type'] = $opp_arr;
    $this->lists = $lists;

		// Set page title
		$pageTitle=JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_TITLE');
		if (isset($this->club)){
			$pageTitle .= ': '.$this->club->name;
		}
		$this->document->setTitle($pageTitle);
/**
 * build feed links
 */
		$project_id = (!empty($this->project->id)) ? '&p='.$this->project->id : '';
		$club_id = (!empty($this->club->id)) ? '&cid='.$this->club->id : '';
		$rssVar = (!empty($this->club->id)) ? $club_id : $project_id;

		//$feed='index.php?option=com_sportsmanagement&view=clubplan&cid='.$this->club->id.'&format=feed';
		$feed = 'index.php?option=com_sportsmanagement&view=clubplan'.$rssVar.'&format=feed';
		$rss = array('type' => 'application/rss+xml','title' => JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_RSSFEED'));

		// add the links
		$this->document->addHeadLink(JRoute::_($feed.'&type=rss'),'alternate','rel',$rss);
        
/**
 *         das brauchen wir nicht mehr, da wir bootsrap benutzen
 *         $view = $jinput->getVar( "view") ;
 *         $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 *         $document->addCustomTag($stylelink);
 */
        
        $this->headertitle = JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_PAGE_TITLE').' '.$this->club->name;

	}

}
?>
