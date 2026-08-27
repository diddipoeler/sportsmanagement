<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Throwable;

/**
 * Joomla 5/6 model for the frontend tournament bracket.
 */
final class JltournamenttreeModel extends SportsManagementProjectModel
{
    public int $projectid = 0;
    public int $count_tournament_round = 0;
    public int $menue_itemid = 0;
    public array $request = [];
    public array $allmatches = [];
    public array $bracket = [];
    public string $color_from = '#FFFFFF';
    public string $color_to = '#0000FF';
    public string $which_first_round = 'scrollLeft()';
    public int $font_size = 14;
    public int $jl_tree_bracket_round_width = 300;
    public int $jl_tree_bracket_teamb_width = 70;
    public int $jl_tree_bracket_width = 140;
    public string $jl_tree_jquery_version = '1.7.1';
    public string $jsmoption = 'com_sportsmanagement';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->projectid = $this->getProjectId();
        $this->jsmoption = Factory::getApplication()->getInput()->getCmd('option', 'com_sportsmanagement');
    }

    public function getWhichJQuery(): string
    {
        return $this->jl_tree_jquery_version;
    }

    public function getWhichShowFirstRound(): string
    {
        return $this->which_first_round;
    }

    public function getTreeBracketRoundWidth(): int
    {
        return $this->jl_tree_bracket_round_width;
    }

    public function getTreeBracketTeambWidth(): int
    {
        $this->jl_tree_bracket_teamb_width = (int) round(70 * $this->jl_tree_bracket_round_width / 100);
        return $this->jl_tree_bracket_teamb_width;
    }

    public function getTreeBracketWidth(): int
    {
        $this->jl_tree_bracket_width = $this->jl_tree_bracket_round_width + 40;
        return $this->jl_tree_bracket_width;
    }

    public function getFontSize(): int
    {
        return $this->font_size;
    }

    public function getColorFrom(): string
    {
        return $this->color_from;
    }

    public function getColorTo(): string
    {
        return $this->color_to;
    }

    public function getTournamentRounds(): array
    {
        if ($this->projectid <= 0) {
            $this->count_tournament_round = 0;
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('ro') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'ro'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'ma')
                    . ' ON ' . $db->quoteName('ma.round_id') . ' = ' . $db->quoteName('ro.id')
            )
            ->where($db->quoteName('ro.project_id') . ' = ' . $this->projectid)
            ->where($db->quoteName('ro.tournement') . ' = 1')
            ->order($db->quoteName('ro.roundcode') . ' DESC');

        try {
            $db->setQuery($query);
            $rounds = $db->loadObjectList() ?: [];
            $this->count_tournament_round = count($rounds);
            return $rounds;
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            $this->count_tournament_round = 0;
            return [];
        }
    }

    public function getTournamentBracketRounds($rounds): string
    {
        $tempRounds = [];

        foreach ((array) $rounds as $round) {
            $roundCode = (int) ($round->roundcode ?? 0);
            $roundName = $this->escapeJavascriptString((string) ($round->name ?? ''));
            $tempRounds[$roundCode] = '{roundname: "' . $roundName . '"}';
        }

        ksort($tempRounds, SORT_NUMERIC);

        return '[' . implode(',', $tempRounds) . ']';
    }

    public function getTournamentMatches($rounds = null): string
    {
        $rounds = is_array($rounds) ? $rounds : [];
        $this->bracket = [];

        if ($this->projectid <= 0 || !$rounds) {
            return '';
        }

        $treeModel = new TreetonodeModel();
        $nodes = $treeModel->getTreetonode();
        if (!is_array($nodes) || !$nodes) {
            return '';
        }

        usort(
            $nodes,
            static fn (object $first, object $second): int =>
                ((int) ($first->node ?? 0)) <=> ((int) ($second->node ?? 0))
        );

        $matchIds = [];
        foreach ($nodes as $node) {
            $matchId = (int) ($node->match_id ?? 0);
            if ($matchId > 0) {
                $matchIds[$matchId] = $matchId;
            }
        }

        $matches = $this->loadTournamentMatches(array_values($matchIds));
        $minimumRoundCode = $this->getMinimumTournamentRoundCode();

        foreach ($nodes as $node) {
            $matchId = (int) ($node->match_id ?? 0);
            if ($matchId <= 0) {
                continue;
            }

            $match = $matches[$matchId] ?? null;
            $roundCode = $match
                ? (int) ($match->roundcode ?? 0)
                : (int) ($node->roundcode ?? 0);

            if ($roundCode <= 0) {
                continue;
            }

            $item = new \stdClass();
            $item->match_id = $matchId;
            $item->projectteam1_id = $match ? (int) ($match->projectteam1_id ?? 0) : 0;
            $item->projectteam2_id = $match ? (int) ($match->projectteam2_id ?? 0) : 0;
            $item->team1_result = $match ? ($match->team1_result ?? '') : '';
            $item->team2_result = $match ? ($match->team2_result ?? '') : '';
            $item->node = (int) ($node->node ?? 0);

            $this->bracket[$roundCode][$matchId] = $item;
        }

        if ($minimumRoundCode <= 0 || empty($this->bracket[$minimumRoundCode])) {
            return '';
        }

        usort(
            $this->bracket[$minimumRoundCode],
            static fn (object $first, object $second): int =>
                ((int) ($first->node ?? 0)) <=> ((int) ($second->node ?? 0))
        );

        $projectTeamIds = [];
        foreach ($this->bracket[$minimumRoundCode] as $bracketItem) {
            foreach (['projectteam1_id', 'projectteam2_id'] as $field) {
                $projectTeamId = (int) ($bracketItem->{$field} ?? 0);
                if ($projectTeamId > 0) {
                    $projectTeamIds[$projectTeamId] = $projectTeamId;
                }
            }
        }
        $teamInfo = $this->loadProjectTeamInfo(array_values($projectTeamIds));

        foreach ($this->bracket[$minimumRoundCode] as $bracketItem) {
            $bracketItem->firstname = '';
            $bracketItem->firstcountry = '';
            $bracketItem->firstlogo = Uri::base() . \sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
            $bracketItem->secondname = '';
            $bracketItem->secondcountry = '';
            $bracketItem->secondlogo = Uri::base() . \sportsmanagementHelper::getDefaultPlaceholder('clublogobig');

            $firstTeam = $teamInfo[(int) $bracketItem->projectteam1_id] ?? null;
            if ($firstTeam) {
                $bracketItem->firstname = (string) ($firstTeam->name ?? '');
                $bracketItem->firstcountry = (string) ($firstTeam->country ?? '');
                $bracketItem->firstlogo = Uri::base() . (string) ($firstTeam->logo_big ?? '');
            }

            $secondTeam = $teamInfo[(int) $bracketItem->projectteam2_id] ?? null;
            if ($secondTeam) {
                $bracketItem->secondname = (string) ($secondTeam->name ?? '');
                $bracketItem->secondcountry = (string) ($secondTeam->country ?? '');
                $bracketItem->secondlogo = Uri::base() . (string) ($secondTeam->logo_big ?? '');
            }
        }

        $this->debugBracket($minimumRoundCode);

        $varTeams = [];
        $this->request['tree_logo'] = 3;

        foreach ($rounds as $round) {
            $roundCode = (int) ($round->roundcode ?? 0);
            if ($roundCode !== $minimumRoundCode || empty($this->bracket[$roundCode])) {
                continue;
            }

            foreach ($this->bracket[$roundCode] as $bracketItem) {
                $varTeams[] = $this->formatBracketTeams($bracketItem, (int) $this->request['tree_logo']);
            }
        }

        if ($this->isFrontendDebugEnabled()) {
            Factory::getApplication()->enqueueMessage(
                __METHOD__ . ' ' . __LINE__ . ' varteams <pre>' . print_r($varTeams, true) . '</pre>',
                ''
            );
        }

        return implode(',', $varTeams);
    }

    public function getTournamentResults($rounds = null): string
    {
        $varResults = [];

        foreach ((array) $rounds as $round) {
            $roundCode = (int) ($round->roundcode ?? 0);
            $roundBracket = $this->bracket[$roundCode] ?? [];
            $tempResults = [];

            foreach ($roundBracket as $item) {
                $home = $item->team1_result ?? '';
                $away = $item->team2_result ?? '';

                if ($home !== '' && $home !== null && $away !== '' && $away !== null) {
                    $tempResults[] = '[' . $home . ',' . $away . ']';
                } elseif (($home === '' || $home === null) && $away !== '' && $away !== null) {
                    $tempResults[] = '[null,' . $away . ']';
                } elseif ($home !== '' && $home !== null && ($away === '' || $away === null)) {
                    $tempResults[] = '[' . $home . ',null]';
                } else {
                    $tempResults[] = '[null,null]';
                }
            }

            $varResults[$roundCode] = '[' . implode(',', $tempResults) . ']';
        }

        if (!$varResults) {
            return '';
        }

        ksort($varResults, SORT_NUMERIC);

        if ($this->isFrontendDebugEnabled()) {
            Factory::getApplication()->enqueueMessage(
                __METHOD__ . ' ' . __LINE__ . ' varresults <pre>' . print_r($varResults, true) . '</pre>',
                ''
            );
        }

        return implode(',', $varResults);
    }

    public function checkStartExtension(): void
    {
        Factory::getApplication();
    }

    private function loadTournamentMatches(array $matchIds): array
    {
        if (!$matchIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                    . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('m.id') . ' IN (' . implode(',', array_map('intval', $matchIds)) . ')')
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectid);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    private function getMinimumTournamentRoundCode(): int
    {
        if ($this->projectid <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('MIN(' . $db->quoteName('r.roundcode') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                    . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectid)
            ->where($db->quoteName('r.tournement') . ' = 1');

        try {
            $db->setQuery($query, 0, 1);
            return (int) $db->loadResult();
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return 0;
        }
    }

    private function loadProjectTeamInfo(array $projectTeamIds): array
    {
        if (!$projectTeamIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('t.name'),
                $db->quoteName('c.country'),
                $db->quoteName('c.logo_big'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->where($db->quoteName('pt.id') . ' IN (' . implode(',', array_map('intval', $projectTeamIds)) . ')')
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectid);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('projectteamid') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    private function formatBracketTeams(object $item, int $treeLogo): string
    {
        if ($treeLogo === 1) {
            $first = $item->firstname !== ''
                ? '{name: "' . $this->escapeJavascriptString($item->firstname)
                    . '", flag: "' . $this->escapeJavascriptString($item->firstlogo) . '"}'
                : 'null';
            $second = $item->secondname !== ''
                ? '{name: "' . $this->escapeJavascriptString($item->secondname)
                    . '", flag: "' . $this->escapeJavascriptString($item->secondlogo) . '"}'
                : 'null';

            return '[' . $first . ', ' . $second . ']';
        }

        if ($treeLogo === 2) {
            $firstFlag = Uri::base() . 'images/com_sportsmanagement/database/flags/'
                . strtolower(\JSMCountries::convertIso3to2($item->firstcountry)) . '.png';
            $secondFlag = Uri::base() . 'images/com_sportsmanagement/database/flags/'
                . strtolower(\JSMCountries::convertIso3to2($item->secondcountry)) . '.png';

            return '[{name: "' . $this->escapeJavascriptString($item->firstname)
                . '", flag: "' . $this->escapeJavascriptString($firstFlag)
                . '"}, {name: "' . $this->escapeJavascriptString($item->secondname)
                . '", flag: "' . $this->escapeJavascriptString($secondFlag) . '"}]';
        }

        $first = $item->firstname !== ''
            ? '"<img src=\\"' . $this->escapeJavascriptString($item->firstlogo)
                . '\\" width=\\"16\\"> ' . $this->escapeJavascriptString($item->firstname) . '"'
            : 'null';
        $second = $item->secondname !== ''
            ? '"<img src=\\"' . $this->escapeJavascriptString($item->secondlogo)
                . '\\" width=\\"16\\"> ' . $this->escapeJavascriptString($item->secondname) . '"'
            : 'null';

        return '[' . $first . ', ' . $second . ']';
    }

    private function escapeJavascriptString(string $value): string
    {
        return strtr(
            $value,
            [
                '\\' => '\\\\',
                '"' => '\\"',
                "\r" => '\\r',
                "\n" => '\\n',
            ]
        );
    }

    private function isFrontendDebugEnabled(): bool
    {
        return (bool) ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend');
    }

    private function debugBracket(int $minimumRoundCode): void
    {
        if (!$this->isFrontendDebugEnabled()) {
            return;
        }

        Factory::getApplication()->enqueueMessage(
            __METHOD__ . ' ' . __LINE__ . ' bracket erste runde <pre>'
                . print_r($this->bracket[$minimumRoundCode] ?? [], true) . '</pre>',
            ''
        );
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
    }
}
