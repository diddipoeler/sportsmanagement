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
    /**
     * Create the individual-match writer using Joomla's database abstraction.
     * Retains the database dependency used by inserted singles, state changes and transactional deletions.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Insert one individual match from the submitted administrator form.
     * Requires valid parent match and round identifiers, maps the player and team references and sends the row through Joomla's insertObject() API.
     *
     * @param array $data Individual match form values.
     * @return bool Whether Joomla inserted the record.
     */
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

    /**
     * Create missing individual games for the supplied home and away players.
     * Pairs the submitted player lists, skips combinations already in the database and reports inserted and failed records without aborting the entire batch on one insertion error.
     *
     * @param array $data Submitted pairings, match/round IDs and team references.
     * @param int $modifiedBy Administrator identifier stored with generated games.
     * @param string $modified Timestamp stored with generated games.
     * @return array{0:int,1:int} Counts of inserted and failed single matches.
     */
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

    /**
     * Publish or unpublish only the explicitly selected individual games.
     * Validates and deduplicates selected IDs before building the Joomla update query, then applies it inside a database transaction.
     *
     * @param array $ids Selected single-match identifiers from the administrator form.
     * @param int $state New publication state; zero unpublishes.
     * @return bool False for an empty selection, true after a successful transaction.
     */
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

    /**
     * Delete only the explicitly selected individual-match records.
     * Normalises the record IDs to safe positive integers and removes those rows atomically using Joomla's query builder.
     *
     * @param array $ids Selected single-match record identifiers.
     * @return bool False for empty selections, true after successful deletion.
     */
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

    /**
     * Check whether a specific pairing is already stored for its parent match.
     * Binds match, round, project-team and player identifiers and limits the lookup to one row to avoid duplicate inserts.
     *
     * @param int $roundId Round containing the match.
     * @param int $matchId Parent match identifier.
     * @param int $projectTeam1Id Home project-team identifier.
     * @param int $projectTeam2Id Away project-team identifier.
     * @param int $homePlayerId Home player identifier.
     * @param int $awayPlayerId Away player identifier.
     * @return bool True when the exact pairing already exists.
     */
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

    /**
     * Validate and deduplicate untrusted IDs used in bulk match operations.
     * Accepts integers and decimal strings (including zero-padded ones), and rejects nonpositive, malformed or overflowing values instead of letting intval() turn arrays and booleans into unrelated record IDs.
     *
     * @param array $ids Raw selection values.
     * @return array<int,int> Unique positive record identifiers.
     */
    private function normaliseIds(array $ids): array
    {
        $normalised = [];

        foreach ($ids as $candidate) {
            if (!is_int($candidate) && !is_string($candidate)) {
                continue;
            }

            $raw = trim((string) $candidate);
            if ($raw === '' || preg_match('/^[0-9]+$/D', $raw) !== 1) {
                continue;
            }

            $digits = ltrim($raw, '0');
            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $normalised[$id] = $id;
            }
        }

        return array_values($normalised);
    }

    /**
     * Execute a database write callback within a Joomla transaction.
     * Commits a successful callback and attempts a rollback on any exception before rethrowing it to the caller.
     *
     * @param callable $callback Work performed inside the transaction.
     * @return mixed Callback result, preserving its return type.
     */
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
