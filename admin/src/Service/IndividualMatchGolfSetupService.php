<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Creates the historical five Golf/Billard single rows from numbered starters. */
final class IndividualMatchGolfSetupService
{
    public function __construct(private DatabaseInterface $db) {}

    public function ensureSingles(int $matchId, int $roundId, int $homeTeamId, int $awayTeamId, int $userId, string $modified): bool
    {
        if ($matchId <= 0 || $roundId <= 0 || $homeTeamId <= 0 || $awayTeamId <= 0) return false;
        if ($this->hasSingles($matchId)) return true;
        $home = $this->startersByShirtNumber($homeTeamId, $matchId);
        $away = $this->startersByShirtNumber($awayTeamId, $matchId);
        $db = $this->db;
        $db->transactionStart();
        try {
            for ($number = 1; $number <= 5; $number++) {
                if (!isset($home[$number], $away[$number])) continue;
                $db->insertObject('#__sportsmanagement_match_single', (object) [
                    'match_id' => $matchId, 'round_id' => $roundId, 'match_number' => (string) $number,
                    'projectteam1_id' => $homeTeamId, 'projectteam2_id' => $awayTeamId,
                    'teamplayer1_id' => $home[$number], 'teamplayer2_id' => $away[$number],
                    'published' => 1, 'modified' => $modified, 'modified_by' => $userId,
                ]);
            }
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        }
    }

    private function hasSingles(int $matchId): bool
    {
        $db = $this->db;
        $query = $db->createQuery()->select('COUNT(*)')->from($db->quoteName('#__sportsmanagement_match_single'))->where('match_id = ' . $matchId);
        return (int) $db->setQuery($query)->loadResult() > 0;
    }

    private function startersByShirtNumber(int $projectTeamId, int $matchId): array
    {
        $db = $this->db;
        $query = $db->createQuery()
            ->select('mp.teamplayer_id, mp.trikot_number')
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.id = mp.teamplayer_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->where('pt.id = ' . $projectTeamId)->where('mp.match_id = ' . $matchId)
            ->where('mp.came_in = 0')->where('mp.trikot_number BETWEEN 1 AND 5');
        $rows = $db->setQuery($query)->loadObjectList() ?: [];
        $result = [];
        foreach ($rows as $row) $result[(int) $row->trikot_number] = (int) $row->teamplayer_id;
        return $result;
    }
}
