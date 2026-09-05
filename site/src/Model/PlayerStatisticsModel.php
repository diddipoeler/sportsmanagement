<?php
/**
 * Native Joomla 5/6 player statistics coordinator.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 player statistics coordinator.
 *
 * Player/team/history reads come from PlayerModel and statistic metadata uses
 * the native project model. Existing SMStatistic plug-ins remain responsible
 * for their sport-specific calculations.
 */
final class PlayerStatisticsModel extends SportsManagementProjectModel
{
    private int $databaseSelector = 0;
    private ?PlayerModel $playerModel = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->databaseSelector = $this->siteApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
    }

    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelector = $selector === 1 ? 1 : 0;
        parent::setDatabaseSelector($this->databaseSelector);

        if ($this->playerModel !== null) {
            $this->playerModel->setDatabaseSelector($this->databaseSelector);
        }
    }

    /**
     * Statistics configured for the current player's project positions.
     */
    public function getStats(): array
    {
        $stats = [];

        foreach ($this->player()->getTeamPlayer() as $player) {
            $positionId = (int) ($player->position_id ?? 0);
            if ($positionId <= 0) {
                continue;
            }

            foreach ($this->getProjectStats(0, $positionId) as $id => $projectStat) {
                if (!array_key_exists($id, $stats)) {
                    $stats[$id] = $projectStat;
                }
            }
        }

        return $stats;
    }

    /**
     * Per-match statistics used by the player game-history table.
     */
    public function getPlayerStatsByGame(): array
    {
        $teamPlayers = $this->player()->getTeamPlayers();
        if ($teamPlayers === []) {
            return [];
        }

        $project = $this->getProject();
        if (!$project) {
            return [];
        }

        $teamPlayerIds = [];
        $positionIds = [];

        foreach ($teamPlayers as $teamPlayer) {
            $teamPlayerId = (int) ($teamPlayer->id ?? 0);
            $positionId = (int) ($teamPlayer->position_id ?? 0);

            if ($teamPlayerId > 0) {
                $teamPlayerIds[$teamPlayerId] = $teamPlayerId;
            }
            if ($positionId > 0) {
                $positionIds[$positionId] = $positionId;
            }
        }

        if ($teamPlayerIds === [] || $positionIds === []) {
            return [];
        }

        $statistics = [];
        foreach ($positionIds as $positionId) {
            foreach ($this->getProjectStats(0, $positionId) as $id => $statistic) {
                $statistics[$id] ??= $statistic;
            }
        }

        $displayStats = [];
        foreach ($statistics as $statistic) {
            if (!isset($statistic->_showinsinglematchreports)
                || !method_exists($statistic, 'getPlayerStatsByGame')) {
                continue;
            }

            try {
                $gamesStats = $statistic->getPlayerStatsByGame(
                    array_values($teamPlayerIds),
                    (int) $project->id
                );
                $statistic->set('gamesstats', $gamesStats);
                $displayStats[] = $statistic;
            } catch (Throwable $e) {
                $this->reportDatabaseError($e);
            }
        }

        return $displayStats;
    }

    /**
     * Career statistic totals grouped by project and project team.
     */
    public function getPlayerStatsByProject(int $sportsTypeId = 0): array
    {
        $teamPlayers = $this->player()->getTeamPlayer();
        if ($teamPlayers === []) {
            return [];
        }

        $personId = (int) ($teamPlayers[0]->person_id ?? 0);
        if ($personId <= 0) {
            return [];
        }

        $statistics = $this->getCareerStats($personId, $sportsTypeId);
        $history = $this->player()->getPlayerHistory($sportsTypeId, 'ASC', 1);
        $result = [];

        foreach ($statistics as $statistic) {
            if (!method_exists($statistic, 'getPlayerStatsByProject')) {
                continue;
            }

            $statisticId = (int) ($statistic->id ?? 0);
            if ($statisticId <= 0) {
                continue;
            }

            foreach ($history as $player) {
                $projectId = (int) ($player->project_id ?? 0);
                $projectTeamId = (int) ($player->ptid ?? 0);
                if ($projectId <= 0 || $projectTeamId <= 0) {
                    continue;
                }

                try {
                    $value = $statistic->getPlayerStatsByProject(
                        (int) ($player->person_id ?? $personId),
                        $projectTeamId,
                        $projectId,
                        $sportsTypeId
                    );
                } catch (Throwable $e) {
                    $this->reportDatabaseError($e);
                    continue;
                }

                $result[$statisticId][$projectId][$projectTeamId] = $value;
                $result[$statisticId]['totals'] = ($result[$statisticId]['totals'] ?? 0) + (float) $value;
            }
        }

        return $result;
    }

    /**
     * Build the SMStatistic objects used for career calculations with the
     * selected SportsManagement database rather than Joomla's default DB.
     */
    public function getCareerStats(int $personId, int $sportsTypeId = 0): array
    {
        if ($personId <= 0 || !$this->loadStatisticBase()) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.name'),
                $db->quoteName('s.short'),
                $db->quoteName('s.class'),
                $db->quoteName('s.icon'),
                $db->quoteName('s.calculated'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('ppos.position_id', 'position_id'),
                $db->quoteName('s.params'),
                $db->quoteName('s.baseparams'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('tp.project_position_id') . ' = ' . $db->quoteName('ppos.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_statistic', 'ps') . ' ON ' . $db->quoteName('ps.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_statistic', 's') . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('s.id'))
            ->where($db->quoteName('p.id') . ' = ' . $personId)
            ->group([$db->quoteName('s.id'), $db->quoteName('ppos.id')]);

        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('pos.sports_type_id') . ' = ' . $sportsTypeId);
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        $stats = [];
        foreach ($rows as $row) {
            try {
                $statistic = \SMStatistic::getInstance((string) $row->class);
                if (!$statistic) {
                    continue;
                }
                $statistic->bind($row);
                $statistic->set('position_id', (int) $row->position_id);
                $stats[(int) $row->id] = $statistic;
            } catch (Throwable $e) {
                $this->reportDatabaseError($e);
            }
        }

        return $stats;
    }

    private function player(): PlayerModel
    {
        if ($this->playerModel === null) {
            $this->playerModel = new PlayerModel();
            $this->playerModel->setDatabaseSelector($this->databaseSelector);
        }

        return $this->playerModel;
    }

    private function loadStatisticBase(): bool
    {
        if (class_exists('SMStatistic')) {
            return true;
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/base.php';
        if (!is_file($file)) {
            return false;
        }

        require_once $file;
        return class_exists('SMStatistic');
    }

    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
