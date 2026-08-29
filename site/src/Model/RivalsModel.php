<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 model for the rivals view.
 */
final class RivalsModel extends SportsManagementProjectModel
{
    public static int $cfg_which_database = 0;

    public ?object $project = null;
    public int $projectid = 0;
    public int $teamid = 0;
    public ?object $team = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->projectid = $this->projectId;
        $this->teamid = max(0, $input->getInt('tid', 0));
        $this->team = $this->loadTeam();
    }

    public function getTeam(): ?object
    {
        if ($this->team === null && $this->teamid > 0) {
            $this->team = $this->loadTeam();
        }

        return $this->team;
    }

    /**
     * Aggregate the selected team's historical results by opponent.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getOpponents(): array
    {
        if ($this->projectid <= 0 || $this->teamid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('pt1.project_id'),
                $db->quoteName('pt1.team_id', 'pteam1_id'),
                $db->quoteName('pt2.team_id', 'pteam2_id'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t2.id', 'team2_id'),
                $db->quoteName('pt1.division_id', 'division_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.alt_decision'),
                $db->quoteName('m.team1_result_decision'),
                $db->quoteName('m.team2_result_decision'),
                $db->quoteName('t1.short_name', 'short_name1'),
                $db->quoteName('t2.short_name', 'short_name2'),
                $db->quoteName('t1.middle_name', 'middle_name1'),
                $db->quoteName('t2.middle_name', 'middle_name2'),
                $db->quoteName('t1.name', 'name1'),
                $db->quoteName('t2.name', 'name2'),
                "CONCAT_WS(':', pt1.id, t1.alias) AS projectteam1_slug",
                "CONCAT_WS(':', pt2.id, t2.alias) AS projectteam2_slug",
                $db->quoteName('t1.picture', 'teampicture1'),
                $db->quoteName('t2.picture', 'teampicture2'),
                $db->quoteName('pt1.picture', 'pteampicture1'),
                $db->quoteName('pt2.picture', 'pteampicture2'),
                $db->quoteName('c1.logo_small', 'logo_small1'),
                $db->quoteName('c2.logo_small', 'logo_small2'),
                $db->quoteName('c1.country', 'country1'),
                $db->quoteName('c2.country', 'country2'),
                $db->quoteName('c1.logo_middle', 'logo_middle1'),
                $db->quoteName('c2.logo_middle', 'logo_middle2'),
                $db->quoteName('c1.logo_big', 'logo_big1'),
                $db->quoteName('c2.logo_big', 'logo_big2'),
                $db->quoteName('t1.club_id', 'club1_id'),
                $db->quoteName('t2.club_id', 'club2_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where('(' . $db->quoteName('t1.id') . ' = ' . $this->teamid . ' OR ' . $db->quoteName('t2.id') . ' = ' . $this->teamid . ')')
            ->where('(' . $db->quoteName('m.team1_result') . ' IS NOT NULL OR ' . $db->quoteName('m.alt_decision') . ' > 0)')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->where($db->quoteName('pt1.project_id') . ' = ' . $this->projectid)
            ->where($db->quoteName('pt2.project_id') . ' = ' . $this->projectid)
            ->order($db->quoteName('m.id') . ' ASC');

        try {
            $db->setQuery($query);
            $matches = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage($e->getMessage(), 'error');
            return [];
        }

        $opponents = [];

        foreach ($matches as $match) {
            $selectedIsHome = (int) $match->team1_id === $this->teamid;
            $opponentId = $selectedIsHome ? (int) $match->team2_id : (int) $match->team1_id;

            if ($opponentId <= 0) {
                continue;
            }

            if (!isset($opponents[$opponentId])) {
                $opponents[$opponentId] = $this->emptyOpponent();
            }

            $opponent = &$opponents[$opponentId];
            $opponent['projectteamid'] = (int) ($selectedIsHome ? $match->projectteam2_id : $match->projectteam1_id);
            $opponent['projectteam_slug'] = (string) ($selectedIsHome ? $match->projectteam2_slug : $match->projectteam1_slug);
            $opponent['project_id'] = (int) $match->project_id;
            $opponent['division_id'] = (int) $match->division_id;
            $opponent['match']++;
            $opponent['id'] = $opponentId;
            $opponent['team_id'] = $opponentId;
            $opponent['club_id'] = (int) ($selectedIsHome ? $match->club2_id : $match->club1_id);
            $opponent['name'] = (string) ($selectedIsHome ? $match->name2 : $match->name1);
            $opponent['short_name'] = (string) ($selectedIsHome ? $match->short_name2 : $match->short_name1);
            $opponent['middle_name'] = (string) ($selectedIsHome ? $match->middle_name2 : $match->middle_name1);
            $opponent['logo_small'] = (string) ($selectedIsHome ? $match->logo_small2 : $match->logo_small1);
            $opponent['logo_middle'] = (string) ($selectedIsHome ? $match->logo_middle2 : $match->logo_middle1);
            $opponent['logo_big'] = (string) ($selectedIsHome ? $match->logo_big2 : $match->logo_big1);
            $opponent['country_flag'] = (string) ($selectedIsHome ? $match->country2 : $match->country1);
            $opponent['team_picture'] = (string) ($selectedIsHome ? $match->teampicture2 : $match->teampicture1);
            $opponent['projectteam_picture'] = (string) ($selectedIsHome ? $match->pteampicture2 : $match->pteampicture1);

            $selectedGoals = $selectedIsHome ? $match->team1_result : $match->team2_result;
            $opponentGoals = $selectedIsHome ? $match->team2_result : $match->team1_result;
            $opponent['g_for'] += (float) ($selectedGoals ?? 0);
            $opponent['g_aga'] += (float) ($opponentGoals ?? 0);

            if (!(int) $match->alt_decision) {
                $this->recordOutcome($opponent, $selectedGoals, $opponentGoals);
            } else {
                $selectedDecision = $selectedIsHome
                    ? $match->team1_result_decision
                    : $match->team2_result_decision;
                $opponentDecision = $selectedIsHome
                    ? $match->team2_result_decision
                    : $match->team1_result_decision;

                if ($selectedDecision === null || $selectedDecision === '') {
                    $opponent['forfeit']++;
                } else {
                    $this->recordOutcome($opponent, $selectedDecision, $opponentDecision);
                }
            }

            unset($opponent);
        }

        $sorted = array_values($opponents);
        usort(
            $sorted,
            static function (array $left, array $right): int {
                return ($right['match'] <=> $left['match'])
                    ?: ($right['win'] <=> $left['win'])
                    ?: ($right['g_for'] <=> $left['g_for'])
                    ?: strcasecmp((string) $left['name'], (string) $right['name']);
            }
        );

        return $sorted;
    }

    private function loadTeam(): ?object
    {
        if ($this->teamid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.id') . ' = ' . $this->teamid);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage($e->getMessage(), 'error');
            return null;
        }
    }

    /** @return array<string,mixed> */
    private function emptyOpponent(): array
    {
        return [
            'match' => 0,
            'name' => '',
            'g_for' => 0,
            'g_aga' => 0,
            'win' => 0,
            'tie' => 0,
            'los' => 0,
            'forfeit' => 0,
        ];
    }

    private function recordOutcome(array &$opponent, $selectedResult, $opponentResult): void
    {
        if ((float) $selectedResult > (float) $opponentResult) {
            $opponent['win']++;
        } elseif ((float) $selectedResult < (float) $opponentResult) {
            $opponent['los']++;
        } else {
            $opponent['tie']++;
        }
    }
}
