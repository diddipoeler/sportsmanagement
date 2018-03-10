<?php

/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage player
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewPlayer
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPlayer extends JViewLegacy {

    /**
     * sportsmanagementViewPlayer::display()
     * 
     * @param mixed $tpl
     * @return
     */
    function display($tpl = null) {
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Reference global application object
        $app = JFactory::getApplication();
// JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $model = $this->getModel();

        $model::$projectid = $jinput->getInt('p', 0);
        $model::$personid = $jinput->getInt('pid', 0);
        $model::$teamplayerid = $jinput->getInt('pt', 0);

        sportsmanagementModelProject::setProjectID($jinput->getInt('p', 0), $model::$cfg_which_database);
        $config = sportsmanagementModelProject::getTemplateConfig($this->getName(), $model::$cfg_which_database);

        $person = sportsmanagementModelPerson::getPerson(0, $model::$cfg_which_database, 1);
        $nickname = isset($person->nickname) ? $person->nickname : "";
        if (!empty($nickname)) {
            $nickname = "'" . $nickname . "'";
        }
        $this->isContactDataVisible = sportsmanagementModelPerson::isContactDataVisible($config['show_contact_team_member_only']);
        $project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
        $this->project = $project;
        $this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
        $this->config = $config;
        $this->person = $person;
        $this->nickname = $nickname;
        $this->teamPlayers = $model->getTeamPlayers($model::$cfg_which_database);

        if (!isset($this->config['show_players_layout'])) {
            $this->config['show_players_layout'] = 'no_tabs';
        }

        if (isset($this->overallconfig['person_events'])) {
            // alles ok    
        } else {
            $person_events = sportsmanagementModelEventtypes::getEvents($project->sports_type_id);
            if (is_array($person_events) || is_object($person_events)) {
                foreach ($person_events as $events) {
                    $this->overallconfig['person_events'][] = $events->value;
                }
            }
        }

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('sportsmanagementViewPlayer teamPlayers<br><pre>'.print_r($this->teamPlayers,true).'</pre>'),'');

        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend', $model::$cfg_which_database);
//        $app->enqueueMessage(JText::_('player checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
        if ($this->checkextrafields) {
            $this->extrafields = sportsmanagementHelper::getUserExtraFields($person->id, 'frontend', $model::$cfg_which_database);
        }

        // Select the teamplayer that is currently published (in case the player played in multiple teams in the project)
        $teamPlayer = null;
        if (count($this->teamPlayers)) {
            $currentProjectTeamId = 0;
            foreach ($this->teamPlayers as $teamPlayer) {
                if ($teamPlayer->published == 1) {
                    $currentProjectTeamId = $teamPlayer->projectteam_id;
                    break;
                }
            }
            if ($currentProjectTeamId) {
                $teamPlayer = $this->teamPlayers[$currentProjectTeamId];
            }
        }
        $sportstype = $config['show_plcareer_sportstype'] ? sportsmanagementModelProject::getSportsType($model::$cfg_which_database) : 0;

        $this->teamPlayer = $teamPlayer;
        $this->historyPlayer = $model->getPlayerHistory($sportstype, 'ASC', 1, $model::$cfg_which_database);
        $this->historyPlayerStaff = $model->getPlayerHistory($sportstype, 'ASC', 2, $model::$cfg_which_database);
        $this->AllEvents = $model->getAllEvents($sportstype);
        $this->showediticon = sportsmanagementModelPerson::getAllowed($config['edit_own_player']);
        $this->stats = sportsmanagementModelProject::getProjectStats(0, 0, $model::$cfg_which_database);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' stats<br><pre>'.print_r($this->stats,true).'</pre>'),'Notice');

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'stats <pre>' . print_r($this->stats, true) . '</pre>';
            //$my_text .= 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';   
            //$my_text .= 'cards <pre>'.print_r($cards,true).'</pre>';       
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' stats<br><pre>'.print_r($this->stats,true).'</pre>'),'');
        }

        // Get events and stats for current project
        if ($config['show_gameshistory']) {
            $this->games = $model->getGames();
            $this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', $model::$cfg_which_database);
            $this->gamesevents = $model->getGamesEvents();
            $this->gamesstats = $model->getPlayerStatsByGame();
        }

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'gamesstats <pre>' . print_r($this->gamesstats, true) . '</pre>';
            //$my_text .= 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';   
            //$my_text .= 'cards <pre>'.print_r($cards,true).'</pre>';       
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' gamesstats<br><pre>'.print_r($this->gamesstats,true).'</pre>'),'');
        }

        // Get events and stats for all projects where player played in (possibly restricted to sports type of current project)
        if ($config['show_career_stats']) {
            $this->stats = $model->getStats();
            $this->projectstats = $model->getPlayerStatsByProject($sportstype);
        }

        $extended = '';
        //if ( $person )
        //{
        $extended = sportsmanagementHelper::getExtended($person->extended, 'person');
        //}
        $this->extended = $extended;
        unset($form_value);
        $form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS');

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'extended <pre>' . print_r($this->extended, true) . '</pre>';
            $my_text .= 'projectstats <pre>' . print_r($this->projectstats, true) . '</pre>';
            $my_text .= 'form_value <pre>' . print_r($form_value, true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);

