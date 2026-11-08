<?php
/**
 * Joomla 5/6 setup service for individual Golf/Billard match rows.
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

/** Creates the historical five Golf/Billard single rows from numbered starters. */
final class IndividualMatchGolfSetupService
{
    /**
     * Create the golf and billiards individual-match setup service.
     * Stores the Joomla database driver used to read the nominated starters and create the corresponding five possible single games.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Create up to five golf or billiards singles from home and away shirt numbers.
     * Checks the fixture identifiers, returns early when any singles already exist, then pairs shirt numbers one through five in a transaction and records the administrator and modification timestamp.
     *
     * @param int $matchId Parent fixture identifier.
     * @param int $roundId Round containing the fixture.
     * @param int $homeTeamId Home project-team identifier.
     * @param int $awayTeamId Away project-team identifier.
     * @param int $userId Administrator creating the rows.
     * @param string $modified SQL modification date for inserted games.
     * @return bool Whether existing rows are sufficient or setup completed successfully.
     */
    public function ensureSingles(
        int $matchId,
        int $roundId,
        int $homeTeamId,
        int $awayTeamId,
        int $userId,
        string $modified
    ): bool {
        if ($matchId <= 0 || $roundId <= 0 || $homeTeamId <= 0 || $awayTeamId <= 0) {
            return false;
        }

        if ($this->hasSingles($matchId)) {
            return true;
        }

        $home = $this->startersByShirtNumber($homeTeamId, $matchId);
        $away = $this->startersByShirtNumber($awayTeamId, $matchId);
        $db = $this->db;
        $db->transactionStart();

        try {
            for ($number = 1; $number <= 5; $number++) {
                if (!isset($home[$number], $away[$number])) {
                    continue;
                }

                $db->insertObject('#__sportsmanagement_match_single', (object) [
                    'match_id' => $matchId,
                    'round_id' => $roundId,
                    'match_number' => (string) $number,
                    'projectteam1_id' => $homeTeamId,
                    'projectteam2_id' => $awayTeamId,
                    'teamplayer1_id' => $home[$number],
                    'teamplayer2_id' => $away[$number],
                    'published' => 1,
                    'modified' => $modified,
                    'modified_by' => $userId,
                ]);
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();

            throw $e;
        }
    }

    /**
     * Check whether a parent fixture already has individual games.
     * Uses a bound match identifier when counting existing single-match rows so repeated setup does not create duplicate games.
     *
     * @param int $matchId Parent fixture identifier.
     * @return bool True when at least one single-match row already exists.
     */
    private function hasSingles(int $matchId): bool
    {
        $db = $this->db;
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match_single'))
            ->where($db->quoteName('match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        return (int) $db->setQuery($query)->loadResult() > 0;
    }

    /**
     * Load the starting lineup indexed by shirt numbers one through five.
     * Joins match-player entries to the nominated project-team roster, excludes substitutes and returns the season-team-person IDs keyed by shirt number.
     *
     * @param int $projectTeamId Project-team identifier to inspect.
     * @param int $matchId Parent fixture identifier.
     * @return array<int,int> Starter person IDs indexed by shirt number.
     */
    private function startersByShirtNumber(int $projectTeamId, int $matchId): array
    {
        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.trikot_number'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.id = mp.teamplayer_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('mp.came_in') . ' = 0')
            ->where($db->quoteName('mp.trikot_number') . ' BETWEEN 1 AND 5')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        $rows = $db->setQuery($query)->loadObjectList() ?: [];
        $result = [];

        foreach ($rows as $row) {
            $result[(int) $row->trikot_number] = (int) $row->teamplayer_id;
        }

        return $result;
    }
}
