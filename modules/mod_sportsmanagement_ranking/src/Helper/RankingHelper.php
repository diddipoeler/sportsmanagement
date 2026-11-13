<?php
/**
 * Joomla 5/6 helper for the SportsManagement Ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRanking\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class RankingHelper
{
    /**
     * Resolve the active Joomla site application from the service container.
     * The legacy AJAX refresh action uses the same application context as
     * the module dispatcher rather than obsolete global factory access.
     * @return CMSApplicationInterface Active site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Retrieve Joomla's database service through dependency injection.
     * Checks the returned object implements the expected interface so
     * ranking queries cannot continue with an invalid database service.
     * @return DatabaseInterface Joomla-managed database connection.
     * @throws \RuntimeException If the database service is unavailable.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Ranking database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Prepare league ranking data for display in the Joomla 5/6 module.
     * Resolves a selected project, collects ranking rows and permitted
     * presentation options, and creates team links and emblem/flag URLs
     * for the frontend template. Incomplete legacy team records are skipped
     * during formatting and missing preferred-name fields fall back to the
     * ordinary team name without raising PHP 8 property warnings.
     * @param Registry $params Ranking module configuration.
     * @param object $module Current Joomla module record.
     * @param CMSApplicationInterface $app Active site application.
     * @return array Ranking rows and template configuration, or an empty list.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Ranking requires the Joomla site application.', 500);
        }

        $projectId = $this->firstId($params->get('p', 0));

        if ($projectId <= 0) {
            return [];
        }

        $cfg = self::nonNegativeOption($params->get('cfg_which_database', 0));
        $divisionId = self::nonNegativeOption($params->get('division_id', 0));

        // Invalid selectors must not silently query the default database or
        // remove the configured division constraint.
        if ($cfg === null || $divisionId === null) {
            return [];
        }

        $engine = new RankingEngine($this->database($cfg));
        $rankingResult = $engine->calculate($projectId, $divisionId);
        $project = $rankingResult['project'];

        if (empty($project->id)) {
            return [];
        }

        $list = array_values($rankingResult['ranking']);
        $visibleTeamId = $this->firstId($params->get('visible_team'));
        $limit = self::boundedOption($params->get('limit', 5), 5, 1, 100);

        if ($visibleTeamId > 0) {
            $list = $this->shrinkAroundTeam($list, $visibleTeamId, $limit);
        }

        $rawColumns = $params->get('columns', 'PLAYED, POINTS');
        $rawColumnNames = $params->get('column_names', 'MP, PTS');
        $columns = self::columnTokens($rawColumns);
        $columnNames = self::columnTokens($rawColumnNames);

        if (count($columns) !== count($columnNames)) {
            $columns = [];
            $columnNames = [];
        }

        $flagMap = $this->countryFlags($params, $list, $app);
        $rawNameType = $params->get('nametype', 'short_name');
        $nameType = is_string($rawNameType) ? $rawNameType : 'short_name';

        if (!in_array($nameType, ['name', 'short_name', 'middle_name'], true)) {
            $nameType = 'short_name';
        }

        $logoType = self::boundedOption($params->get('show_logo', 0), 0, 0, 6);

        foreach ($list as $row) {
            // Legacy ranking engines may return incomplete records. Avoid
            // attempting dynamic property access on missing team objects.
            if (!is_object($row) || !isset($row->team) || !is_object($row->team)) {
                continue;
            }

            $preferredName = $row->team->{$nameType} ?? null;
            $row->display_team_name = (string) ($preferredName ?: ($row->team->name ?? ''));
            $row->logo_url = $this->logoUrl($row->team, $logoType, $flagMap);
            $row->team_url = $this->teamUrl($params, $project, $row->team);
            $row->column_values = [];

            foreach ($columns as $column) {
                $row->column_values[$column] = $this->columnValue($column, $row);
            }
        }

        $colors = self::boundedOption($params->get('show_rank_colors', 0), 0, 0, 1) === 1
            ? ($rankingResult['colors'] ?? []) : [];
        $fullTableUrl = $this->route('ranking', [
            'cfg_which_database' => $cfg,
            's' => self::nonNegativeOption($params->get('s', 0)) ?? 0,
            'p' => (string) ($project->slug ?? $projectId),
            'type' => 0,
            'r' => (string) ($project->round_slug ?? ''),
            'from' => 0,
            'to' => 0,
            'division' => $divisionId,
        ]);

        $identity = $app->getIdentity();
        $canRefresh = self::boundedOption($params->get('ishd_update', 0), 0, 0, 1) === 1
            && $identity !== null
            && $identity->authorise('core.manage', 'com_sportsmanagement');

        return [
            'project' => $project,
            'ranking' => array_slice($list, 0, $limit),
            'colors' => $colors,
            'columns' => $columns,
            'column_names' => $columnNames,
            'full_table_url' => $fullTableUrl,
            'can_refresh' => $canRefresh,
            'refresh_url' => rtrim((string) Uri::root(), '/') . '/index.php?option=com_ajax&module=sportsmanagement_ranking&method=refresh&format=json',
            'module_id' => (int) ($module->id ?? 0),
        ];
    }

    /**
     * Refresh ranking data through the module's server-side AJAX task.
     * Validates the site context and request token, enforces configured
     * refresh permissions and checks the project's pending match data
     * before performing the optional inline-hockey update.
     * @return array Refresh result and affected-project metadata.
     */
    public function refreshAjax(): array
    {
        $app = self::siteApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Ranking requires the Joomla site application.', 500);
        }

        if (!Session::checkToken('post')) {
            throw new \RuntimeException('Invalid CSRF token.', 403);
        }

        $identity = $app->getIdentity();

        if ($identity === null || !$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorised to refresh SportsManagement match data.', 403);
        }

        $moduleId = $app->getInput()->post->getInt('module_id', 0);

        if ($moduleId <= 0) {
            throw new \RuntimeException('Invalid ranking module.', 400);
        }

        $joomlaDb = self::joomlaDatabase();
        $moduleName = 'mod_sportsmanagement_ranking';
        $query = $joomlaDb->createQuery()
            ->select([$joomlaDb->quoteName('params'), $joomlaDb->quoteName('published')])
            ->from($joomlaDb->quoteName('#__modules'))
            ->where($joomlaDb->quoteName('id') . ' = :moduleId')
            ->where($joomlaDb->quoteName('module') . ' = :moduleName')
            ->where($joomlaDb->quoteName('client_id') . ' = 0')
            ->bind(':moduleId', $moduleId, ParameterType::INTEGER)
            ->bind(':moduleName', $moduleName, ParameterType::STRING);
        $joomlaDb->setQuery($query, 0, 1);
        $module = $joomlaDb->loadObject();

        if (!$module || (int) $module->published !== 1) {
            throw new \RuntimeException('Ranking module is not published.', 404);
        }

        if (!is_string($module->params ?? null) || strlen($module->params) > 65536) {
            throw new \RuntimeException('Invalid ranking module configuration.', 400);
        }

        $params = new Registry($module->params);

        if (self::boundedOption($params->get('ishd_update', 0), 0, 0, 1) !== 1) {
            throw new \RuntimeException('Inline-hockey refresh is disabled for this module.', 403);
        }

        if (self::nonNegativeOption($params->get('cfg_which_database', 0)) !== 0) {
            throw new \RuntimeException('Inline-hockey refresh is restricted to the Joomla database.', 409);
        }

        $projectId = $this->firstId($params->get('p', 0));

        if ($projectId <= 0) {
            throw new \RuntimeException('Ranking module has no valid project.', 400);
        }

        $hours = self::boundedOption($params->get('ishd_update_hour', 4), 4, 1, 168);
        $cutoff = time() - ($hours * 3600);
        $query = $joomlaDb->createQuery()
            ->select('COUNT(*)')
            ->from($joomlaDb->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $joomlaDb->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $joomlaDb->quoteName('r.id') . ' = ' . $joomlaDb->quoteName('m.round_id')
            )
            ->where($joomlaDb->quoteName('r.project_id') . ' = :projectId')
            ->where($joomlaDb->quoteName('m.team1_result') . ' IS NULL')
            ->where($joomlaDb->quoteName('m.match_timestamp') . ' < :cutoff')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':cutoff', $cutoff, ParameterType::INTEGER);
        $joomlaDb->setQuery($query);
        $pending = (int) $joomlaDb->loadResult();

        if ($pending <= 0) {
            return ['updated' => false, 'pending' => 0, 'project_id' => $projectId];
        }

        $modelFile = JPATH_SITE . '/components/com_sportsmanagement/extensions/jsminlinehockey/admin/models/jsminlinehockey.php';

        if (!is_file($modelFile)) {
            throw new \RuntimeException('Inline-hockey importer is not installed.', 500);
        }

        require_once $modelFile;

        if (!class_exists('sportsmanagementModeljsminlinehockey')) {
            throw new \RuntimeException('Inline-hockey importer could not be loaded.', 500);
        }

        $model = new \sportsmanagementModeljsminlinehockey();
        $model->getmatches($projectId);

        return ['updated' => true, 'pending' => $pending, 'project_id' => $projectId];
    }

    /**
     * Resolve country flag resources for the teams in a ranking.
     * Uses the row countries and Joomla module preferences to collect
     * the flag URLs needed by the ranking display.
     * @param Registry $params Module presentation settings.
     * @param array $rows Ranking rows containing teams.
     * @param CMSApplicationInterface $app Active Joomla application.
     * @return array Country-code-to-flag mapping.
     */
    private function countryFlags(Registry $params, array $rows, CMSApplicationInterface $app): array
    {
        $countries = [];

        foreach ($rows as $row) {
            $country = strtoupper(trim((string) ($row->team->country ?? '')));

            if ($country !== '') {
                $countries[$country] = $country;
            }
        }

        if (!$countries) {
            return [];
        }

        $selector = self::nonNegativeOption($params->get('cfg_which_database', 0));
        if ($selector === null) {
            return [];
        }

        $db = $this->database($selector);
        $query = $db->createQuery()
            ->select([$db->quoteName('alpha3'), $db->quoteName('picture')])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->whereIn($db->quoteName('alpha3'), array_values($countries), ParameterType::STRING);
        $db->setQuery($query);
        $map = [];

        foreach ($db->loadObjectList() ?: [] as $country) {
            $map[strtoupper((string) $country->alpha3)] = $this->mediaUrl((string) $country->picture);
        }

        return $map;
    }

    /**
     * Generate the configured destination for a ranking team link.
     * Chooses the supported team or roster view and retains the current
     * project, season and database selection when building the Joomla route.
     * @param Registry $params Team-link module settings.
     * @param object $project Project supplying route IDs.
     * @param object $team Team to link.
     * @return string Route URL or an empty string if links are disabled.
     */
    private function teamUrl(Registry $params, object $project, object $team): string
    {
        $base = [
            'cfg_which_database' => self::nonNegativeOption($params->get('cfg_which_database', 0)) ?? 0,
            's' => self::nonNegativeOption($params->get('s', 0)) ?? 0,
            'p' => (string) ($project->slug ?? $project->id),
        ];

        $rawLink = $params->get('teamlink', 'none');
        return match (is_string($rawLink) ? $rawLink : 'none') {
            'teaminfo' => $this->route('teaminfo', $base + ['tid' => $team->team_slug, 'ptid' => (int) $team->projectteamid]),
            'roster' => $this->route('roster', $base + ['tid' => $team->team_slug, 'ptid' => (int) $team->projectteamid]),
            'teamplan' => $this->route('teamplan', $base + ['tid' => $team->team_slug, 'ptid' => (int) $team->projectteamid, 'division' => (string) ($team->division_slug ?? 0), 'mode' => 0]),
            'clubinfo' => $this->route('clubinfo', $base + ['cid' => (string) ($team->club_slug ?? '')]),
            default => '',
        };
    }

    /**
     * Choose a team's visual marker according to module display settings.
     * Supports country flags or one of the configured club logo sizes
     * and passes the selected image through the safe media URL helper.
     * @param object $team Team row with logo paths.
     * @param int $type Requested marker type.
     * @param array $flags Resolved country flag URLs.
     * @return string Safe image URL, or an empty string.
     */
    private function logoUrl(object $team, int $type, array $flags): string
    {
        if ($type === 2) {
            return $flags[strtoupper((string) ($team->country ?? ''))] ?? '';
        }

        $path = match ($type) {
            1 => (string) ($team->logo_small ?? ''),
            3 => (string) ($team->logo_middle ?? ''),
            4 => (string) ($team->logo_big ?? ''),
            5 => (string) ($team->trikot_home ?? ''),
            6 => (string) ($team->trikot_away ?? ''),
            default => '',
        };

        return $this->mediaUrl($path);
    }

    /**
     * Resolve the value of a requested table column from a ranking row.
     * Maps supported legacy column names to statistics exposed by the
     * ranking item instead of dynamically evaluating a database column.
     * @param string $column Column identifier from module configuration.
     * @param object $item Ranking row and computed statistics.
     * @return mixed Value for the selected statistics column.
     */
    private function columnValue(string $column, object $item): mixed
    {
        $key = strtolower(str_replace('jl_', '', trim($column)));

        return match ($key) {
            'points' => $item->getPoints(),
            'played' => $item->cnt_matches,
            'wins' => $item->cnt_won,
            'ties' => $item->cnt_draw,
            'losses' => $item->cnt_lost,
            'wot' => $item->cnt_wot,
            'wso' => $item->cnt_wso,
            'lot' => $item->cnt_lot,
            'lso' => $item->cnt_lso,
            'scorefor' => $item->sum_team1_result,
            'scoreagainst' => $item->sum_team2_result,
            'results' => $item->sum_team1_result . ':' . $item->sum_team2_result,
            'diff', 'scorediff' => $item->diff_team_results,
            'scorepct' => round($item->scorePct(), 2),
            'bonus' => $item->bonus_points,
            'start' => $item->cnt_lost,
            'winpct' => round($item->winpct(), 2),
            'legs' => $item->sum_team1_legs . ':' . $item->sum_team2_legs,
            'legsdiff' => $item->diff_team_legs,
            'legsratio' => round($item->legsRatio(), 2),
            'negpoints' => $item->neg_points,
            'oldnegpoints' => $item->getPoints() . ':' . $item->neg_points,
            'pointsratio' => round($item->pointsRatio(), 2),
            'gfa' => round($item->getGFA(), 2),
            'gaa' => round($item->getGAA(), 2),
            'ppg' => round($item->getPPG(), 2),
            'ppp' => round($item->getPPP(), 2),
            default => $item->{$key} ?? '?',
        };
    }

    /**
     * Limit ranking rows to a window around a selected team.
     * Locates the highlighted team and selects neighbouring table rows
     * so a compact module still shows its league position in context.
     * @param array $ranking Full ordered ranking rows.
     * @param int $teamId Team that should remain visible.
     * @param int $limit Maximum rows to display.
     * @return array Contiguous subset of ranking rows.
     */
    private function shrinkAroundTeam(array $ranking, int $teamId, int $limit): array
    {
        foreach ($ranking as $index => $item) {
            if ((int) ($item->team->id ?? 0) !== $teamId) {
                continue;
            }

            $other = max(0, $limit - 1);
            $start = $index - intdiv($other, 2) - ($other % 2);
            // Keep a full visible window when the selected team is close to
            // the end: array_slice alone would otherwise return 1-2 rows.
            $maxStart = max(0, count($ranking) - $limit);
            $start = min($maxStart, max(0, $start));

            return array_slice($ranking, $start, $limit);
        }

        return $ranking;
    }

    /**
     * Extract one validated positive identifier from a module selection.
     * Supports scalar and first-array-entry values while rejecting
     * nondecimal, invalid and overflowing identifiers.
     * @param mixed $value Raw module ID selection.
     * @return int Positive selected ID, or zero if invalid.
     */
    private function firstId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $value = trim((string) $value);

        // A Joomla selection may contain "12:team-alias", but a malformed
        // suffix or an overflowing ID must never silently select team 12.
        if (!preg_match('/^([1-9][0-9]*)(?::[^\s:]+)?$/D', $value, $match)) {
            return 0;
        }

        $id = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Build a Joomla frontend URL using the shared site route helper.
     * Keeps ranking destinations consistent with component routing aliases.
     * @param string $view Target SportsManagement view.
     * @param array $parameters Parameters supplied to the view route.
     * @return string Joomla-routed URL.
     */
    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    /**
     * Convert a ranking image reference into a safe frontend media URL.
     *
     * Accept valid HTTP or HTTPS URLs without embedded credentials and reject
     * control characters, unsupported schemes and network-path references.
     * For Joomla-local paths, reject traversal segments and URL metacharacters,
     * encode each path segment, and resolve the result relative to the site
     * root. Invalid paths return an empty string instead of unsafe markup.
     *
     * @param string $path Remote image URL or Joomla-relative media path.
     * @return string Sanitised image URL, or an empty string when invalid.
     */
    private function mediaUrl(string $path): string
    {
        $path = trim($path);

        if ($path === ''
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || str_contains($path, '\\')
        ) {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            if (preg_match('/[\x00-\x20\x7F]/', $path)
                || filter_var($path, FILTER_VALIDATE_URL) === false
            ) {
                return '';
            }

            $parts = parse_url($path);

            return is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                ? $path
                : '';
        }

        if (str_starts_with($path, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)
            || preg_match('~(?:^|/)\.\.?(?:/|$)~', $path)
            || str_contains($path, '%')
            || strpbrk($path, '?#') !== false
        ) {
            return '';
        }

        $relative = ltrim($path, '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return rtrim((string) Uri::root(), '/') . '/' . $encoded;
    }

    /**
     * Validate a nonnegative integer selector without implicit PHP casts.
     *
     * Accepts decimal strings with leading zeros but rejects structured
     * values, signs, whitespace and integer overflow.
     *
     * @param mixed $value Raw Joomla module numeric configuration.
     * @return int|null Nonnegative integer, or null when invalid.
     */
    private static function nonNegativeOption(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || strlen($value) > 256
            || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return null;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? null : $number;
    }

    /**
     * Clamp a nonnegative module setting between the supplied bounds.
     *
     * Rejects malformed settings, caps large valid numbers and never
     * converts arrays or overflowing decimal strings to PHP integers.
     *
     * @param mixed $value Raw module setting.
     * @param int $default Value used for invalid settings.
     * @param int $minimum Lowest supported value.
     * @param int $maximum Highest supported value.
     * @return int Safe bounded option value.
     */
    private static function boundedOption(mixed $value, int $default, int $minimum, int $maximum): int
    {
        if (is_int($value)) {
            return max($minimum, min($maximum, $value));
        }

        if (!is_string($value) || strlen($value) > 4096
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return $default;
        }

        $digits = ltrim($value, '0');
        if ($digits === '') {
            return $minimum;
        }

        if (strlen($digits) > strlen((string) $maximum)
            || (strlen($digits) === strlen((string) $maximum)
                && strcmp($digits, (string) $maximum) > 0)) {
            return $maximum;
        }

        return max($minimum, (int) $digits);
    }

    /**
     * Read a short list of ranking column labels from module settings.
     *
     * Validates input type and length before splitting the comma-separated
     * setting and bounds the number of rendered columns.
     *
     * @param mixed $value Configured column identifiers or headings.
     * @return array<int,string> At most 20 nonempty trimmed labels.
     */
    private static function columnTokens(mixed $value): array
    {
        if (!is_string($value) || strlen($value) > 2048) {
            return [];
        }

        $tokens = [];
        foreach (array_slice(explode(',', $value), 0, 20) as $token) {
            $token = trim($token);
            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    /**
     * Resolve the chosen SportsManagement database for ranking queries.
     * Uses the injected Joomla database as a fallback and delegates the
     * database selection to the component's native resolver. The caller
     * has already verified the selector before this method is invoked.
     * @param int $selector Validated database selection.
     * @return DatabaseInterface Connection used to load ranking data.
     */
    private function database(int $selector): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase(), $selector);
    }
}
