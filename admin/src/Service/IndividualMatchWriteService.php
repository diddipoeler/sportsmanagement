<?php
/**
 * Joomla 5/6 transactional individual-match write service.
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

/** Transactional writer for individual-match results used by the frontend match editor. */
final class IndividualMatchWriteService
{
    private const SPORT_GOLF_BILLARD = 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD';
    private const SPORT_SMALL_BORE = 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION';
    private const SPORT_TABLE_TENNIS = 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS';
    private const SPORT_TENNIS = 'COM_SPORTSMANAGEMENT_ST_TENNIS';

    /**
     * Initialise the transactional individual-match writer with Joomla's database.
     * Retains the injected DatabaseInterface for all scoped match queries, result updates and rollback-safe operations.
     * @param DatabaseInterface $db Database connection for match result writes.
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Persist individual-match results for the selected parent match and project.
     * Validates parent/project membership and every selected single-match ID before using stored team IDs, then dispatches sport-specific writes within one transaction.
     * @param array $post Submitted match results and sport-specific form fields.
     * @param array $ids Selected single-match record identifiers.
     * @param int $modifiedBy Joomla user ID recording the change.
     * @param string $modified Database-compatible modification timestamp.
     * @return bool Whether the save succeeded or safely performed the legacy no-op.
     */
    public function saveShort(array $post, array $ids, int $modifiedBy, string $modified): bool
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
        $matchId = (int) ($post['match_id'] ?? 0);
        $projectId = (int) ($post['project_id'] ?? 0);

        if ($matchId <= 0 || $projectId <= 0) {
            return false;
        }
        if (!$ids) {
            // Legacy saveshort() silently did nothing when no single-match rows were selected.
            return true;
        }

        // A valid parent match ID is not sufficient: cid[] contains IDs from
        // match_single, and every selected row must belong to that parent.
        // Verify the posted project against the actual match's round, too.
        $match = $this->matchForProject($matchId, $projectId);
        if (!$match || !$this->allSingleMatchesBelongToMatch($ids, $matchId)) {
            return false;
        }

        // Team references are used to create score events. They must come
        // from the persisted match, never from hidden frontend form inputs.
        $post['projectteam1_id'] = (int) ($match->projectteam1_id ?? 0);
        $post['projectteam2_id'] = (int) ($match->projectteam2_id ?? 0);

        $project = $this->projectConfig($projectId);
        if (!$project) {
            return false;
        }

        $sportName = $this->sportTypeName((int) $project->sports_type_id);
        if ($sportName === '') {
            return false;
        }

