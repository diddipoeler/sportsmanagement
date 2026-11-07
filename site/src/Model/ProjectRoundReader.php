<?php
/**
 * Joomla 5/6 project round reader.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class ProjectRoundReader
{
    /**
     * Create a project-scoped round reader and normalise its project ID.
     * @param DatabaseInterface $database Component database connection.
     * @param int $projectId Project identifier.
     */
    public function __construct(
        private DatabaseInterface $database,
        private int $projectId
    ) {
        $this->projectId = max(0, $this->projectId);
    }

    /**
     * Read project rounds in ascending or descending order.
     * @param string $ordering Requested round direction.
     * @param bool $slug Whether to include round aliases.
     * @return array<int,object> Project round rows.
     */
    public function getRounds(string $ordering = 'ASC', bool $slug = true): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery();

        if ($slug) {
            $query->select("CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ') AS ' . $db->quoteName('id'));
        } else {
            $query->select($db->quoteName('r.id'));
        }

        $query->select([
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                'CASE LENGTH(' . $db->quoteName('r.name') . ') WHEN 0 THEN ' . $db->quoteName('r.roundcode') . ' ELSE ' . $db->quoteName('r.name') . ' END AS ' . $db->quoteName('name'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundListProjectId')
            ->bind(':roundListProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode') . ' ' . $direction);

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Resolve the current round identifier for a project.
     * @param object|null $project Optional project details.
     * @param bool $persist Whether automatic round changes may be saved.
     * @return int Current round ID or zero.
     */
    public function getCurrentRoundId(?object $project = null, bool $persist = true): int
    {
        $round = $this->getCurrentRound($project, $persist);
        return $round ? (int) $round->id : 0;
    }

    /**
     * Determine the current round from project timing rules.
     * @param object|null $project Optional project details.
     * @param bool $persist Whether automatic selection may be persisted.
     * @return object|null Current round record.
     */
    public function getCurrentRound(?object $project = null, bool $persist = true): ?object
    {
        if ($this->projectId <= 0) {
            return null;
        }

        $project ??= $this->loadProject();
        if (!$project) {
            return null;
        }

        $projectId = (int) ($project->id ?? 0);
        if ($projectId <= 0 || $projectId !== $this->projectId) {
            return null;
        }

        $currentRoundId = max(0, (int) ($project->current_round ?? 0));
        $autoMode = max(0, (int) ($project->current_round_auto ?? 0));
        $autoTime = (int) ($project->auto_time ?? 0);
        if ($autoTime <= 0) {
            $autoTime = 7200;
        }

        $round = $this->findAutomaticRound($autoMode, $autoTime, $currentRoundId);
        if (!$round && $currentRoundId > 0) {
            $round = $this->loadRound($currentRoundId);
        }
        if (!$round) {
            $round = $this->loadFallbackRound($autoMode);
        }

        if ($round && $persist && $currentRoundId !== (int) $round->id) {
            $update = (object) [
                'id' => $projectId,
                'current_round' => (int) $round->id,
            ];
            $this->database->updateObject('#__sportsmanagement_project', $update, 'id');
        }

        return $round ?: null;
    }

    /**
     * Find the round selected by automatic scheduling.
     * @param int $autoMode Automatic mode selector.
     * @param int $autoTime Schedule time offset in seconds.
     * @param int $currentRoundId Previously selected round.
     * @return object|null Matching round.
     */
    private function findAutomaticRound(int $autoMode, int $autoTime, int $currentRoundId): ?object
    {
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ') AS ' . $db->quoteName('round_slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :autoRoundProjectId')
            ->bind(':autoRoundProjectId', $projectId, ParameterType::INTEGER);

        $today = gmdate('Y-m-d');

        switch ($autoMode) {
            case 0:
                if ($currentRoundId <= 0) {
                    return null;
                }
                $query->where($db->quoteName('r.id') . ' = :currentRoundId')
                    ->bind(':currentRoundId', $currentRoundId, ParameterType::INTEGER);
                break;

            case 1:
                $query->where('(r.round_date_first - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($today) . ')')
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;

            case 2:
                $query->where('(r.round_date_last - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($today) . ')')
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;

            case 3:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where('(m.match_date - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($today) . ')')
                    ->order($db->quoteName('m.match_date') . ' DESC');
                break;

            case 4:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where('(m.match_date + INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($today) . ')')
                    ->order($db->quoteName('m.match_date') . ' ASC');
                break;

            default:
                return null;
        }

        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    /**
     * Load a round belonging to the selected project.
     * @param int $roundId Round identifier.
     * @return object|null Selected round or null.
     */
    private function loadRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            return null;
        }

        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ') AS ' . $db->quoteName('round_slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundProjectId')
            ->where($db->quoteName('r.id') . ' = :roundId')
            ->bind(':roundProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':roundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    /**
     * Select a fallback round when no automatic round matches.
     * @param int $autoMode Automatic selection mode.
     * @return object|null Fallback round.
     */
    private function loadFallbackRound(int $autoMode): ?object
    {
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ') AS ' . $db->quoteName('round_slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :fallbackProjectId')
            ->bind(':fallbackProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode') . (in_array($autoMode, [0, 2], true) ? ' DESC' : ' ASC'));
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    /**
     * Read the current project's round schedule configuration.
     * @return object|null Project record.
     */
    private function loadProject(): ?object
    {
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('current_round'),
                $db->quoteName('current_round_auto'),
                $db->quoteName('auto_time'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }
}
