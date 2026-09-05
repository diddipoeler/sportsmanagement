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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
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

    public function getTeamStaff()
    {
        return self::$_inproject = $this->player()->getTeamStaff(self::$projectid, self::$personid);
    }

    public function getAllEvents($sportstype = 0): array
    {
        return $this->player()->getAllEvents((int) $sportstype);
    }

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

    public function getStats(): array
    {
        return $this->statistics()->getStats();
    }

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

    public function getPlayerStatsByGame(): array
    {
        return $this->statistics()->getPlayerStatsByGame();
    }

    public function getTeamPlayers($cfg_which_database = 0): array
    {
        if ((int) $cfg_which_database === 1) {
            $this->setDatabaseSelector(1);
        }

        return self::$_teamplayers = $this->player()->getTeamPlayers(self::$projectid, self::$personid);
    }

    public function getPlayerStatsByProject($sportstype = 0): array
    {
        return $this->statistics()->getPlayerStatsByProject((int) $sportstype);
    }

    public function getCareerStats($person_id, $sports_type_id): array
    {
        return $this->statistics()->getCareerStats((int) $person_id, (int) $sports_type_id);
    }

    public function getGames(): array
    {
        return $this->matches()->getGames($this->player()->getTeamPlayers(self::$projectid, self::$personid));
    }

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

    public function getGamesEvents($show_events_as_sum = 1): array
    {
        return $this->matches()->getGamesEvents(
            $this->player()->getTeamPlayers(self::$projectid, self::$personid),
            (bool) $show_events_as_sum
        );
    }

    private function player(): PlayerModel
    {
        if ($this->playerModel === null) {
            $this->playerModel = new PlayerModel();
            $this->playerModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->playerModel;
    }

    private function statistics(): PlayerStatisticsModel
    {
        if ($this->statisticsModel === null) {
            $this->statisticsModel = new PlayerStatisticsModel();
            $this->statisticsModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->statisticsModel;
    }

    private function matches(): PlayerMatchDataModel
    {
        if ($this->matchDataModel === null) {
            $this->matchDataModel = new PlayerMatchDataModel();
            $this->matchDataModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->matchDataModel;
    }

    private static function currentDatabaseSelector(): int
    {
        if (self::$cfg_which_database === 1) {
            return 1;
        }

        return self::frontendApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
    }

    private static function frontendApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }
}
