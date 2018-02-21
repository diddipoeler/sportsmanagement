<?php

/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
 * @version 1.0.26
 * @file		components/sportsmanagement/views/ranking/view.html.php
 * @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license This file is part of Joomla Sports Management.
 *
 * Joomla Sports Management is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Joomla Sports Management is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von Joomla Sports Management.
 *
 * Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewRanking extends sportsmanagementView {

    /**
     * sportsmanagementViewRanking::init()
     * 
     * @return void
     */
    function init() {

        $this->document->addScript(JUri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');

        sportsmanagementModelProject::setProjectID($this->jinput->getInt('p', 0), sportsmanagementModelProject::$cfg_which_database);
        $mdlDivisions = JModelLegacy::getInstance("Divisions", "sportsmanagementModel");
        $mdlProjectteams = JModelLegacy::getInstance("Projectteams", "sportsmanagementModel");
        $mdlTeams = JModelLegacy::getInstance("Teams", "sportsmanagementModel");
        $model = $this->getModel();
        $this->paramconfig = sportsmanagementModelRanking::$paramconfig;
        $this->paramconfig['p'] = $this->project->slug;

        $rounds = sportsmanagementHelper::getRoundsOptions($this->project->id, 'ASC', true, NULL, sportsmanagementModelProject::$cfg_which_database);

        sportsmanagementModelProject::setProjectId($this->project->id, sportsmanagementModelProject::$cfg_which_database);

        $this->projectinfo = $this->project->projectinfo;
        $extended = sportsmanagementHelper::getExtended($this->project->extended, 'project');
        $this->extended = $extended;

        $this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
        $this->tableconfig = $this->config;

        /**
         * sollen die vereinskürzel ersetzt und/oder angezeigt werden ?
         */
        if ($this->config['show_club_short_names'] || $this->config['show_replace_club_short_names']) {
            $mdlClubnames = JModelLegacy::getInstance("clubnames", "sportsmanagementModel");
        }

        if (($this->overallconfig['show_project_rss_feed']) == 1) {
            $mod_name = "mod_jw_srfr";
            $rssfeeditems = '';
            $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED');
            if ($rssfeedlink) {
                $this->rssfeeditems = $model->getRssFeeds($rssfeedlink, $this->overallconfig['rssitems']);
            } else {
                $this->rssfeeditems = $rssfeeditems;
            }
        }

        if ($this->config['show_half_of_season']) {
            if ($this->config['show_table_4']) {
                sportsmanagementModelRanking::$part = 1;
                sportsmanagementModelRanking::$from = 0;
                sportsmanagementModelRanking::$to = 0;
                sportsmanagementModelRanking::computeRanking(sportsmanagementModelProject::$cfg_which_database);
                $this->firstRank = sportsmanagementModelRanking::$currentRanking;
            }

            if ($this->config['show_table_5']) {
                sportsmanagementModelRanking::$part = 2;
                sportsmanagementModelRanking::$from = 0;
                sportsmanagementModelRanking::$to = 0;
                sportsmanagementModelRanking::computeRanking(sportsmanagementModelProject::$cfg_which_database);
                $this->secondRank = sportsmanagementModelRanking::$currentRanking;
            }
            sportsmanagementModelRanking::$part = 0;
        }

        sportsmanagementModelRanking::computeRanking(sportsmanagementModelProject::$cfg_which_database);

        $this->round = sportsmanagementModelRanking::$round;
        $this->part = sportsmanagementModelRanking::$part;
        $this->rounds = $rounds;
        $this->divisions = sportsmanagementModelProject::getDivisions(0, sportsmanagementModelProject::$cfg_which_database);
        $this->type = sportsmanagementModelRanking::$type;
        $this->from = sportsmanagementModelProject::$_round_from;
        $this->to = sportsmanagementModelProject::$_round_to;
        $this->divLevel = sportsmanagementModelRanking::$divLevel;

        if ($this->config['show_table_1']) {
            $this->currentRanking = sportsmanagementModelRanking::$currentRanking;
        }

        $this->previousRanking = sportsmanagementModelRanking::$previousRanking;

        if ($this->config['show_table_2']) {
            $this->homeRank = sportsmanagementModelRanking::$homeRank;
        }

        if ($this->config['show_table_3']) {
            $this->awayRank = sportsmanagementModelRanking::$awayRank;
        }

        /**
         * wenn keine reiter ausgewählt wurden, dann nur die standardtabelle übergeben 
         */
        if (!$this->config['show_table_1'] ||
                !$this->config['show_table_2'] ||
                !$this->config['show_table_3'] ||
                !$this->config['show_table_4'] ||
                !$this->config['show_table_5']) {
            $this->currentRanking = sportsmanagementModelRanking::$currentRanking;
        }

        $this->current_round = sportsmanagementModelProject::getCurrentRound(__METHOD__ . ' ' . $this->jinput->getVar("view"), sportsmanagementModelProject::$cfg_which_database);

        // mannschaften holen
        $this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', sportsmanagementModelProject::$cfg_which_database, __METHOD__);

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'divisions <pre>' . print_r($this->divisions, true) . '</pre>';
            $my_text .= 'currentRanking <pre>' . print_r($this->currentRanking, true) . '</pre>';
        }

        $no_ranking_reason = '';
        $ranking_reason = array();
        if ($this->config['show_notes']) {
            foreach ($this->teams as $teams) {
                if ($teams->start_points) {
                    if ($teams->start_points < 0) {
                        $color = "red";
                    } else {
                        $color = "green";
                    }
                    $ranking_reason[$teams->name] = '<font color="' . $color . '">' . $teams->name . ': ' . $teams->start_points . ' Punkte Grund: ' . $teams->reason . '</font>';
                }
            }
        }

        if (sizeof($ranking_reason) > 0) {
            $this->ranking_notes = implode(", ", $ranking_reason);
        } else {
            $this->ranking_notes = $no_ranking_reason;
        }

        $this->previousgames = sportsmanagementModelRanking::getPreviousGames(sportsmanagementModelProject::$cfg_which_database);
        $frommatchday = array();
        $tomatchday = array();
        $lists = array();

        $frommatchday[] = JHTML :: _('select.option', '0', JText :: _('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'));
        $frommatchday = array_merge($frommatchday, $rounds);
        $lists['frommatchday'] = $frommatchday;
        $tomatchday[] = JHTML :: _('select.option', '0', JText :: _('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'));
        $tomatchday = array_merge($tomatchday, $rounds);
        $lists['tomatchday'] = $tomatchday;

        $opp_arr = array();
        $opp_arr[] = JHTML :: _('select.option', "0", JText :: _('COM_SPORTSMANAGEMENT_RANKING_FULL_RANKING'));
        $opp_arr[] = JHTML :: _('select.option', "1", JText :: _('COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING'));
        $opp_arr[] = JHTML :: _('select.option', "2", JText :: _('COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING'));

        $lists['type'] = $opp_arr;
        $this->lists = $lists;

        if (!isset($this->config['colors'])) {
            $this->config['colors'] = "";
        }

        $this->colors = sportsmanagementModelProject::getColors($this->config['colors'], sportsmanagementModelProject::$cfg_which_database);
        /**
         * wir holen uns alle mannschaften die dem projekt zugeordnet wurden
         */
        $this->allteams = $mdlProjectteams->getAllProjectTeams($this->project->id, 0, NULL, sportsmanagementModelProject::$cfg_which_database);

        /**
         * möchte der anwender die vereinskürzel ausgeschrieben sehen ?
         */
        if ($this->config['show_replace_club_short_names']) {
            /**
             * als erstes holen wir uns die vereinskürzel des landes im projekt
             */
            $short_names = $mdlClubnames->getClubNames($this->project->country);
        }

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text .= 'overallconfig <pre>' . print_r($this->overallconfig, true) . '</pre>';
            $my_text .= 'config <pre>' . print_r($this->config, true) . '</pre>';
            $my_text .= 'previousRanking <pre>' . print_r($this->previousRanking, true) . '</pre>';
            if (isset($this->homeRank)) {
                $my_text .= 'homeRank <pre>' . print_r($this->homeRank, true) . '</pre>';
            }
            if (isset($this->awayRank)) {
                $my_text .= 'awayRank <pre>' . print_r($this->awayRank, true) . '</pre>';
            }
            $my_text .= 'teams <pre>' . print_r($this->teams, true) . '</pre>';
            $my_text .= 'allteams <pre>' . print_r($this->allteams, true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
        }

        if ($this->config['show_ranking_maps']) {
            $this->geo = new JSMsimpleGMapGeocoder();
            $this->geo->genkml3($this->project->id, $this->allteams);

            foreach ($this->allteams as $row) {
                $address_parts = array();
                if (!empty($row->club_address)) {
                    $address_parts[] = $row->club_address;
                }
                if (!empty($row->club_state)) {
                    $address_parts[] = $row->club_state;
                }
                if (!empty($row->club_location)) {
                    if (!empty($row->club_zipcode)) {
                        $address_parts[] = $row->club_zipcode . ' ' . $row->club_location;
                    } else {
                        $address_parts[] = $row->club_location;
                    }
                }
                if (!empty($row->club_country)) {
                    $address_parts[] = JSMCountries::getShortCountryName($row->club_country);
                }
                $row->address_string = implode(', ', $address_parts);
            }
        }

        unset($frommatchday);
        unset($tomatchday);
        unset($lists);

        // Set page title
        $pageTitle = JText::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');
        if (isset($this->project->name)) {
            $pageTitle .= ': ' . $this->project->name;
        }
        $this->document->setTitle($pageTitle);
        /*
          $view = $jinput->getVar( "view") ;
          $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
          $document->addCustomTag($stylelink);
          $document->addCustomTag('<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">');
         */
        $this->headertitle = JText::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }

}

?>
