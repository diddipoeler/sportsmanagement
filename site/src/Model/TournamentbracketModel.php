<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Throwable;

if (!class_exists(SportsManagementModel::class)) {
    require_once __DIR__ . '/SportsManagementModel.php';
}

if (!class_exists(TournamentbracketResultNormalizer::class)) {
    require_once __DIR__ . '/TournamentbracketResultNormalizer.php';
}

/**
 * Native Joomla 5/6 tournament-bracket model.
 *
 * The historic bracket ordering and double-leg aggregation are preserved, but
 * database access now runs entirely through SportsManagementModel.
 */
final class TournamentbracketModel extends SportsManagementModel
{
    private TournamentbracketResultNormalizer $resultNormalizer;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->resultNormalizer = new TournamentbracketResultNormalizer();
    }

    public function gettournamentbracket($project_id = 0): array
    {
        $projectId = max(0, (int) $project_id);
        $country = $this->getProjectCountry($projectId);
        $rounds = $this->getTournamentRounds($projectId);

        $start = 0;
        $selectFirstRound = true;
        $results = [];
        $roundTeams = [];
        $roundNames = [];
        $detailResults = [];
        $finalTeams = [];
        $detailIndex = 0;
        $doubleRound = false;

        foreach ($rounds as $roundIndex => $round) {
            $matches = $this->getRoundMatches((int) $round->id);

            if ($matches !== [] && $selectFirstRound) {
                $start = $roundIndex + 1;

                foreach ($matches as $match) {
                    $homeId = (int) ($match->projectteam1_id ?? 0);
                    $awayId = (int) ($match->projectteam2_id ?? 0);
                    $knownTeams = $roundTeams[$roundIndex] ?? [];

                    if (in_array($homeId, $knownTeams, true) && in_array($awayId, $knownTeams, true)) {
                        $doubleRound = true;
                    } else {
                        $roundTeams[$roundIndex][] = $homeId;
                        $roundTeams[$roundIndex][] = $awayId;
                    }

                    $normalised = $this->resultNormalizer->normalise($match);
                    $homeResult = $normalised['home'];
                    $awayResult = $normalised['away'];

                    if (!$doubleRound) {
                        $results[$roundIndex][] = $this->resultPlaceholder($homeId, $awayId, (string) $round->name);
                    }

                    foreach ($detailResults as $roundDetail) {
                        foreach ($roundDetail as $key2 => $value2) {
                            foreach ($value2 as $key3 => $value3) {
                                if (!array_key_exists($homeId, $value2)) {
                                    $detailIndex = (int) $key3 === $homeId ? (int) $key2 : (int) $key2 + 1;
                                }
                            }
                        }
                    }

                    $detailResults[$roundIndex][$detailIndex][$homeId][] = $homeResult;
                    $detailResults[$roundIndex][$detailIndex][$awayId][] = $awayResult;
                }

                $detailIndex++;
                $selectFirstRound = false;
            }

            $roundNames[] = '"' . (string) $round->name . '"';
        }

        for ($roundIndex = $start; $roundIndex < count($rounds); $roundIndex++) {
            $previousRoundIndex = $roundIndex - 1;
            $detailIndex = 0;

            foreach (($roundTeams[$previousRoundIndex] ?? []) as $startTeamId) {
                $startTeamId = (int) $startTeamId;
                $singleMatches = $this->getMatchesForTeam((int) $rounds[$roundIndex]->id, $startTeamId);
                $doubleRound = count($singleMatches) > 1;
                $selectedHome = 0;
                $selectedAway = 0;
                $lastMatch = null;

                foreach ($singleMatches as $match) {
                    $lastMatch = $match;
                    $homeId = (int) ($match->projectteam1_id ?? 0);
                    $awayId = (int) ($match->projectteam2_id ?? 0);
                    $normalised = $this->resultNormalizer->normalise($match);

                    $detailResults[$roundIndex][$detailIndex][$homeId][] = $normalised['home'];
                    $detailResults[$roundIndex][$detailIndex][$awayId][] = $normalised['away'];

                    if (!$doubleRound) {
                        $roundTeams[$roundIndex][] = $homeId;
                        $roundTeams[$roundIndex][] = $awayId;
                        $results[$roundIndex][] = $this->resultPlaceholder(
                            $homeId,
                            $awayId,
                            (string) $rounds[$roundIndex]->name
                        );
                    }

                    $selectedHome = $homeId;
                    $selectedAway = $awayId;
                }

                if ($singleMatches === []) {
                    $results[$roundIndex][] = '[null,null]';
                    $roundTeams[$roundIndex][] = $startTeamId;
                    $roundTeams[$roundIndex][] = null;
                    $selectedHome = $startTeamId;
                    $selectedAway = 0;
                }

                if ($roundIndex === count($rounds) - 1) {
                    $homeTeam = $this->getTeamInfo($selectedHome, $country);
                    $awayTeam = $this->getTeamInfo($selectedAway, $country);
                    $finalTeams[] = $this->buildTeamPair($homeTeam, $selectedAway > 0 ? $awayTeam : null);
                }

                if ($doubleRound && $lastMatch !== null) {
                    $homeId = (int) ($lastMatch->projectteam1_id ?? 0);
                    $awayId = (int) ($lastMatch->projectteam2_id ?? 0);
                    $roundName = (string) $rounds[$roundIndex]->name;

                    if ($selectedHome === $startTeamId) {
                        $results[$roundIndex][] = $this->resultPlaceholder($homeId, $awayId, $roundName);
                        $roundTeams[$roundIndex][] = $selectedHome;
                        $roundTeams[$roundIndex][] = $selectedAway;
                    }

                    if ($selectedAway === $startTeamId) {
                        $results[$roundIndex][] = $this->resultPlaceholder($awayId, $homeId, $roundName);
                        $roundTeams[$roundIndex][] = $selectedHome;
                        $roundTeams[$roundIndex][] = $selectedAway;
                    }
                }

                $detailIndex++;
            }
        }

        krsort($detailResults);
        krsort($results);

        foreach ($detailResults as $roundIndex => $roundDetail) {
            foreach ($roundDetail as $resultIndex => $teamResults) {
                foreach ($teamResults as $projectTeamId => $values) {
                    if (!isset($results[$roundIndex][$resultIndex])) {
                        continue;
                    }

                    $partialResult = implode(' ', $values);
                    $resultTemplate = $results[$roundIndex][$resultIndex];
                    $resultTemplate = str_replace('teil' . $projectTeamId, $partialResult, $resultTemplate);
                    $results[$roundIndex][$resultIndex] = str_replace(
                        'result' . $projectTeamId,
                        (string) $this->sumResultValues($values),
                        $resultTemplate
                    );
                }
            }
        }

        $resultRows = [];
        $shootoutRows = [];

        foreach ($results as $roundResult) {
            $resultRows[] = '[' . implode(',', $roundResult) . ']';
            $shootoutRows[] = implode('#', $roundResult);
        }

        krsort($roundNames);

        return [
            'elfmeter' => [implode('#', $shootoutRows)],
            'teams' => '[' . implode(',', $finalTeams) . ']',
            'results' => '[' . implode(',', $resultRows) . ']',
            'runden' => '[' . implode(',', $roundNames) . ']',
        ];
    }

    private function getProjectCountry(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('l.country'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable) {
            return '';
        }
    }

    private function getTournamentRounds(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('tournement') . ' = 1')
            ->order($db->quoteName('roundcode') . ' DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function getRoundMatches(int $roundId): array
    {
        if ($roundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $roundId)
            ->where($db->quoteName('published') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function getMatchesForTeam(int $roundId, int $projectTeamId): array
    {
        if ($roundId <= 0 || $projectTeamId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $roundId)
            ->where($db->quoteName('published') . ' = 1')
            ->where('(' . $db->quoteName('projectteam1_id') . ' = ' . $projectTeamId
                . ' OR ' . $db->quoteName('projectteam2_id') . ' = ' . $projectTeamId . ')');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function getTeamInfo(int $projectTeamId, string $fallbackCountry): object
    {
        $fallback = (object) [
            'country' => $fallbackCountry,
            'logo_big' => 'images/com_sportsmanagement/database/clubs/large/placeholder_wappen_150.png',
            'name' => 'FREI',
        ];

        if ($projectTeamId <= 0) {
            return $fallback;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.name'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }


    private function sumResultValues(array $values): int|float
    {
        $sum = 0.0;

        foreach ($values as $value) {
            if (is_int($value) || is_float($value)) {
                $sum += $value;
                continue;
            }

            if (is_numeric($value)) {
                $sum += (float) $value;
                continue;
            }

            if (is_string($value) && preg_match('/^\\s*([+-]?(?:\\d+(?:\\.\\d*)?|\\.\\d+))/', $value, $match)) {
                $sum += (float) $match[1];
            }
        }

        return floor($sum) === $sum ? (int) $sum : $sum;
    }

    private function resultPlaceholder(int $homeId, int $awayId, string $roundName): string
    {
        return '[result' . $homeId . ',result' . $awayId . ',"' . $roundName . '","teil'
            . $homeId . ' - teil' . $awayId . '"]';
    }

    private function buildTeamPair(object $homeTeam, ?object $awayTeam): string
    {
        $home = $this->buildTeamLabel($homeTeam);

        if ($awayTeam === null) {
            return '[ "' . $home . '",null]';
        }

        return '[ "' . $home . '","' . $this->buildTeamLabel($awayTeam) . '"]';
    }

    private function buildTeamLabel(object $team): string
    {
        $country = (string) ($team->country ?? '');
        $flag = strtolower((string) \JSMCountries::convertIso3to2($country));
        $logo = (string) ($team->logo_big ?? 'images/com_sportsmanagement/database/clubs/large/placeholder_wappen_150.png');
        $name = (string) ($team->name ?? 'FREI');

        return '<img src=\\"' . Uri::base() . 'images/com_sportsmanagement/database/flags/' . $flag
            . '\\" width=\\"16\\"> <img src=\\"' . Uri::base() . $logo
            . '\\" width=\\"16\\"> ' . $name;
    }
}
