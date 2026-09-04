<?php
/**
 * Native Joomla 5/6 administrator CRUD service for individual match rows.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Administrator CRUD operations for individual match rows. */
final class IndividualMatchAdminService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function addMatch(array $data): bool
    {
        $matchId = (int) ($data['match_id'] ?? 0);
        $roundId = (int) ($data['round_id'] ?? 0);
        if ($matchId <= 0 || $roundId <= 0) {
            return false;
        }

        $row = (object) [
            'match_date' => (string) ($data['match_date'] ?? ''),
            'projectteam1_id' => (int) ($data['projectteam1_id'] ?? 0),
            'projectteam2_id' => (int) ($data['projectteam2_id'] ?? 0),
            'match_id' => $matchId,
            'teamplayer1_id' => (int) ($data['teamplayer1_id'] ?? 0),
            'teamplayer2_id' => (int) ($data['teamplayer2_id'] ?? 0),
            'published' => (int) ($data['published'] ?? 1),
            'round_id' => $roundId,
        ];

        return (bool) $this->db->insertObject('#__sportsmanagement_match_single', $row);
    }

    /** @return array{0:int,1:int} inserted, failed */
    public function generateSingles(array $data, int $modifiedBy, string $modified): array
    {
        $homePlayers = array_values((array) ($data['teamplayer1_id'] ?? []));
        $awayPlayers = array_values((array) ($data['teamplayer2_id'] ?? []));
        $matchTypes = array_values((array) ($data['match_type'] ?? []));
        $roundId = (int) ($data['round_id'] ?? 0);
        $matchId = (int) ($data['match_id'] ?? 0);
        $projectTeam1Id = (int) ($data['projectteam1_id'] ?? 0);
        $projectTeam2Id = (int) ($data['projectteam2_id'] ?? 0);

        if ($roundId <= 0 || $matchId <= 0 || !$homePlayers) {
            return [0, 0];
        }

        $inserted = 0;
        $failed = 0;

        foreach ($homePlayers as $index => $homePlayer) {
            $homePlayerId = (int) $homePlayer;
            $awayPlayerId = (int) ($awayPlayers[$index] ?? 0);
            if ($this->singleExists($roundId, $matchId, $projectTeam1Id, $projectTeam2Id, $homePlayerId, $awayPlayerId)) {
                continue;
            }

            $row = (object) [
                'projectteam1_id' => $projectTeam1Id,
                'projectteam2_id' => $projectTeam2Id,
                'match_id' => $matchId,
                'teamplayer1_id' => $homePlayerId,
                'teamplayer2_id' => $awayPlayerId,
                'published' => 1,
                'match_type' => (string) ($matchTypes[$index] ?? ''),
                'round_id' => $roundId,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];

            try {
                $this->db->insertObject('#__sportsmanagement_match_single', $row);
                $inserted++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        return [$inserted, $failed];
    }

    public function setPublished(array $ids, int $state): bool
    {
        $ids = $this->normaliseIds($ids);
        if (!$ids) {
            return false;
        }

        return $this->transaction(function () use ($ids, $state): bool {
            $query = $this->db->createQuery()
                ->update($this->db->quoteName('#__sportsmanagement_match_single'))
                ->set($this->db->quoteName('published') . ' = ' . ($state ? 1 : 0))
                ->where($this->db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
            $this->db->setQuery($query)->execute();
            return true;
        });
    }

    public function deleteSingles(array $ids): bool
    {
        $ids = $this->normaliseIds($ids);
        if (!$ids) {
            return false;
        }

        return $this->transaction(function () use ($ids): bool {
            $query = $this->db->createQuery()
                ->delete($this->db->quoteName('#__sportsmanagement_match_single'))
                ->where($this->db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
            $this->db->setQuery($query)->execute();
            return true;
        });
    }

    private function singleExists(
        int $roundId,
        int $matchId,
        int $projectTeam1Id,
        int $projectTeam2Id,
        int $homePlayerId,
        int $awayPlayerId
    ): bool {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_match_single'))
            ->where($this->db->quoteName('round_id') . ' = :roundId')
            ->where($this->db->quoteName('match_id') . ' = :matchId')
            ->where($this->db->quoteName('projectteam1_id') . ' = :projectTeam1Id')
            ->where($this->db->quoteName('projectteam2_id') . ' = :projectTeam2Id')
            ->where($this->db->quoteName('teamplayer1_id') . ' = :homePlayerId')
            ->where($this->db->quoteName('teamplayer2_id') . ' = :awayPlayerId')
            ->bind(':roundId', $roundId, ParameterType::INTEGER)
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':projectTeam1Id', $projectTeam1Id, ParameterType::INTEGER)
            ->bind(':projectTeam2Id', $projectTeam2Id, ParameterType::INTEGER)
            ->bind(':homePlayerId', $homePlayerId, ParameterType::INTEGER)
            ->bind(':awayPlayerId', $awayPlayerId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) $this->db->loadResult() > 0;
    }

    /** @return int[] */
    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    private function transaction(callable $callback): mixed
    {
        $this->db->transactionStart();
        try {
            $result = $callback();
            $this->db->transactionCommit();
            return $result;
        } catch (\Throwable $e) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
            }
            throw $e;
        }
    }
}
