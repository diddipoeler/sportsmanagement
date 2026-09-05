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

final class ProjectRoundReader
{
    public function __construct(
        private DatabaseInterface $database,
        private int $projectId
    ) {
        $this->projectId = max(0, $this->projectId);
    }

    public function getRounds(string $ordering = 'ASC', bool $slug = true): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->database;
        $query = $db->getQuery(true);

        if ($slug) {
            $query->select("CONCAT_WS(':', r.id, r.alias) AS id");
        } else {
            $query->select($db->quoteName('r.id'));
        }

        $query->select([
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                'CASE LENGTH(r.name) WHEN 0 THEN r.roundcode ELSE r.name END AS name',
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('r.roundcode') . ' ' . $direction);

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getCurrentRoundId(?object $project = null, bool $persist = true): int
    {
        $round = $this->getCurrentRound($project, $persist);
        return $round ? (int) $round->id : 0;
    }

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

    private function findAutomaticRound(int $autoMode, int $autoTime, int $currentRoundId): ?object
    {
        $db = $this->database;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId);

        $today = gmdate('Y-m-d');

        switch ($autoMode) {
            case 0:
                if ($currentRoundId <= 0) {
                    return null;
                }
                $query->where($db->quoteName('r.id') . ' = ' . $currentRoundId);
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

    private function loadRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            return null;
        }

        $db = $this->database;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('r.id') . ' = ' . $roundId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private function loadFallbackRound(int $autoMode): ?object
    {
        $db = $this->database;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('r.roundcode') . (in_array($autoMode, [0, 2], true) ? ' DESC' : ' ASC'));
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private function loadProject(): ?object
    {
        $db = $this->database;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('current_round'),
                $db->quoteName('current_round_auto'),
                $db->quoteName('auto_time'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $this->projectId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }
}
