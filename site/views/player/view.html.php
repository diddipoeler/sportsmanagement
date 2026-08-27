<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewPlayer
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPlayer extends sportsmanagementView
{
    /**
     * sportsmanagementViewPlayer::init()
     *
     * @return void
     */
    public function init()
    {
        $this->warnings = [];
        $this->tips = [];
        $this->notes = [];

        $model = $this->model;
        $model::$projectid = $this->jinput->getInt('p', 0);
        $model::$personid = $this->jinput->getInt('pid', 0);
        $model::$teamplayerid = $this->jinput->getInt('pt', 0);

        sportsmanagementModelProject::setProjectID($model::$projectid, $model::$cfg_which_database);

        $person = sportsmanagementModelPerson::getPerson(0, $model::$cfg_which_database, 1);
        $nickname = (string) ($person->nickname ?? '');

        if ($nickname !== '') {
            $nickname = "'" . $nickname . "'";
        }

        $this->isContactDataVisible = sportsmanagementModelPerson::isContactDataVisible(
            $this->config['show_contact_team_member_only'] ?? 0
        );
        $this->person = $person;
        $this->nickname = $nickname;
        $this->teamPlayers = (array) $model->getTeamPlayers($model::$cfg_which_database);

        if (!isset($this->config['show_players_layout'])) {
            $this->config['show_players_layout'] = 'no_tabs';
        }

        if (!isset($this->overallconfig['person_events'])) {
            $personEvents = sportsmanagementModelEventtypes::getEvents($this->project->sports_type_id ?? 0);

            if (is_iterable($personEvents)) {
                $this->overallconfig['person_events'] = [];
                foreach ($personEvents as $event) {
                    $this->overallconfig['person_events'][] = $event->value;
                }
            }
        }

        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields(
            'frontend',
            $model::$cfg_which_database
        );

        if ($this->checkextrafields && $person) {
            $this->extrafields = sportsmanagementHelper::getUserExtraFields(
                $person->id,
                'frontend',
                $model::$cfg_which_database
            );
        }

        /** Select the teamplayer that is currently published. */
        $teamPlayer = null;
        $currentProjectTeamId = 0;

        foreach ($this->teamPlayers as $candidate) {
            if ($teamPlayer === null) {
                $teamPlayer = $candidate;
            }

            if ((int) ($candidate->published ?? 0) === 1) {
                $currentProjectTeamId = (int) ($candidate->projectteam_id ?? 0);
                $teamPlayer = $candidate;
                break;
            }
        }

        if ($currentProjectTeamId > 0 && isset($this->teamPlayers[$currentProjectTeamId])) {
            $teamPlayer = $this->teamPlayers[$currentProjectTeamId];
        }

        $sportstype = !empty($this->config['show_plcareer_sportstype'])
            ? sportsmanagementModelProject::getSportsType($model::$cfg_which_database)
            : 0;

        $this->teamPlayer = $teamPlayer;
        $this->historyPlayer = $model->getPlayerHistory(
            $sportstype,
            $this->config['historyorder'] ?? 'ASC',
            1,
            $model::$cfg_which_database
        );
        $this->historyPlayerStaff = $model->getPlayerHistory(
            $sportstype,
            $this->config['historyorder'] ?? 'ASC',
            2,
            $model::$cfg_which_database
        );
        $this->AllEvents = $model->getAllEvents($sportstype);
        $this->showediticon = sportsmanagementModelPerson::getAllowed($this->config['edit_own_player'] ?? 0);
        $this->stats = sportsmanagementModelProject::getProjectStats(0, 0, $model::$cfg_which_database);

        /** Get events and stats for current project. */
        if (!empty($this->config['show_gameshistory'])) {
            $this->games = $model->getGames();
            $this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(
                0,
                'name',
                $model::$cfg_which_database
            );
            $this->gamesevents = $model->getGamesEvents($this->config['show_events_as_sum'] ?? 0);
            $this->gamesstats = $model->getPlayerStatsByGame();
        }

        /** Get events and stats for all projects where the player participated. */
        if (!empty($this->config['show_career_stats'])) {
            $this->stats = $model->getStats();
            $this->projectstats = $model->getPlayerStatsByProject($sportstype);
        }

        $this->extended = $person
            ? sportsmanagementHelper::getExtended($person->extended ?? '', 'player')
            : null;

        $parentPositions = null;
        $personPosition = null;

        if ($this->extended) {
            $parentPositions = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS');
            $personPosition = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION');
        }

        $this->person_parent_positions = $parentPositions;

        if (!$personPosition && $teamPlayer) {
            switch ($teamPlayer->position_name ?? '') {
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_DEFENDER':
                    $personPosition = 'hp2';
                    break;
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_FORWARD':
                    $personPosition = 'hp14';
                    break;
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_GOALKEEPER':
                    $personPosition = 'hp1';
                    break;
                case 'COM_SPORTSMANAGEMENT_SOCCER_P_MIDFIELDER':
                    $personPosition = 'hp7';
                    break;
            }
        }

        $this->person_position = $personPosition;
        $this->hasDescription = (string) ($teamPlayer->notes ?? '');
        $hasData = false;

        if ($this->extended && method_exists($this->extended, 'getFieldsets')) {
            foreach ($this->extended->getFieldsets() as $fieldset) {
                foreach ($this->extended->getFieldset($fieldset->name) as $field) {
                    if (!empty($field->value)) {
                        $hasData = true;
                        break 2;
                    }
                }
            }
        }

        $this->hasExtendedData = $hasData;
        $this->hasStatus = $teamPlayer && (
            (int) ($teamPlayer->injury ?? 0) > 0
            || (int) ($teamPlayer->suspension ?? 0) > 0
            || (int) ($teamPlayer->away ?? 0) > 0
        );

        $name = $person
            ? sportsmanagementHelper::formatName(
                null,
                $person->firstname ?? '',
                $person->nickname ?? '',
                $person->lastname ?? '',
                $this->config['name_format'] ?? 0
            )
            : '';

        $this->playername = $name;
        $this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', $name));

        $view = $this->jinput->getCmd('view', 'player');
        $stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $view . '.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
