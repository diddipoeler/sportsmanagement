<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Ensures the expected placeholder rows exist for an individual-sport team match. */
final class IndividualMatchSetupService
{
    private const SPORT_SMALL_BORE = 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION';
    private const SPORT_TENNIS = 'COM_SPORTSMANAGEMENT_ST_TENNIS';

    public function __construct(private DatabaseInterface $db)
    {
    }

    public function ensureMatchSlots(
        object $project,
        int $matchId,
        int $roundId,
        int $projectTeam1Id,
        int $projectTeam2Id
    ): bool {
        if ($matchId <= 0 || $roundId <= 0) {
            return false;
        }

        $targets = match ((string) ($project->sports_type_name ?? '')) {
            self::SPORT_SMALL_BORE => ['SINGLE' => max(0, (int) ($project->single_matches ?? 0))],
            self::SPORT_TENNIS => [
                'SINGLE' => max(0, (int) ($project->tennis_single_matches ?? 0)),
                'DOUBLE' => max(0, (int) ($project->tennis_double_matches ?? 0)),
            ],
            default => [],
        };

        if (!$targets) {
            return true;
        }

        return $this->transaction(function () use ($targets, $matchId, $roundId, $projectTeam1Id, $projectTeam2Id): bool {
            foreach ($targets as $matchType => $target) {
                $current = $this->countSlots($matchId, $matchType);
                for ($index = $current; $index < $target; $index++) {
                    $row = (object) [
                        'round_id' => $roundId,
                        'projectteam1_id' => $projectTeam1Id,
                        'projectteam2_id' => $projectTeam2Id,
                        'match_id' => $matchId,
                        'match_type' => $matchType,
                        'published' => 1,
                        'teamplayer1_id' => 0,
                        'teamplayer2_id' => 0,
                        'summary' => '',
                        'preview' => '',
                    ];
                    $this->db->insertObject('#__sportsmanagement_match_single', $row);
                }
            }

            return true;
        });
    }

    private function countSlots(int $matchId, string $matchType): int
    {
        $query = $this->db->createQuery()
            ->select('COUNT(' . $this->db->quoteName('id') . ')')
            ->from($this->db->quoteName('#__sportsmanagement_match_single'))
            ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('match_type') . ' = ' . $this->db->quote($matchType));
        $this->db->setQuery($query);
        return (int) $this->db->loadResult();
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