        return $this->transaction(function () use ($sportName, $project, $post, $ids, $matchId, $modifiedBy, $modified): bool {
            return match ($sportName) {
                self::SPORT_GOLF_BILLARD => $this->saveGolfBillard($post, $ids, $matchId, $modifiedBy, $modified),
                self::SPORT_SMALL_BORE => $this->saveSmallBore($post, $ids, $matchId, (int) $project->sports_type_id, $modifiedBy, $modified),
                self::SPORT_TABLE_TENNIS, self::SPORT_TENNIS => $this->saveRacketSport(
                    $sportName,
                    $post,
                    $ids,
                    $matchId,
                    (int) $project->sports_type_id,
                    (bool) $project->use_tie_break,
                    (int) $project->game_parts,
                    $modifiedBy,
                    $modified
                ),
                // Preserve the legacy no-op for sports types without a dedicated individual-result writer.
                default => true,
            };
        });
    }

    /**
     * Update individual golf or billiards results and the parent totals.
     * Aggregates paired split scores for each selected game, saves its person and match metadata, then sums all persisted single-match results into the parent match.
     * @param array $post Submitted game and split-score fields.
     * @param array $ids Selected single-match record identifiers.
     * @param int $matchId Verified parent match identifier.
     * @param int $modifiedBy Editing user identifier.
     * @param string $modified Modification timestamp.
     * @return bool True after successful result updates.
     */
    private function saveGolfBillard(array $post, array $ids, int $matchId, int $modifiedBy, string $modified): bool
    {
        foreach ($ids as $id) {
            $homeSplit = $this->arrayField($post, 'team1_result_split' . $id);
            $awaySplit = $this->arrayField($post, 'team2_result_split' . $id);
            $home = 0;
            $away = 0;

            foreach ($homeSplit as $key => $value) {
                if ($value === '' || !array_key_exists($key, $awaySplit) || $awaySplit[$key] === '') {
                    continue;
                }
                $home += (int) $value;
                $away += (int) $awaySplit[$key];
            }

            $row = (object) [
                'id' => $id,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
                'team1_result' => $home,
                'team2_result' => $away,
                'match_number' => (string) ($post['match_number' . $id] ?? ''),
                'teamplayer1_id' => (int) ($post['teamplayer1_id' . $id] ?? 0),
                'teamplayer2_id' => (int) ($post['teamplayer2_id' . $id] ?? 0),
                'match_type' => (string) ($post['match_type' . $id] ?? ''),
                'team1_result_split' => implode(';', $homeSplit),
                'team2_result_split' => implode(';', $awaySplit),
            ];
            $this->db->updateObject('#__sportsmanagement_match_single', $row, 'id', true);
        }

        $rows = $this->singleMatches($matchId);
        $home = 0;
        $away = 0;
        foreach ($rows as $row) {
            $home += (int) ($row->team1_result ?? 0);
            $away += (int) ($row->team2_result ?? 0);
        }

        $this->db->updateObject(
            '#__sportsmanagement_match',
            (object) ['id' => $matchId, 'team1_result' => $home, 'team2_result' => $away],
            'id',
            true
        );

        return true;
    }

    /**
     * Store small-bore shooting scores and match-level ring totals.
     * Updates each shooter's individual result, creates the relevant first sport event when possible, and accumulates home rings on the parent match.
     * @param array $post Shooter/result form values.
     * @param array $ids Selected single-match IDs.
     * @param int $matchId Verified parent match identifier.
     * @param int $sportTypeId Selected shooting sport type.
     * @param int $modifiedBy Editing user identifier.
     * @param string $modified Modification timestamp.
     * @return bool True when all individual and parent records were updated.
     */
    private function saveSmallBore(
        array $post,
        array $ids,
        int $matchId,
        int $sportTypeId,
        int $modifiedBy,
        string $modified
    ): bool {
        $eventTypeId = $this->firstEventTypeId($sportTypeId);
        $projectTeamId = (int) ($post['projectteam1_id'] ?? 0);
        $ringTotal = 0;

        foreach ($ids as $id) {
            $teamPlayerId = (int) ($post['teamplayer1_id' . $id] ?? 0);
            $home = (int) ($post['team1_result' . $id] ?? 0);
            $away = (int) ($post['team2_result' . $id] ?? 0);
            $row = (object) [
                'id' => $id,
                'teamplayer1_id' => $teamPlayerId,
                'team1_result' => $home,
                'team2_result' => $away,
                'ringetotal' => $home,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];
            $this->db->updateObject('#__sportsmanagement_match_single', $row, 'id', true);
            $ringTotal += $home;

            if ($eventTypeId > 0 && $teamPlayerId > 0 && $projectTeamId > 0) {
                $this->upsertScoreEvent($matchId, $projectTeamId, $teamPlayerId, $eventTypeId, $home);
            }
        }

        $this->db->updateObject(
            '#__sportsmanagement_match',
            (object) [
                'id' => $matchId,
                'team1_result' => $ringTotal,
                'ringetotal' => $ringTotal,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ],
            'id',
            true
        );

        return true;
    }

    /**
     * Persist tennis or table-tennis single-match scores and derived outcomes.
     * Calculates results from split sets or direct scores, records player rating changes, synchronises win/loss events, and recomputes the parent match summary.
     * @param string $sportName Sport translation key used to select event names.
     * @param array $post Submitted match, player and set-result fields.
     * @param array $ids Selected single-match IDs.
     * @param int $matchId Verified parent match ID.
     * @param int $sportTypeId Sport type used to find outcome events.
     * @param bool $useTieBreak Whether the project uses tie breaks.
     * @param int $gameParts Number of normal game parts for summary aggregation.
     * @param int $modifiedBy Editing user ID.
     * @param string $modified Modification timestamp.
     * @return bool True after successful individual, event and summary updates.
     */
    private function saveRacketSport(
        string $sportName,
        array $post,
        array $ids,
        int $matchId,
        int $sportTypeId,
        bool $useTieBreak,
        int $gameParts,
        int $modifiedBy,
        string $modified
    ): bool {
        $eventPrefix = $sportName === self::SPORT_TABLE_TENNIS
            ? 'COM_SPORTSMANAGEMENT_TABLETENNIS'
            : 'COM_SPORTSMANAGEMENT_TENNIS';
        $events = $this->eventTypeMap($sportTypeId);
        $projectTeam1Id = (int) ($post['projectteam1_id'] ?? 0);
        $projectTeam2Id = (int) ($post['projectteam2_id'] ?? 0);

        foreach ($ids as $id) {
            $homeSplit = $this->arrayField($post, 'team1_result_split' . $id);
            $awaySplit = $this->arrayField($post, 'team2_result_split' . $id);
            [$homeResult, $awayResult, $homeRings, $awayRings] = $this->setResult($homeSplit, $awaySplit);

            if (!$homeSplit && !$awaySplit) {
                $homeResult = (int) ($post['team1_result' . $id] ?? 0);
                $awayResult = (int) ($post['team2_result' . $id] ?? 0);
                $homeRings = $homeResult;
                $awayRings = $awayResult;
            }

            $player1 = (int) ($post['teamplayer1_id' . $id] ?? 0);
            $player2 = (int) ($post['teamplayer2_id' . $id] ?? 0);
            $start1 = $this->startPoints($player1);
            $start2 = $this->startPoints($player2);
            [$normal, $anormal] = $this->ratingDelta(abs($start1 - $start2));

            $row = (object) [
                'id' => $id,
                'match_number' => (string) ($post['match_number' . $id] ?? ''),
                'match_type' => (string) ($post['match_type' . $id] ?? ''),
                'crowd' => (int) ($post['crowd' . $id] ?? 0),
                // Editing results must not reassign an existing single match
                // to another round, division or project team.
                'teamplayer1_id' => $player1,
                'teamplayer2_id' => $player2,
                'tt_startpoints_teamplayer1_id' => $start1,
                'tt_startpoints_teamplayer2_id' => $start2,
                'double_team1_player1' => (int) ($post['double_team1_player1' . $id] ?? 0),
                'double_team1_player2' => (int) ($post['double_team1_player2' . $id] ?? 0),
                'double_team2_player1' => (int) ($post['double_team2_player1' . $id] ?? 0),
                'double_team2_player2' => (int) ($post['double_team2_player2' . $id] ?? 0),
                'team1_result' => $homeResult,
                'team2_result' => $awayResult,
                'ringetotal_teamplayer1_id' => $homeRings,
                'ringetotal_teamplayer2_id' => $awayRings,
                'team1_result_split' => implode(';', $homeSplit),
                'team2_result_split' => implode(';', $awaySplit),
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];
            $this->applyRatingColumns($row, $homeResult, $awayResult, $normal, $anormal);
            $this->db->updateObject('#__sportsmanagement_match_single', $row, 'id', true);

            if ($homeResult !== $awayResult) {
                $homeWon = $homeResult > $awayResult;
                $this->syncOutcomeEvents($events, $eventPrefix, $matchId, $projectTeam1Id, $player1, 'SINGLE', $homeWon);
                $this->syncOutcomeEvents($events, $eventPrefix, $matchId, $projectTeam2Id, $player2, 'SINGLE', !$homeWon);
                $this->syncOutcomeEvents($events, $eventPrefix, $matchId, $projectTeam1Id, (int) $row->double_team1_player1, 'DOUBLE', $homeWon);
                $this->syncOutcomeEvents($events, $eventPrefix, $matchId, $projectTeam1Id, (int) $row->double_team1_player2, 'DOUBLE', $homeWon);
                $this->syncOutcomeEvents($events, $eventPrefix, $matchId, $projectTeam2Id, (int) $row->double_team2_player1, 'DOUBLE', !$homeWon);
                $this->syncOutcomeEvents($events, $eventPrefix, $matchId, $projectTeam2Id, (int) $row->double_team2_player2, 'DOUBLE', !$homeWon);
            }
        }

        $summary = $this->aggregateRacketResults($this->singleMatches($matchId), $useTieBreak, $gameParts);
        $summary->id = $matchId;
        $this->db->updateObject('#__sportsmanagement_match', $summary, 'id', true);

        return true;
    }

    /**
     * Count the won sets and raw rings of two players from paired score parts.
     *
     * Ignores incomplete split pairs and returns each side's win count and
     * numeric totals so the racket-sport writer can save both score formats.
     *
     * @param array $homeSplit Home-side per-part scores.
     * @param array $awaySplit Away-side per-part scores.
     * @return array{0:int,1:int,2:int,3:int} Home wins, away wins, home rings, away rings.
     */
    private function setResult(array $homeSplit, array $awaySplit): array
    {
        $home = 0;
        $away = 0;
        $homeRings = 0;
        $awayRings = 0;

        foreach ($homeSplit as $key => $value) {
            if ($value === '' || !array_key_exists($key, $awaySplit) || $awaySplit[$key] === '') {
                continue;
            }
            $homeValue = (int) $value;
            $awayValue = (int) $awaySplit[$key];
            $homeRings += $homeValue;
            $awayRings += $awayValue;

            if ($homeValue > $awayValue) {
                $home++;
            } elseif ($homeValue < $awayValue) {
                $away++;
            } else {
                $home++;
                $away++;
            }
        }

        return [$home, $away, $homeRings, $awayRings];
    }

    /**
     * Populate table-tennis player rating deltas for the match outcome.
     * Initialises all normal and alternative rating columns, then credits the winner and debits the loser with the computed change amounts.
     * @param object $row Mutable individual-match database row.
     * @param int $home Home-side result.
     * @param int $away Away-side result.
     * @param int $normal Standard rating delta.
     * @param int $anormal Alternative rating delta.
     * @return void
     */
    private function applyRatingColumns(object $row, int $home, int $away, int $normal, int $anormal): void
    {
        $row->tt_teamplayer1_id_normal_won = 0;
        $row->tt_teamplayer1_id_normal_lost = 0;
        $row->tt_teamplayer1_id_anormal_won = 0;
        $row->tt_teamplayer1_id_anormal_lost = 0;
        $row->tt_teamplayer2_id_normal_won = 0;
        $row->tt_teamplayer2_id_normal_lost = 0;
        $row->tt_teamplayer2_id_anormal_won = 0;
        $row->tt_teamplayer2_id_anormal_lost = 0;

        if ($home > $away) {
            $row->tt_teamplayer1_id_normal_won = $normal;
            $row->tt_teamplayer1_id_anormal_won = $anormal;
            $row->tt_teamplayer2_id_normal_lost = -$normal;
            $row->tt_teamplayer2_id_anormal_lost = -$anormal;
        } elseif ($home < $away) {
            $row->tt_teamplayer1_id_normal_lost = -$normal;
            $row->tt_teamplayer1_id_anormal_lost = -$anormal;
            $row->tt_teamplayer2_id_normal_won = $normal;
            $row->tt_teamplayer2_id_anormal_won = $anormal;
        }
    }

    /**
     * Select the normal and alternative table-tennis rating adjustments.
     *
     * Uses the players' starting-point difference to choose the legacy
     * rating bracket and returns its standard and alternative deltas.
     *
     * @param int $difference Absolute difference between player starting points.
     * @return array{0:int,1:int} Normal and alternative rating adjustments.
     */
    private function ratingDelta(int $difference): array
    {
        return match (true) {
            $difference <= 49 => [6, 6],
            $difference <= 99 => [5, 8],
            $difference <= 149 => [4, 10],
            $difference <= 199 => [3, 12],
            $difference <= 299 => [2, 15],
            $difference <= 399 => [1, 19],
            default => [0, 25],
        };
    }

    /**
     * Aggregate individual racket-sport games into one parent match summary.
     * Counts won singles, match points, sets and game totals, using the configured tie-break location and number of game parts.
     * @param array $rows Persisted individual-match result records.
     * @param bool $useTieBreak Whether a tie-break game is configured.
     * @param int $gameParts Number of configured game parts.
     * @return object Parent match results and single-match counters.
     */
    private function aggregateRacketResults(array $rows, bool $useTieBreak, int $gameParts): object
    {
        $summary = (object) [
            'team1_result' => 0,
            'team2_result' => 0,
            'team1_single_matchpoint' => 0,
            'team2_single_matchpoint' => 0,
            'team1_single_sets' => 0,
            'team2_single_sets' => 0,
            'team1_single_games' => 0,
            'team2_single_games' => 0,
        ];
        $tieBreakIndex = max(0, $useTieBreak ? $gameParts - 1 : $gameParts);

        foreach ($rows as $row) {
            $home = (int) ($row->team1_result ?? 0);
            $away = (int) ($row->team2_result ?? 0);
            if ($home > $away) {
                $summary->team1_result++;
            } elseif ($home < $away) {
                $summary->team2_result++;
            } else {
                $summary->team1_result++;
                $summary->team2_result++;
            }

            $summary->team1_single_sets += $home;
            $summary->team2_single_sets += $away;
            $homeSplit = $this->splitString((string) ($row->team1_result_split ?? ''));
            $awaySplit = $this->splitString((string) ($row->team2_result_split ?? ''));

            foreach ($homeSplit as $key => $value) {
                if (!$useTieBreak || $key < $tieBreakIndex) {
                    $summary->team1_single_games += $value;
                }
            }
            foreach ($awaySplit as $key => $value) {
                if (!$useTieBreak || $key < $tieBreakIndex) {
                    $summary->team2_single_games += $value;
                }
            }

            if ($useTieBreak && isset($homeSplit[$tieBreakIndex], $awaySplit[$tieBreakIndex])) {
                if ($homeSplit[$tieBreakIndex] > $awaySplit[$tieBreakIndex]) {
                    $summary->team1_single_games++;
                } elseif ($homeSplit[$tieBreakIndex] < $awaySplit[$tieBreakIndex]) {
                    $summary->team2_single_games++;
                }
            } elseif (!$useTieBreak && isset($homeSplit[$tieBreakIndex], $awaySplit[$tieBreakIndex])) {
                // Preserve the legacy final-game addition when no tie break is configured.
                $summary->team1_single_games += $homeSplit[$tieBreakIndex];
                $summary->team2_single_games += $awaySplit[$tieBreakIndex];
            }
        }

        $summary->team1_single_matchpoint = $summary->team1_result;
        $summary->team2_single_matchpoint = $summary->team2_result;
        return $summary;
    }

    /**
     * Register the matching win or loss event for an individual player.
     * Selects the sport-specific event code for singles or doubles and forwards valid match/team/player IDs to the insert-if-missing event writer.
     * @param array $events Event-name-to-ID map for this sport.
     * @param string $prefix Sport-specific event key prefix.
     * @param int $matchId Verified parent match ID.
     * @param int $projectTeamId Participating project-team ID.
     * @param int $teamPlayerId Related player assignment ID.
     * @param string $kind SINGLE or DOUBLE event suffix.
     * @param bool $won Whether this player won the match.
     * @return void
     */
    private function syncOutcomeEvents(
        array $events,
        string $prefix,
        int $matchId,
        int $projectTeamId,
        int $teamPlayerId,
        string $kind,
        bool $won
    ): void {
        if ($matchId <= 0 || $projectTeamId <= 0 || $teamPlayerId <= 0) {
            return;
        }

        $wonId = (int) ($events[$prefix . '_E_' . $kind . '_WON'] ?? 0);
        $lostId = (int) ($events[$prefix . '_E_' . $kind . '_LOST'] ?? 0);
        $currentId = $won ? $wonId : $lostId;
        if ($currentId <= 0) {
            return;
        }

        // Legacy behaviour replaces only the currently calculated event type.
        $query = $this->db->createQuery()
            ->delete($this->db->quoteName('#__sportsmanagement_match_event'))
            ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('teamplayer_id') . ' = ' . $teamPlayerId)
            ->where($this->db->quoteName('event_type_id') . ' = ' . $currentId);
        $this->db->setQuery($query)->execute();

        $this->db->insertObject('#__sportsmanagement_match_event', (object) [
            'match_id' => $matchId,
            'projectteam_id' => $projectTeamId,
            'teamplayer_id' => $teamPlayerId,
            'event_type_id' => $currentId,
            'event_sum' => 1,
        ]);
    }

    /**
     * Add a sports result event only if the identical event does not exist.
     * Queries match, project team, player and event type before inserting a new event row, preserving legacy idempotent behaviour.
     * @param int $matchId Parent match identifier.
     * @param int $projectTeamId Project-team identifier.
     * @param int $teamPlayerId Player assignment identifier.
     * @param int $eventTypeId Sports event type identifier.
     * @param int $value Result value stored alongside the event.
     * @return void
     */
    private function upsertScoreEvent(int $matchId, int $projectTeamId, int $teamPlayerId, int $eventTypeId, int $value): void
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_match_event'))
            ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('projectteam_id') . ' = ' . $projectTeamId)
            ->where($this->db->quoteName('teamplayer_id') . ' = ' . $teamPlayerId)
            ->where($this->db->quoteName('event_type_id') . ' = ' . $eventTypeId);
        $this->db->setQuery($query, 0, 1);

        if ((int) $this->db->loadResult() > 0) {
            return;
        }

        $this->db->insertObject('#__sportsmanagement_match_event', (object) [
            'match_id' => $matchId,
            'projectteam_id' => $projectTeamId,
            'teamplayer_id' => $teamPlayerId,
            'event_type_id' => $eventTypeId,
            'event_sum' => $value,
        ]);
    }

    /**
     * Verify that the parent match belongs to the requested project.
     *
     * Joins the match to its round to check the project ID and reads the
     * authoritative project-team IDs used by subsequent match event writes.
     *
     * @param int $matchId Parent match identifier.
     * @param int $projectId Expected project identifier.
     * @return object|null Verified parent match data or null.
     */
    private function matchForProject(int $matchId, int $projectId): ?object
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('m.projectteam1_id'),
                $this->db->quoteName('m.projectteam2_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $this->db->quoteName('r.id')
                . ' = ' . $this->db->quoteName('m.round_id')
            )
            ->where($this->db->quoteName('m.id') . ' = :singleParentMatchId')
            ->where($this->db->quoteName('r.project_id') . ' = :singleParentProjectId')
            ->bind(':singleParentMatchId', $matchId, ParameterType::INTEGER)
            ->bind(':singleParentProjectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    /**
     * Reject the entire batch if even one selected single-match row is
     * missing or belongs to a different match.
     */
    private function allSingleMatchesBelongToMatch(array $ids, int $matchId): bool
    {
        if (!$ids || $matchId <= 0) {
            return false;
        }

        $query = $this->db->createQuery()
            ->select('COUNT(DISTINCT ' . $this->db->quoteName('ms.id') . ')')
            ->from($this->db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->whereIn($this->db->quoteName('ms.id'), $ids, ParameterType::INTEGER)
            ->where($this->db->quoteName('ms.match_id') . ' = :singleParentId')
            ->bind(':singleParentId', $matchId, ParameterType::INTEGER);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult() === count($ids);
    }

    /**
     * Read the sport and scoring configuration of a parent project.
     * Selects the tie-break setting, number of game parts and sports-type ID required to choose the correct individual-match writer.
     * @param int $projectId Project identifier.
     * @return object|null Project configuration or null when unavailable.
     */
    private function projectConfig(int $projectId): ?object
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('use_tie_break'),
                $this->db->quoteName('game_parts'),
                $this->db->quoteName('sports_type_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project'))
            ->where($this->db->quoteName('id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);
        return $this->db->loadObject() ?: null;
    }

    /**
     * Resolve the stored sport name used for result-writer dispatch.
     * Reads the name of a sport type by its integer ID so the parent writer can select golf, shooting or racket sport logic.
     * @param int $sportTypeId Sport type identifier.
     * @return string Sport name, or empty string when missing.
     */
    private function sportTypeName(int $sportTypeId): string
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('name'))
            ->from($this->db->quoteName('#__sportsmanagement_sports_type'))
            ->where($this->db->quoteName('id') . ' = ' . $sportTypeId);
        $this->db->setQuery($query, 0, 1);
        return (string) ($this->db->loadResult() ?: '');
    }

    /**
     * Find the first available event type for a sport.
     * Orders the sport's event definitions by ID and returns the lowest matching event ID for small-bore score events.
     * @param int $sportTypeId Sport type identifier.
     * @return int First event type ID, or zero when unavailable.
     */
    private function firstEventTypeId(int $sportTypeId): int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_eventtype'))
            ->where($this->db->quoteName('sports_type_id') . ' = ' . $sportTypeId)
            ->order($this->db->quoteName('id') . ' ASC');
        $this->db->setQuery($query, 0, 1);
        return (int) ($this->db->loadResult() ?: 0);
    }

    /**
     * Build the event-name-to-ID lookup for the current sport type.
     *
     * Reads Joomla event types from the database and indexes the resulting
     * records by event name for winner and loser event synchronisation.
     *
     * @param int $sportTypeId Sport type identifier.
     * @return array<string,int> Event identifiers keyed by event names.
     */
    private function eventTypeMap(int $sportTypeId): array
    {
        $query = $this->db->createQuery()
            ->select([$this->db->quoteName('name'), $this->db->quoteName('id')])
            ->from($this->db->quoteName('#__sportsmanagement_eventtype'))
            ->where($this->db->quoteName('sports_type_id') . ' = ' . $sportTypeId);
        $this->db->setQuery($query);

        $map = [];
        foreach ($this->db->loadObjectList() ?: [] as $event) {
            $map[(string) $event->name] = (int) $event->id;
        }
        return $map;
    }

    /**
     * Read the starting table-tennis rating points for a registered player.
     * Looks up the stored tt_startpoints for a positive season-team-person assignment, falling back to zero for unknown players.
     * @param int $teamPlayerId Season-team-person assignment identifier.
     * @return int Player starting rating or zero.
     */
    private function startPoints(int $teamPlayerId): int
    {
        if ($teamPlayerId <= 0) {
            return 0;
        }
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('tt_startpoints'))
            ->from($this->db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($this->db->quoteName('id') . ' = ' . $teamPlayerId);
        $this->db->setQuery($query, 0, 1);
        return (int) ($this->db->loadResult() ?: 0);
    }

    /**
     * Load all individual matches associated with a parent match.
     *
     * Returns the persisted single-match records after sport-specific writes
     * so the parent match total can be recomputed from saved data.
     *
     * @param int $matchId Parent match identifier.
     * @return array<int,object> Single-match rows.
     */
    private function singleMatches(int $matchId): array
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('ms') . '.*')
            ->from($this->db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->where($this->db->quoteName('ms.match_id') . ' = ' . $matchId);
        $this->db->setQuery($query);
        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Read a posted array field without accepting scalar substitutes.
     *
     * Normalises valid array fields to a zero-indexed list, and returns
     * an empty array for missing or malformed form inputs.
     *
     * @param array $post Submitted match form fields.
     * @param string $key Form field key to read.
     * @return array<int,mixed> Normalised field values.
     */
    private function arrayField(array $post, string $key): array
    {
        return isset($post[$key]) && is_array($post[$key]) ? array_values($post[$key]) : [];
    }

    /**
     * Convert a semicolon-delimited split-score value into integers.
     *
     * Preserves the legacy empty-string convention and produces a list of
     * integer score segments for aggregate racket match calculations.
     *
     * @param string $value Semicolon-delimited numeric scores.
     * @return array<int,int> Parsed integer score segments.
     */
    private function splitString(string $value): array
    {
        if ($value === '') {
            return [];
        }
        return array_map('intval', explode(';', $value));
    }

    /**
     * Execute a match-result write callback atomically.
     * Starts a database transaction, commits on a successful callback result, rolls back on failure and attempts rollback before rethrowing errors.
     * @param callable $callback Callback implementing the selected sport's database writes.
     * @return bool Whether the callback completed successfully.
     * @throws \Throwable Rethrows callback or database exceptions after rollback.
     */
    private function transaction(callable $callback): bool
    {
        $this->db->transactionStart();
        try {
            $result = (bool) $callback();
            if ($result) {
                $this->db->transactionCommit();
                return true;
            }
            $this->db->transactionRollback();
            return false;
        } catch (\Throwable $e) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
            }
            throw $e;
        }
    }
}