//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' extended<br><pre>'.print_r($this->extended,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectstats<br><pre>'.print_r($this->projectstats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS<br><pre>'.print_r($form_value,true).'</pre>'),'');
        }

        // nebenposition vorhanden ?
        $this->assignRef('person_parent_positions', $form_value);
//        if ( $form_value )
//        {
//            $this->assignRef ('person_parent_positions', explode(",",$form_value) );
//        }

        unset($form_value);
        $form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION');

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            //$my_text = 'extended <pre>'.print_r($this->extended,true).'</pre>';
            //$my_text .= 'projectstats <pre>'.print_r($this->projectstats,true).'</pre>';   
            $my_text = 'form_value <pre>' . print_r($form_value, true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION<br><pre>'.print_r($form_value,true).'</pre>'),'');
        }

        if ($form_value) {
            //$this->assignRef ('person_position', $form_value );
        } else {
            // wenn beim spieler noch nichts gesetzt wurde dann nehmen wir die standards
            switch ($this->teamPlayer->position_name) {
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_DEFENDER':
                    $form_value = 'hp2';
                    break;
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_FORWARD':
                    $form_value = 'hp14';
                    break;
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_GOALKEEPER':
                    $form_value = 'hp1';
                    break;
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_MIDFIELDER':
                    $form_value = 'hp7';
                    break;
            }
            //$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_PERSON_NO_POSITION'),'Error');
        }
        $this->person_position = $form_value;
        $this->hasDescription = $this->teamPlayer->notes;

        foreach ($this->extended->getFieldsets() as $fieldset) {
            $hasData = false;
            $fields = $this->extended->getFieldset($fieldset->name);
            foreach ($fields as $field) {
                // TODO: backendonly was a feature of JLGExtraParams, and is not yet available.
                //       (this functionality probably has to be added later)
                $value = $field->value; // Remark: empty($field->value) does not work, using an extra local var does
                if (!empty($value)) { // && !$field->backendonly
                    $hasData = true;
                    break;
                }
            }
        }
        $this->hasExtendedData = $hasData;

        $hasStatus = false;
        if (( isset($this->teamPlayer->injury) && $this->teamPlayer->injury > 0 ) ||
                ( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension > 0 ) ||
                ( isset($this->teamPlayer->away) && $this->teamPlayer->away > 0 )) {
            $hasStatus = true;
        }
        $this->hasStatus = $hasStatus;

        if (isset($person)) {
            $name = sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]);
        }
        $this->playername = $name;
        $document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', $name));

        $view = $jinput->getVar("view");
        $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'components/' . $option . '/assets/css/' . $view . '.css' . '" type="text/css" />' . "\n";
        $document->addCustomTag($stylelink);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
        if (!isset($this->config['show_players_layout'])) {
            $this->config['show_players_layout'] = 'no_tabs';
        }

        parent::display($tpl);
    }

}

?>