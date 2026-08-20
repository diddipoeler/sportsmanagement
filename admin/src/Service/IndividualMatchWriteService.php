<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Transactional writer for individual-match results used by the frontend match editor. */
final class IndividualMatchWriteService
{
    private const SPORT_GOLF_BILLARD = 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD';
    private const SPORT_SMALL_BORE = 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION';
    private const SPORT_TABLE_TENNIS = 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS';
    private const SPORT_TENNIS = 'COM_SPORTSMANAGEMENT_ST_TENNIS';

    public function __construct(private DatabaseInterface $db)
    {
    }

    public function saveShort(array $post, array $ids, int $modifiedBy, string $modified): bool
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        $matchId = (int) ($post['match_id'] ?? 0);
        $projectId = (int) ($post['project_id'] ?? 0);

        if ($matchId <= 0 || $projectId <= 0) {
            return false;
        }
        if (!$ids) {
            // Legacy saveshort() silently did nothing when no single-match rows were selected.
            return true;
        }

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
                'round_id' => (int) ($post['rid'] ?? 0),
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
                'round_id' => (int) ($post['rid'] ?? 0),
                'division_id' => (int) ($post['division_id'] ?? 0),
                'projectteam1_id' => $projectTeam1Id,
                'projectteam2_id' => $projectTeam2Id,
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

    /** @return array{0:int,1:int,2:int,3:int} */
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

    /** @return array{0:int,1:int} */
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

    private function sportTypeName(int $sportTypeId): string
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('name'))
            ->from($this->db->quoteName('#__sportsmanagement_sports_type'))
            ->where($this->db->quoteName('id') . ' = ' . $sportTypeId);
        $this->db->setQuery($query, 0, 1);
        return (string) ($this->db->loadResult() ?: '');
    }

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

    /** @return array<string,int> */
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

    /** @return array<int,object> */
    private function singleMatches(int $matchId): array
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('ms') . '.*')
            ->from($this->db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->where($this->db->quoteName('ms.match_id') . ' = ' . $matchId);
        $this->db->setQuery($query);
        return $this->db->loadObjectList() ?: [];
    }

    /** @return array<int,mixed> */
    private function arrayField(array $post, string $key): array
    {
        return isset($post[$key]) && is_array($post[$key]) ? array_values($post[$key]) : [];
    }

    /** @return array<int,int> */
    private function splitString(string $value): array
    {
        if ($value === '') {
            return [];
        }
        return array_map('intval', explode(';', $value));
    }

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
