<?php
/**
 * Joomla 5/6 native helper for the SportsManagement liveticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementLiveticker\Site\Helper;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class LivetickerHelper
{
    /**
     * Prepare live-match data and display settings for the module.
     * Requires the Joomla site client, validates the configured database
     * selector and obtains bounded fixture/commentary records used to
     * render the current match list.
     * @param Registry $params Live ticker module configuration.
     * @param object $module Current Joomla module instance.
     * @param CMSApplicationInterface $app Active frontend app.
     * @param DatabaseInterface|null $database Optional injected Joomla database.
     * @return array Live-ticker view data.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app, ?DatabaseInterface $database = null): array
    {
        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Liveticker requires the Joomla site application.', 500);
        }

        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        // Malformed module settings must not switch to a different database.
        if ($selector === null) {
            return [
                'listHtml' => '',
                'updateTimeout' => 10,
                'cssFile' => 'simple.css',
                'moduleId' => (int) ($module->id ?? 0),
            ];
        }

        $database = $this->joomlaDatabase($database);
        $db = $this->database($selector, $database);
        $list = $this->getList(
            $params,
            $app,
            self::boundedNumber($params->get('display_num', 5), 5, 1, 100),
            $db
        );
        $commentary = (bool) $params->get('display_commentary', 1)
            ? $this->getListCommentary($list, $db)
            : [];

        return [
            'listHtml' => $this->buildListHtml($list, $commentary, $params, $app),
            'updateTimeout' => self::boundedNumber($params->get('update_timeout', 10), 10, 1, 3600),
            'cssFile' => self::cssFile($params->get('use_css', 'simple.css')),
            'moduleId' => (int) ($module->id ?? 0),
        ];
    }

    /**
     * Read-only module refresh for Joomla com_ajax.
     *
     * Endpoint: index.php?option=com_ajax&module=sportsmanagement_liveticker&method=refresh&format=raw
     */
    public function refreshAjax(?DatabaseInterface $database = null): string
    {
        $app = self::siteApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Liveticker requires the Joomla site application.', 500);
        }

        $module = $this->requestedModule($app->getInput()->getInt('module_id', 0));

        if ($module === null) {
            return '';
        }

        $params = new Registry();
        $params->loadString((string) ($module->params ?? ''));
        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($selector === null) {
            return '';
        }

        $database = $this->joomlaDatabase($database);
        $db = $this->database($selector, $database);
        $list = $this->getList(
            $params,
            $app,
            self::boundedNumber($params->get('display_num', 5), 5, 1, 100),
            $db
        );
        $commentary = (bool) $params->get('display_commentary', 1)
            ? $this->getListCommentary($list, $db)
            : [];

        return $this->buildListHtml($list, $commentary, $params, $app);
    }

    /**
     * Resolve the native Joomla frontend application for AJAX actions.
     * Obtains the service through the SportsManagement application
     * resolver and does not use obsolete static factory calls.
     * @return CMSApplicationInterface Current Joomla site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Return a caller-provided or Joomla-managed database connection.
     * Uses an injected database for tests and module dispatchers;
     * otherwise validates the DatabaseInterface service from Joomla DI.
     * @param DatabaseInterface|null $database Optional explicit connection.
     * @return DatabaseInterface Connection for ticker queries.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
    {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Liveticker database service is unavailable.', 500);
        }

        return $database;
    }

    /** @return array<int,array<int,object>> */
    public function getListCommentary(array $list, DatabaseInterface $db): array
    {
        $matchIds = [];

        foreach (array_slice($list, 0, 100) as $row) {
            if (!is_object($row)) {
                continue;
            }

            $rawId = $row->match_id ?? null;
            if (!is_int($rawId) && (!is_string($rawId) || !preg_match('/^[0-9]+$/D', $rawId))) {
                continue;
            }

            $matchId = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($matchId !== false) {
                $matchIds[$matchId] = $matchId;
            }
        }

        if ($matchIds === []) {
            return [];
        }

        $query = $db->createQuery()
            ->select($db->quoteName('*'))
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->whereIn($db->quoteName('match_id'), array_values($matchIds), ParameterType::INTEGER)
            ->order([
                $db->quoteName('event_time') . ' DESC',
                $db->quoteName('match_id') . ' ASC',
            ]);
        // The Ajax endpoint may refresh frequently: do not fetch the full
        // lifetime commentary history for every rendered match.
        $db->setQuery($query, 0, 1000);

        $matches = [];

        foreach ($db->loadObjectList() ?: [] as $row) {
            $matchId = (int) ($row->match_id ?? 0);

            if ($matchId > 0) {
                $matches[$matchId][] = $row;
            }
        }

        return $matches;
    }

    /** @return array<int,object> */
    public function getList(
        Registry $params,
        CMSApplicationInterface $app,
        int $limit,
        DatabaseInterface $db
    ): array {
        $now = $this->getNow($app);
        $timestamp = $now->getTimestamp();
        // Legacy parameters are not always constrained by their XML form limits.
        // Bound the query window to one day and the row count to 100 matches.
        $playtime = self::boundedNumber($params->get('playtime', 105), 105, 0, 1440);
        $limit = min(100, max(1, $limit));
        $timestampFrom = $timestamp - ($playtime * 60);
        $timestampTo = $timestamp + ($playtime * 60);

        $query = $db->createQuery()
            ->select([
                $db->quoteName('jl.id'),
                $db->quoteName('jl.name'),
                $db->quoteName('jl.game_regular_time'),
                $db->quoteName('jl.halftime'),
                $db->quoteName('jl.fav_team'),
                $db->quoteName('jco.alpha2'),
                $db->quoteName('jco.picture', 'country_picture'),
                $db->quoteName('jco.alpha3', 'countries_iso_code_3'),
                $db->quoteName('jm.id', 'match_id'),
                $db->quoteName('jm.match_date'),
                $db->quoteName('jm.projectteam1_id'),
                $db->quoteName('jm.projectteam2_id'),
                $db->quoteName('jm.team1_result'),
                $db->quoteName('jm.team2_result'),
                $db->quoteName('jm.team1_result_split'),
                $db->quoteName('jm.team2_result_split'),
                $db->quoteName('jt1.name', 'heim'),
                $db->quoteName('jt1.short_name', 'heim_short_name'),
                $db->quoteName('jt1.middle_name', 'heim_middle_name'),
                $db->quoteName('jt2.name', 'gast'),
                $db->quoteName('jt2.short_name', 'gast_short_name'),
                $db->quoteName('jt2.middle_name', 'gast_middle_name'),
                $db->quoteName('jc1.logo_big', 'wappenheim'),
                $db->quoteName('jc2.logo_big', 'wappengast'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'jl'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'jr')
                . ' ON ' . $db->quoteName('jr.project_id') . ' = ' . $db->quoteName('jl.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'jm')
                . ' ON ' . $db->quoteName('jm.round_id') . ' = ' . $db->quoteName('jr.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'jpt1')
                . ' ON ' . $db->quoteName('jpt1.id') . ' = ' . $db->quoteName('jm.projectteam1_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('jpt1.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 'jt1')
                . ' ON ' . $db->quoteName('jt1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'jc1')
                . ' ON ' . $db->quoteName('jc1.id') . ' = ' . $db->quoteName('jt1.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'jpt2')
                . ' ON ' . $db->quoteName('jpt2.id') . ' = ' . $db->quoteName('jm.projectteam2_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('jpt2.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 'jt2')
                . ' ON ' . $db->quoteName('jt2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'jc2')
                . ' ON ' . $db->quoteName('jc2.id') . ' = ' . $db->quoteName('jt2.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'jle')
                . ' ON ' . $db->quoteName('jle.id') . ' = ' . $db->quoteName('jl.league_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'jco')
                . ' ON ' . $db->quoteName('jco.alpha3') . ' = ' . $db->quoteName('jle.country')
            )
            ->where($db->quoteName('jm.match_timestamp') . ' >= :timestampFrom')
            ->where($db->quoteName('jm.match_timestamp') . ' <= :timestampTo')
            ->order($db->quoteName('jm.match_date') . ' ASC')
            ->bind(':timestampFrom', $timestampFrom, ParameterType::INTEGER)
            ->bind(':timestampTo', $timestampTo, ParameterType::INTEGER);

        $db->setQuery($query, 0, $limit);

        try {
            return $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Render escaped live-ticker markup from match and commentary data.
     * Applies module settings for competition names, flags, kickoff,
     * results and event details while keeping displayed values HTML safe.
     * @param array $list Prepared live-match records.
     * @param array $commentary Commentary mapped to match IDs.
     * @param Registry $params Live-ticker display preferences.
     * @param CMSApplicationInterface $app Joomla site application.
     * @return string HTML representing the live match list.
     */
    private function buildListHtml(
        array $list,
        array $commentary,
        Registry $params,
        CMSApplicationInterface $app
    ): string {
        $displayTitle = (bool) $params->get('display_title', 1);
        $displayLeagueName = (bool) $params->get('display_liganame', 0);
        $displayLeagueFlag = (bool) $params->get('display_ligaflagge', 0);
        $displayKickoff = (bool) $params->get('display_anstoss', 0);
        $displayFinalWhistle = (bool) $params->get('display_abpfiff', 0);
        $displayTeamLogo = (bool) $params->get('display_teamwappen', 0);
        $displayTeamName = (int) $params->get('display_teamname', 0);
        $displayLegs = (bool) $params->get('display_legs', 0);
        $tableClass = $this->escape((string) $params->get('table_class', 'table'));
        $html = '<div class="turtushout-entry"><div class="turtushout-name">';
        $html .= '<div class="table-responsive"><table class="' . $tableClass . '"><thead><tr>';
        $html .= '<td>aktuelle Zeit</td><td colspan="8">' . $this->escape($this->getNow($app)->format('H:i:s')) . '</td></tr>';

        if ($displayTitle) {
            $html .= '<tr>';

            if ($displayLeagueName || $displayLeagueFlag) {
                $html .= '<td colspan="' . (($displayLeagueName && $displayLeagueFlag) ? '2' : '1') . '">Liga</td>';
            }

            if ($displayKickoff) {
                $html .= '<td>Anpfiff</td>';
            }

            if ($displayFinalWhistle) {
                $html .= '<td>Abpfiff</td>';
            }

            if ($displayTeamLogo && $displayTeamName < 3) {
                $html .= '<td colspan="4">Paarung</td>';
            } elseif ($displayTeamLogo || $displayTeamName < 3) {
                $html .= '<td colspan="2">Paarung</td>';
            }

            $html .= '<td colspan="2">Ergebnis</td>';

            if ($displayLegs) {
                $html .= '<td>Sätze</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</thead><tbody>';

        foreach ($list as $match) {
            $kickoffParts = explode(' ', (string) ($match->match_date ?? ''));
            $kickoff = $kickoffParts[1] ?? '';
            $duration = ((int) ($match->game_regular_time ?? 0) + (int) ($match->halftime ?? 0)) * 60;
            $finalWhistle = $kickoff !== '' ? date('H:i:s', strtotime($kickoff) + $duration) : '';
            $homeLogo = trim((string) ($match->wappenheim ?? ''));
            $awayLogo = trim((string) ($match->wappengast ?? ''));

            $html .= '<tr>';

            if ($displayLeagueFlag) {
                $html .= '<td>' . $this->image((string) ($match->country_picture ?? ''), (string) ($match->countries_iso_code_3 ?? '')) . '</td>';
            }

            if ($displayLeagueName) {
                $html .= '<td>' . $this->escape((string) ($match->name ?? '')) . '</td>';
            }

            if ($displayKickoff) {
                $html .= '<td>' . $this->escape($kickoff) . '</td>';
            }

            if ($displayFinalWhistle) {
                $html .= '<td>' . $this->escape($finalWhistle) . '</td>';
            }

            if ($displayTeamLogo) {
                $html .= '<td>' . $this->image($homeLogo, (string) ($match->heim ?? ''), 20) . '</td>';
            }

            if ($displayTeamName < 3) {
                $html .= '<td>' . $this->escape($this->teamName($match, 'heim', $displayTeamName)) . '</td>';
            }

            if ($displayTeamLogo) {
                $html .= '<td>' . $this->image($awayLogo, (string) ($match->gast ?? ''), 20) . '</td>';
            }

            if ($displayTeamName < 3) {
                $html .= '<td>' . $this->escape($this->teamName($match, 'gast', $displayTeamName)) . '</td>';
            }

            $html .= '<td>' . $this->escape((string) ($match->team1_result ?? '')) . '</td>';
            $html .= '<td>' . $this->escape((string) ($match->team2_result ?? '')) . '</td>';

            if ($displayLegs) {
                $html .= '<td>' . $this->splitResults($match) . '</td>';
            }

            $html .= '</tr>';

            $matchId = (int) ($match->match_id ?? 0);

            if (isset($commentary[$matchId])) {
                $html .= '<tr><td colspan="9"><div class="overflow-auto" style="max-height:80px"><table class="table table-sm mb-0">';

                foreach ($commentary[$matchId] as $entry) {
                    $icon = HTMLHelper::image(
                        Uri::root() . 'media/com_sportsmanagement/jl_images/discuss_active.gif',
                        'Kommentar',
                        ['title' => 'Kommentar', 'loading' => 'lazy']
                    );
                    $html .= '<tr><td style="width:10%">' . $this->escape((string) ($entry->event_time ?? '')) . '</td>';
                    $html .= '<td style="width:10%">' . $icon . '</td>';
                    $html .= '<td style="width:80%">' . $this->escape((string) ($entry->notes ?? '')) . '</td></tr>';
                }

                $html .= '</table></div></td></tr>';
            }
        }

        return $html . '</tbody></table></div></div></div>';
    }

    /**
     * Select the requested team-name length for a ticker fixture.
     * Chooses full, middle or short names from the match record
     * according to the module's team display mode.
     * @param object $match Match record with team name variants.
     * @param string $side Team field prefix.
     * @param int $mode Requested name length.
     * @return string Selected team name.
     */
    private function teamName(object $match, string $side, int $mode): string
    {
        return match ($mode) {
            1 => (string) ($match->{$side . '_middle_name'} ?? ''),
            2 => (string) ($match->{$side . '_short_name'} ?? ''),
            default => (string) ($match->{$side} ?? ''),
        };
    }

    /**
     * Format a match's period-by-period results for display.
     * Combines the stored split-score lists for both participants
     * into a consistent text representation.
     * @param object $match Match row containing split result strings.
     * @return string Formatted split scores.
     */
    private function splitResults(object $match): string
    {
        $left = explode(';', (string) ($match->team1_result_split ?? ''));
        $right = explode(';', (string) ($match->team2_result_split ?? ''));
        $count = max(count($left), count($right));
        $parts = [];

        for ($i = 0; $i < $count; $i++) {
            $leftValue = $left[$i] ?? '';
            $rightValue = $right[$i] ?? '';

            if ($leftValue === '' && $rightValue === '') {
                continue;
            }

            $parts[] = $this->escape($leftValue !== '' ? $leftValue : '0')
                . ':' . $this->escape($rightValue !== '' ? $rightValue : '0');
        }

        return implode(' ', $parts);
    }

    /**
     * Render a safe HTML image element for the ticker.
     * Escapes the source and alt text, preserves an optional explicit
     * width and omits the element when no image URL was supplied.
     * @param string $src Safe image URL.
     * @param string $alt Alternative text for the image.
     * @param int|null $width Optional image width.
     * @return string Image HTML or empty string.
     */
    private function image(string $src, string $alt, ?int $width = null): string
    {
        if ($src === '') {
            return '';
        }

        $widthAttribute = $width !== null ? ' width="' . $width . '"' : '';

        return '<img src="' . $this->escape($src) . '" alt="' . $this->escape($alt) . '"'
            . $widthAttribute . ' loading="lazy">';
    }

    /**
     * Return the current instant in Joomla's configured timezone.
     * Falls back to UTC when stored timezone data is invalid so live
     * match-window calculations do not fail.
     * @param CMSApplicationInterface $app Active Joomla site application.
     * @return DateTimeImmutable Current timezone-aware instant.
     */
    private function getNow(CMSApplicationInterface $app): DateTimeImmutable
    {
        $timezone = (string) $app->get('offset', 'UTC');

        try {
            return new DateTimeImmutable('now', new DateTimeZone($timezone));
        } catch (\Throwable) {
            return new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }
    }

    /** Only local CSS filenames are allowed in module configuration. */
    private static function cssFile(mixed $value): string
    {
        if (!is_string($value) || $value === ''
            || !preg_match('/^[A-Za-z0-9_-]+\\.css$/D', $value)
        ) {
            return 'simple.css';
        }

        return $value;
    }

    /** Parse nonnegative numeric settings without partial or overflowing casts. */
    private static function boundedNumber(mixed $raw, int $default, int $min, int $max): int
    {
        if (!is_int($raw) && (!is_string($raw) || !preg_match('/^[0-9]+$/D', $raw))) {
            return $default;
        }

        $digits = ltrim((string) $raw, '0');
        $digits = $digits === '' ? '0' : $digits;
        $maximum = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return $max;
        }

        return min($max, max($min, (int) $digits));
    }

    /** Permit only database 0 or 1, including zero-padded legacy settings. */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || !preg_match('/^0*([01])$/D', $value, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Resolve the selected SportsManagement database for a ticker request.
     * Uses the validated selector and shared component resolver to
     * keep fixture and commentary lookups on the same connection.
     * @param int $selector Validated database selector.
     * @param DatabaseInterface $joomlaDatabase Joomla fallback connection.
     * @return DatabaseInterface Effective SportsManagement connection.
     */
    private function database(int $selector, DatabaseInterface $joomlaDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
    }

    /**
     * Resolve the Joomla module whose AJAX refresh was requested.
     * Rejects nonpositive IDs and checks that the module retrieved by
     * ModuleHelper matches the requested configured module.
     * @param int $moduleId Numeric Joomla module identifier.
     * @return object|null Module record or null if unavailable.
     */
    private function requestedModule(int $moduleId): ?object
    {
        if ($moduleId <= 0) {
            return null;
        }

        // Joomla's ModuleHelper compares the stored module ID to a string strictly.
        // Passing an integer makes an otherwise valid AJAX refresh look missing.
        $module = ModuleHelper::getModuleById((string) $moduleId);

        if (
            !is_object($module)
            || (int) ($module->id ?? 0) !== $moduleId
            || (string) ($module->module ?? '') !== 'mod_sportsmanagement_liveticker'
        ) {
            return null;
        }

        return $module;
    }

    /**
     * Escape a database-derived string before insertion into ticker HTML.
     * Uses HTML entity encoding with substitute handling for malformed
     * UTF-8 input, avoiding HTML injection from club and event fields.
     * @param string $value Text needing safe HTML presentation.
     * @return string Escaped UTF-8 HTML text.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
