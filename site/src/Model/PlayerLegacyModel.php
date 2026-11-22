<?php
/**
 * Joomla 5/6 compatibility facade for the historic player model API.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Joomla 5/6 compatibility facade for the historic sportsmanagementModelPlayer
 * API. All database work is delegated to native site models.
 */
final class PlayerLegacyModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $personid = 0;
    public static int $teamplayerid = 0;
    public static mixed $_playerhistory = null;
    public static mixed $_playerhistorystaff = null;
    public static mixed $_teamplayers = null;
    public static mixed $_inproject = null;
    public static int $cfg_which_database = 0;

    private int $databaseSelector = 0;
    private ?PlayerModel $playerModel = null;
    private ?PlayerStatisticsModel $statisticsModel = null;
    private ?PlayerMatchDataModel $matchDataModel = null;
    private ?PlayerTimeModel $timeModel = null;

    /**
     * Initialise the historical facade from the active Joomla site request.
     * @param array $config Model settings.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = max(0, $input->getInt('p', 0));
        self::$personid = max(0, $input->getInt('pid', 0));
        self::$teamplayerid = max(0, $input->getInt('pt', 0));
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->databaseSelector = self::$cfg_which_database;

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
            \sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
        }
    }

    /**
     * Select a SportsManagement database and propagate it to cached models.
     * @param int $selector Database selector zero or one.
     * @return void
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelector = $selector === 1 ? 1 : 0;
        self::$cfg_which_database = $this->databaseSelector;
        parent::setDatabaseSelector($this->databaseSelector);

        foreach ([$this->playerModel, $this->statisticsModel, $this->matchDataModel, $this->timeModel] as $model) {
            if ($model !== null) {
                $model->setDatabaseSelector($this->databaseSelector);
            }
        }
    }

    /**
     * Calculate a player's playing time using the native PlayerTimeModel.
     * @param mixed $player_id Team-player ID.
     * @param mixed $game_regular_time Normal match minutes.
     * @param mixed $match_id Optional match ID.
     * @param mixed $cards Optional cards array.
     * @param mixed $project_id Project ID.
     * @param mixed $add_time Additional minutes.
     * @return int|float Time played.
     */
    public static function getTimePlayed(
        $player_id,
        $game_regular_time,
        $match_id = null,
        $cards = null,
        $project_id = 0,
        $add_time = 0
    ): int|float {
        $model = new PlayerTimeModel();
        $model->setDatabaseSelector(self::currentDatabaseSelector());

        return $model->getTimePlayed(
            (int) $player_id,
            (int) $game_regular_time,
            $match_id === null ? null : (int) $match_id,
            is_array($cards) ? $cards : null,
            (int) $project_id,
            (int) $add_time
        );
    }

    /**
     * Read and cache staff assignments for the current project and person.
     * @return mixed Staff data from the native player reader.
     */
    public function getTeamStaff()
    {
        return self::$_inproject = $this->player()->getTeamStaff(self::$projectid, self::$personid);
    }

    /**
     * Fetch event types for the selected sport.
     * @param mixed $sportstype Sport type.
     * @return array Event records.
     */
    public function getAllEvents($sportstype = 0): array
    {
        return $this->player()->getAllEvents((int) $sportstype);
    }

    /**
     * Retrieve and cache the person's playing or staff history.
     * @param mixed $sportstype Sport type.
     * @param mixed $order Sort direction.
     * @param mixed $persontype Person role.
     * @param mixed $cfg_which_database Optional database selector.
     * @return array Historical assignments.
     */
    public function getPlayerHistory($sportstype = 0, $order = 'ASC', $persontype = 1, $cfg_which_database = 0): array
    {
        if ((int) $cfg_which_database === 1) {
            $this->setDatabaseSelector(1);
        }

        $history = $this->player()->getPlayerHistory((int) $sportstype, (string) $order, (int) $persontype);

        if ((int) $persontype === 2) {
            self::$_playerhistorystaff = $history;
        } else {
            self::$_playerhistory = $history;
        }

        return $history;
    }

    /**
     * Retrieve summary statistics via the native player statistics model.
     * @return array Player statistics.
     */
    public function getStats(): array
    {
        return $this->statistics()->getStats();
    }

    /**
     * Resolve a project team-player relation through the native player model.
     * @param mixed $projectid Project ID.
     * @param mixed $personid Person ID.
     * @param mixed $teamplayerid Team-player relation ID.
     * @return array Project team-player records.
     */
    public static function getTeamPlayer($projectid = 0, $personid = 0, $teamplayerid = 0): array
    {
        if ((int) $projectid > 0) {
            self::$projectid = (int) $projectid;
        }
        if ((int) $personid > 0) {
            self::$personid = (int) $personid;
        }
        if ((int) $teamplayerid > 0) {
            self::$teamplayerid = (int) $teamplayerid;
        }

        $model = new PlayerModel();
        $model->setDatabaseSelector(self::currentDatabaseSelector());
        $result = $model->getTeamPlayer(self::$projectid, self::$personid, self::$teamplayerid);
        self::$_inproject = $result;

        return $result;
    }

    /**
     * Retrieve game-by-game player statistics.
     * @return array Per-game statistics.
     */
    public function getPlayerStatsByGame(): array
    {
        return $this->statistics()->getPlayerStatsByGame();
    }

    /**
     * Retrieve and cache project-team player assignments.
     * @param mixed $cfg_which_database Optional database choice.
     * @return array Team-player relations.
     */
    public function getTeamPlayers($cfg_which_database = 0): array
    {
        if ((int) $cfg_which_database === 1) {
            $this->setDatabaseSelector(1);
        }

        return self::$_teamplayers = $this->player()->getTeamPlayers(self::$projectid, self::$personid);
    }

    /**
     * Retrieve the selected person's project statistics.
     * @param mixed $sportstype Sport type filter.
     * @return array Per-project statistics.
     */
    public function getPlayerStatsByProject($sportstype = 0): array
    {
        return $this->statistics()->getPlayerStatsByProject((int) $sportstype);
    }

    /**
     * Retrieve aggregated career statistics for a player and sport.
     * @param mixed $person_id Person ID.
     * @param mixed $sports_type_id Sport type ID.
     * @return array Career statistics.
     */
    public function getCareerStats($person_id, $sports_type_id): array
    {
        return $this->statistics()->getCareerStats((int) $person_id, (int) $sports_type_id);
    }

    /**
     * Retrieve matches linked to the selected player's teams.
     * @return array Player match history.
     */
    public function getGames(): array
    {
        return $this->matches()->getGames($this->player()->getTeamPlayers(self::$projectid, self::$personid));
    }

    /**
     * Calculate the player's match participation and substitution statistics.
     * @param mixed $project_id Project ID.
     * @param mixed $projectteam_id Project-team ID.
     * @param mixed $teamplayer_id Team-player ID.
     * @param mixed $game_regular_time Normal match duration.
     * @param mixed $match_id Match ID.
     * @param mixed $cfg_which_database Database selector.
     * @param mixed $team_id Team ID.
     * @param mixed $person_id Person ID.
     * @return object Substitution and playing-time data.
     */
    public static function getInOutStats(
        $project_id = 0,
        $projectteam_id = 0,
        $teamplayer_id = 0,
        $game_regular_time = 90,
        $match_id = 0,
        $cfg_which_database = 0,
        $team_id = 0,
        $person_id = 0
    ): object {
        $selector = (int) $cfg_which_database === 1 ? 1 : self::currentDatabaseSelector();
        $model = new PlayerTimeModel();
        $model->setDatabaseSelector($selector);

        return $model->getInOutStats(
            (int) $project_id,
            (int) $projectteam_id,
            (int) $teamplayer_id,
            (int) $game_regular_time,
            (int) $match_id,
            $selector,
            (int) $team_id,
            (int) $person_id
        );
    }

    /**
     * Read player match events, optionally summed by event quantity.
     * @param mixed $show_events_as_sum Whether to sum event values.
     * @return array Match-event data.
     */
    public function getGamesEvents($show_events_as_sum = 1): array
    {
        return $this->matches()->getGamesEvents(
            $this->player()->getTeamPlayers(self::$projectid, self::$personid),
            (bool) $show_events_as_sum
        );
    }

    /**
     * Create or reuse the native player reader with the selected database.
     * @return PlayerModel Native player model.
     */
    private function player(): PlayerModel
    {
        if ($this->playerModel === null) {
            $this->playerModel = new PlayerModel();
            $this->playerModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->playerModel;
    }

    /**
     * Create or reuse the native player statistics model.
     * @return PlayerStatisticsModel Statistics model.
     */
    private function statistics(): PlayerStatisticsModel
    {
        if ($this->statisticsModel === null) {
            $this->statisticsModel = new PlayerStatisticsModel();
            $this->statisticsModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->statisticsModel;
    }

    /**
     * Create or reuse the native player match-data reader.
     * @return PlayerMatchDataModel Match-data model.
     */
    private function matches(): PlayerMatchDataModel
    {
        if ($this->matchDataModel === null) {
            $this->matchDataModel = new PlayerMatchDataModel();
            $this->matchDataModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->matchDataModel;
    }

    /**
     * Read the active database selector for static compatibility methods.
     * @return int Zero or one.
     */
    private static function currentDatabaseSelector(): int
    {
        if (self::$cfg_which_database === 1) {
            return 1;
        }

        return self::frontendApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
    }

    /**
     * Resolve Joomla's site application for static compatibility calls.
     * @return CMSApplicationInterface Active frontend application.
     */
    private static function frontendApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }
}
