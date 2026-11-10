<?php
/**
 * Joomla 5/6 model for the frontend tournament bracket.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
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

    /**
     * Initialise the tournament-tree model with Joomla MVC services and the
     * selected project. Stores the native project ID and component option so the
     * database queries stay in the selected frontend context.
     *
     * @param array $config Native Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->projectid = $this->getProjectId();
        $this->jsmoption = $this->siteApplication()->getInput()->getCmd('option', 'com_sportsmanagement');
    }

    /**
     * Expose the historical bracket-library version for old template
     * overrides. Native Joomla views use the bundled Web Asset Manager library
     * instead of adding scripts dynamically.
     *
     * @return string Legacy library version identifier.
     */
    public function getWhichJQuery(): string
    {
        return $this->jl_tree_jquery_version;
    }

    /**
     * Return the legacy first-round scrolling expression.
     * The native read-only renderer does not execute this saved JavaScript value,
     * but older template overrides can continue reading the setting.
     *
     * @return string Historical bracket scrolling option.
     */
    public function getWhichShowFirstRound(): string
    {
        return $this->which_first_round;
    }

    /**
     * Read the width configured for tournament bracket rounds.
     * The native view clamps this value before rendering it as a CSS variable.
     *
     * @return int Configured bracket round width in pixels.
     */
    public function getTreeBracketRoundWidth(): int
    {
        return $this->jl_tree_bracket_round_width;
    }

    /**
     * Calculate the historical team label width as seventy percent
     * of a bracket round. The native view uses a bounded CSS style instead.
     *
     * @return int Calculated team label width in pixels.
     */
    public function getTreeBracketTeambWidth(): int
    {
        $this->jl_tree_bracket_teamb_width = (int) round(70 * $this->jl_tree_bracket_round_width / 100);
        return $this->jl_tree_bracket_teamb_width;
    }

    /**
     * Calculate the legacy total bracket column width including
     * the margin used by the older jQuery Bracket layout.
     *
     * @return int Calculated bracket column width in pixels.
     */
    public function getTreeBracketWidth(): int
    {
        $this->jl_tree_bracket_width = $this->jl_tree_bracket_round_width + 40;
        return $this->jl_tree_bracket_width;
    }

    /**
     * Read the configured team-name font size for the tree display.
     * The native view clamps this value before adding it to HTML.
     *
     * @return int Configured font size in pixels.
     */
    public function getFontSize(): int
    {
        return $this->font_size;
    }

    /**
     * Return the legacy first background gradient color.
     * The native view validates its CSS hexadecimal representation.
     *
     * @return string Configured starting gradient color.
     */
    public function getColorFrom(): string
    {
        return $this->color_from;
    }

    /**
     * Return the legacy final background gradient color.
     * The native view validates its CSS hexadecimal representation.
     *
     * @return string Configured ending gradient color.
     */
    public function getColorTo(): string
    {
        return $this->color_to;
    }

    /**
     * Load the selected project's tournament rounds through parameterized
     * Joomla database queries. Only rounds with tournament matches are returned;
     * query failures are logged privately and reported to visitors generically.
     *
     * @return array<int,object> Ordered tournament round records or an empty array.
     */
    public function getTournamentRounds(): array
    {
        if ($this->projectid <= 0) {
            $this->count_tournament_round = 0;
            return [];
        }

        $projectId = $this->projectid;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('ro') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'ro'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'ma')
                    . ' ON ' . $db->quoteName('ma.round_id') . ' = ' . $db->quoteName('ro.id')
            )
            ->where($db->quoteName('ro.project_id') . ' = :projectId')
            ->where($db->quoteName('ro.tournement') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
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

    /**
     * Build the historical JavaScript round-heading fragment for older
     * overrides. Sorts round codes numerically and escapes text values; the new
     * native view derives round titles as JSON-ready arrays instead.
     *
     * @param mixed $rounds Tournament round records returned by getTournamentRounds().
     * @return string Legacy JavaScript round-heading array text.
     */
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

    /**
     * Build bracket match positions from the current tournament's tree nodes.
     * Loads scoped match and team records, calculates opening-round team names
     * and scores, and stores match objects in the bracket property. Retains a
     * legacy JavaScript fragment return for historical overrides; the native
     * Joomla view deliberately ignores that fragment and serializes the objects.
     *
     * @param mixed $rounds Selected tournament rounds or null.
     * @return string Historical JavaScript team pair fragment.
     */
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
            Log::add(__METHOD__ . ': prepared ' . count($varTeams) . ' tournament teams.', Log::INFO, 'com_sportsmanagement');
        }

        return implode(',', $varTeams);
    }

    /**
     * Convert existing bracket results to the historical JavaScript score
     * fragment required by legacy overrides. Sanitizes every score as an integer
     * literal or null to avoid arbitrary database content becoming executable JS.
     * The native view reads typed bracket objects and never uses the fragment.
     *
     * @param mixed $rounds Ordered tournament rounds.
     * @return string Safe legacy numeric score-array fragments.
     */
    public function getTournamentResults($rounds = null): string
    {
        $varResults = [];

        foreach ((array) $rounds as $round) {
            $roundCode = (int) ($round->roundcode ?? 0);
            $roundBracket = $this->bracket[$roundCode] ?? [];
            $tempResults = [];

            foreach ($roundBracket as $item) {
                // Historical database values must never become executable
                // JavaScript through a raw result-fragment concatenation.
                $home = self::safeScoreLiteral($item->team1_result ?? null);
                $away = self::safeScoreLiteral($item->team2_result ?? null);
                $tempResults[] = '[' . $home . ',' . $away . ']';
            }

            $varResults[$roundCode] = '[' . implode(',', $tempResults) . ']';
        }

        if (!$varResults) {
            return '';
        }

        ksort($varResults, SORT_NUMERIC);

        if ($this->isFrontendDebugEnabled()) {
            Log::add(__METHOD__ . ': prepared ' . count($varResults) . ' tournament result rounds.', Log::INFO, 'com_sportsmanagement');
        }

        return implode(',', $varResults);
    }

    /**
     * Preserve the former extension-start hook for older tournament callers.
     * Accesses the native site application, validating that the Joomla frontend
     * runtime is available without performing global bootstrap work.
     *
     * @return void
     */
    public function checkStartExtension(): void
    {
        $this->siteApplication();
    }

    /**
     * Fetch tournament matches corresponding to validated positive match IDs.
     * Uses Joomla's bound IN clause, project-scoped joins and a private error
     * reporter, so unrelated projects cannot contribute bracket match data.
     *
     * @param array<int,mixed> $matchIds Requested tree match identifiers.
     * @return array<int,object> Match records indexed by their database primary key.
     */
    private function loadTournamentMatches(array $matchIds): array
    {
        $matchIds = array_values(array_unique(array_filter(array_map('intval', $matchIds), static fn (int $id): bool => $id > 0)));
        if (!$matchIds) {
            return [];
        }

        $projectId = $this->projectid;
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            ->whereIn($db->quoteName('m.id'), $matchIds, ParameterType::INTEGER)
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Find the earliest tournament round in the selected project with matches.
     * Uses a bound project ID and returns zero when the query cannot complete.
     *
     * @return int Positive minimum round code, or zero when unavailable.
     */
    private function getMinimumTournamentRoundCode(): int
    {
        if ($this->projectid <= 0) {
            return 0;
        }

        $projectId = $this->projectid;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('MIN(' . $db->quoteName('r.roundcode') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                    . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return (int) $db->loadResult();
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return 0;
        }
    }

    /**
     * Load team names, countries and club logos only for current-project
     * team identifiers. Deduplicates requested IDs, binds their values and
     * returns an empty map after database errors.
     *
     * @param array<int,mixed> $projectTeamIds Selected SportsManagement project-team IDs.
     * @return array<int,object> Team metadata indexed by project-team ID.
     */
    private function loadProjectTeamInfo(array $projectTeamIds): array
    {
        $projectTeamIds = array_values(array_unique(array_filter(array_map('intval', $projectTeamIds), static fn (int $id): bool => $id > 0)));
        if (!$projectTeamIds) {
            return [];
        }

        $projectId = $this->projectid;
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            ->whereIn($db->quoteName('pt.id'), $projectTeamIds, ParameterType::INTEGER)
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('projectteamid') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Prepare legacy tournament team label fragments in the three historical
     * logo presentation modes. Escapes strings and supports null team slots;
     * the native read-only view now uses plain team-name data instead.
     *
     * @param object $item Bracket match containing team names and logo metadata.
     * @param int $treeLogo Historical label/logo presentation mode.
     * @return string Legacy JavaScript team pair fragment.
     */
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

    /**
     * Escape backslashes, quotes and newlines for legacy JavaScript string
     * fragments still consumed by older tournament overrides. Native views no
     * longer need this because they use JSON encoding and text-only rendering.
     *
     * @param string $value Raw team label or resource URL.
     * @return string Escaped legacy JavaScript string contents.
     */
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

    /**
     * Inspect the administrator-selected frontend diagnostic setting.
     * Diagnostics now go only to Joomla's private log and never print match
     * records, database errors or internal configuration to public visitors.
     *
     * @return bool Whether frontend logging was requested.
     */
    private function isFrontendDebugEnabled(): bool
    {
        return (bool) ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend');
    }

    /**
     * Log a count of bracket nodes in the first tournament round for
     * diagnostic purposes without displaying private match data on the page.
     *
     * @param int $minimumRoundCode First tournament round code.
     * @return void
     */
    private function debugBracket(int $minimumRoundCode): void
    {
        if (!$this->isFrontendDebugEnabled()) {
            return;
        }

        Log::add(
            __METHOD__ . ': first round contains '
                . count($this->bracket[$minimumRoundCode] ?? []) . ' bracket nodes.',
            Log::INFO,
            'com_sportsmanagement'
        );
    }

    /**
     * Log the original Joomla database exception for administrators and
     * notify the frontend with a generic translated error message. Internal
     * SQL text, credentials, table names and paths stay out of visitor output.
     *
     * @param Throwable $e Native Joomla database or driver failure.
     * @return void
     */
    private function reportDatabaseError(Throwable $e): void
    {
        Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');
        $this->siteApplication()->enqueueMessage(
            Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            'error'
        );
    }

    /**
     * Convert a legacy match score to a safe JavaScript numeric literal.
     *
     * The historical team/result view still consumes JavaScript fragments.
     * Only nonnegative whole numbers up to four digits may appear in those
     * fragments; invalid values, markup, arrays and overflow become null.
     * The native view instead builds its own typed JSON payload.
     *
     * @param mixed $score Legacy tournament match result.
     * @return string Decimal integer literal or the null keyword.
     */
    private static function safeScoreLiteral(mixed $score): string
    {
        if (is_int($score)) {
            return $score >= 0 && $score <= 9999 ? (string) $score : 'null';
        }

        if (!is_string($score) || preg_match('/^[0-9]{1,4}$/D', $score) !== 1) {
            return 'null';
        }

        return (string) (int) $score;
    }
}
