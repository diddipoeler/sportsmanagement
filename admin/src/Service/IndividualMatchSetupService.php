<?php
/**
 * Joomla 5/6 setup service for individual match placeholder rows.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Ensures the expected placeholder rows exist for an individual-sport team match. */
final class IndividualMatchSetupService
{
    private const SPORT_SMALL_BORE = 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION';
    private const SPORT_TENNIS = 'COM_SPORTSMANAGEMENT_ST_TENNIS';

    /**
     * Create a placeholder-game setup service using Joomla's database connection.
     * Stores the database driver used to count existing singles and doubles and add missing match slots transactionally.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Ensure the required number of placeholder games for a supported individual sport.
     * Reads rifle and tennis match-count settings, counts existing singles and doubles by fixture and type, then inserts only missing empty slots under one transaction.
     *
     * @param object $project Project sport settings and configured game counts.
     * @param int $matchId Parent match identifier.
     * @param int $roundId Owning round identifier.
     * @param int $projectTeam1Id Home project-team identifier.
     * @param int $projectTeam2Id Away project-team identifier.
     * @return bool False for invalid fixture IDs, true after setup or for other sports.
     */
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

    /**
     * Count the stored single or double game slots for a fixture.
     * Quotes the match type and restricts the database count to the requested parent fixture, using only internal table and column names.
     *
     * @param int $matchId Parent fixture identifier.
     * @param string $matchType Game category, such as SINGLE or DOUBLE.
     * @return int Number of existing placeholder or played games of that type.
     */
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

    /**
     * Run game-slot creation atomically through Joomla's database transaction API.
     * Commits a successful callback and attempts rollback before rethrowing exceptions on failure.
     *
     * @param callable $callback Database write work to execute inside the transaction.
     * @return mixed Result returned by the callback.
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
