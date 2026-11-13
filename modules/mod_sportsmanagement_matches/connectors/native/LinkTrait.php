<?php
/**
 * Joomla 5/6 SportsManagement matches module PHP implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

trait NativeLinkTrait
{
    /**
     * Build the configured internal and external links for one team.
     * Select teamplan, ranking curve, team information, roster and club URLs
     * from Joomla's routing helper; include validated club websites when enabled.
     * Never generate internal team or club links with missing positive IDs.
     * Validate module flags and labels before constructing URLs.
     * @param object $match Fixture row containing project, team and club IDs.
     * @param string $n Team position, 1 for home or 2 for away.
     * @param Registry $params Module link visibility and label settings.
     * @return array<int,array{label:string,url:string,external:bool}> Team links.
     */
    private function teamLinks(object $match, string $n, Registry $params): array
    {
        $mode = self::boundedMatchesOption($params->get('link_teams', 0), 0, 10);
        if ($mode <= 0) {
            return [];
        }
        $teamId = self::boundedMatchesId($match->{'team' . $n . '_id'} ?? null);
        $clubId = self::boundedMatchesId($match->{'club' . $n . '_id'} ?? null);
        $base = ['p' => (string) $match->project_slug, 'tid' => $teamId, 'cfg_which_database' => $this->validatedMatchesDatabase($params)];
        $links = [];
        foreach ([
            'plan' => ['teamplan', 'link_team_plan_text', 'Schedule'],
            'curve' => ['curve', 'link_team_curve_text', 'Fever Chart'],
            'teaminfo' => ['teaminfo', 'link_team_teaminfo_text', 'Team Info'],
            'roster' => ['roster', 'link_team_roster_text', 'Roster'],
            'club' => ['clubinfo', 'link_team_club_text', 'Club'],
        ] as $key => [$view, $textKey, $fallback]) {
            if (self::boundedMatchesOption($params->get('link_team_' . $key, 0), 0, 1) !== 1) {
                continue;
            }
            $query = $base;
            if ($key === 'club') {
                if ($clubId <= 0) {
                    continue;
                }
                unset($query['tid']);
                $query['cid'] = $clubId;
            } elseif ($teamId <= 0) {
                continue;
            }
            $links[] = ['label' => self::matchesLinkLabel($params->get($textKey, $fallback), $fallback), 'url' => $this->route($view, $query), 'external' => false];
        }
        $website = $this->externalWebsiteUrl((is_string($match->{'club' . $n . '_website'} ?? null) ? $match->{'club' . $n . '_website'} : ''));
        if (self::boundedMatchesOption($params->get('link_team_www', 0), 0, 1) === 1 && $website !== '') {
            $links[] = ['label' => self::matchesLinkLabel($params->get('link_team_www_text', 'WWW'), 'WWW'), 'url' => $website, 'external' => true];
        }
        return $links;
    }


    /**
     * Validate a club's stored website before placing it in public HTML.
     * Accept only HTTP(S) links, supply HTTPS for hostnames without a
     * scheme and reject credentials, control characters or unsafe protocols.
     * @param string $value Website address stored on the club record.
     * @return string Valid website URL, or an empty string when rejected.
     */
    private function externalWebsiteUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '' || preg_match('/[\x00-\x20\x7F]/', $value)) {
            return '';
        }

        if (!preg_match('~^https?://~i', $value)) {
            if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $value) || str_starts_with($value, '//')) {
                return '';
            }

            $value = 'https://' . $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $parts = parse_url($value);

        // Login details in a publicly rendered link are not ordinary club
        // websites and may expose credentials or produce misleading links.
        return is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            && !empty($parts['host'])
            && !isset($parts['user'])
            && !isset($parts['pass'])
            ? $value
            : '';
    }

    /**
     * Create report, statistics and next-match links for a fixture.
     * Respect each configured feature flag and keep the project and database
     * selectors in the resulting Joomla component routes.
     * @param object $match Match row with route identifiers.
     * @param Registry $params Link visibility and label settings.
     * @return array<int,array{label:string,url:string}> Fixture navigation links.
     */
    private function matchLinks(object $match, Registry $params): array
    {
        $links = [];
        $common = ['p' => (string) $match->project_slug, 'cfg_which_database' => $this->validatedMatchesDatabase($params)];
        if (self::boundedMatchesOption($params->get('show_act_report_link', 0), 0, 1) === 1 && self::boundedMatchesOption($match->show_report ?? 0, 0, 1) === 1) {
            $links[] = ['label' => self::matchesLinkLabel($params->get('show_act_report_text', 'Show Report'), 'Show Report'), 'url' => $this->route('matchreport', $common + ['mid' => (string) $match->match_slug])];
        }
        if (self::boundedMatchesOption($params->get('show_statistic_link', 0), 0, 1) === 1) {
            $links[] = ['label' => self::matchesLinkLabel($params->get('statistic_link_text', 'Season statistics'), 'Season statistics'), 'url' => $this->route('stats', $common)];
        }
        if (self::boundedMatchesOption($params->get('show_nextmatch_link', 0), 0, 1) === 1) {
            $links[] = ['label' => self::matchesLinkLabel($params->get('nextmatch_link_text', 'Nextmatch'), 'Nextmatch'), 'url' => $this->route('nextmatch', $common + ['mid' => (string) $match->match_slug])];
        }
        return $links;
    }

    /**
     * Find the previous and next fixture relative to the current match.
     * Issue bounded queries in chronological order, optionally restricted
     * to the same project, then build match-report links with route slugs.
     * Use the Joomla 5/6 createQuery() factory instead of deprecated
     * getQuery(true) parameter semantics. Invalid parent and neighbour IDs
     * are discarded before generating public Joomla routes. Validates and
     * binds the match timestamp and project filter as SQL parameters.
     * @param DatabaseInterface $db Resolved database connection.
     * @param object $match Current match and timestamp metadata.
     * @param Registry $params Neighbour navigation configuration.
     * @param array<int,int> $projectIds Validated selected project identifiers.
     * @return array{previous:?array{label:string,url:string},next:?array{label:string,url:string}} Neighbour links.
     */
    private function neighborLinks(DatabaseInterface $db, object $match, Registry $params, array $projectIds): array
    {
        $out = ['previous' => null, 'next' => null];
        $projectId = self::boundedMatchesId($match->project_id ?? null);
        $timestamp = self::validatedMatchesTimestamp($match->match_timestamp ?? null);
        if ($projectId <= 0 || $timestamp === null || !$projectIds) {
            return $out;
        }

        foreach (['previous' => ['<', 'DESC', self::matchesLinkLabel($params->get('last_text', '<<'), '<<')], 'next' => ['>', 'ASC', self::matchesLinkLabel($params->get('next_text', '>>'), '>>')]] as $key => [$operator, $direction, $label]) {
            $q = $db->createQuery()
                ->select(['m.id', 'p.id AS project_id', 'p.alias AS project_alias', 't1.alias AS team1_alias', 't2.alias AS team2_alias'])
                ->from('#__sportsmanagement_match AS m')
                ->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id')
                ->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id')
                ->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id')
                ->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id')
                ->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id')
                ->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id')
                ->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id')
                ->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id')
                ->where('m.published = 1')
                ->where($db->quoteName('m.match_timestamp') . ' ' . $operator . ' :neighborTimestamp')
                ->bind(':neighborTimestamp', $timestamp, ParameterType::INTEGER)
                ->order('m.match_timestamp ' . $direction);

            if (self::boundedMatchesOption($params->get('nextlast_from_same_project', 1), 1, 1) === 1) {
                $q->where($db->quoteName('r.project_id') . ' = :neighborProjectId')
                    ->bind(':neighborProjectId', $projectId, ParameterType::INTEGER);
            } else {
                $q->whereIn($db->quoteName('r.project_id'), $projectIds, ParameterType::INTEGER);
            }
            $db->setQuery($q, 0, 1);
            $neighbor = $db->loadObject();
            if (is_object($neighbor)) {
                $neighborProjectId = self::boundedMatchesId($neighbor->project_id ?? null);
                $neighborMatchId = self::boundedMatchesId($neighbor->id ?? null);

                if ($neighborProjectId <= 0 || $neighborMatchId <= 0) {
                    continue;
                }

                $projectAlias = $neighbor->project_alias ?? '';
                $homeAlias = $neighbor->team1_alias ?? '';
                $awayAlias = $neighbor->team2_alias ?? '';
                $p = $neighborProjectId . ':' . (is_string($projectAlias) ? $projectAlias : '');
                $mid = $neighborMatchId . ':' . trim(
                    (is_string($homeAlias) ? $homeAlias : '')
                    . '_' . (is_string($awayAlias) ? $awayAlias : ''),
                    '_'
                );
                $out[$key] = ['label' => $label, 'url' => $this->route('matchreport', ['p' => $p, 'mid' => $mid, 'cfg_which_database' => $this->validatedMatchesDatabase($params)])];
            }
        }
        return $out;
    }

    /**
     * Build a project-scoped route for a configurable header link.
     * Pass the selected view and any extra route parameters through the
     * common Joomla route helper with current project and database settings.
     * @param string $view Target SportsManagement site view.
     * @param object $match Match providing the project route slug.
     * @param array<string,mixed> $extra Additional route arguments.
     * @param Registry $params Component database selection.
     * @return string Routed component URL.
     */
    private function componentRoute(string $view, object $match, array $extra, Registry $params): string
    {
        return $this->route($view, ['p' => (string) $match->project_slug, 'cfg_which_database' => $this->validatedMatchesDatabase($params)] + $extra);
    }

    /**
     * Validate the match timestamp before binding a neighbour query.
     *
     * Only nonnegative integer timestamps and complete decimal strings are
     * accepted. Rejects floats, scientific notation, arrays and overflow.
     *
     * @param mixed $value Stored fixture timestamp.
     * @return int|null Safe Unix timestamp or null for invalid input.
     */
    private static function validatedMatchesTimestamp(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || strlen($value) > 256
            || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return null;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $timestamp = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $timestamp === false ? null : $timestamp;
    }

    /**
     * Read the database selector used for match navigation links.
     *
     * Rejects malformed module values instead of generating routes that
     * silently point to a different SportsManagement database.
     *
     * @param Registry $params Configured match module database.
     * @return int Validated nonnegative database selector.
     * @throws \InvalidArgumentException When the selector is malformed.
     */
    private function validatedMatchesDatabase(Registry $params): int
    {
        $raw = $params->get('cfg_which_database', 0);

        if (!is_int($raw) && (!is_string($raw) || strlen($raw) > 256
            || preg_match('/^[0-9]+$/D', $raw) !== 1)) {
            throw new \InvalidArgumentException('Invalid SportsManagement Matches database selector.', 400);
        }

        if (is_string($raw)) {
            $digits = ltrim($raw, '0');
            $raw = $digits === '' ? '0' : $digits;
        }

        $selector = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($selector === false) {
            throw new \InvalidArgumentException('Invalid SportsManagement Matches database selector.', 400);
        }

        return $selector;
    }

    /**
     * Normalise a configured link label before template rendering.
     *
     * Discards structured values, unusually long labels and control
     * characters without coercing arrays to strings on PHP 8.
     *
     * @param mixed $value Submitted or configured label.
     * @param string $fallback Safe built-in label.
     * @return string Display label without invalid control characters.
     */
    private static function matchesLinkLabel(mixed $value, string $fallback): string
    {
        return is_string($value) && strlen($value) <= 200
            && !preg_match('/[\x00-\x1F\x7F]/', $value)
            ? $value : $fallback;
    }

    /**
     * Delegate a frontend link to the native component routing service.
     * Keep all match-module links consistent with the Joomla 5/6 SEF router.
     * @param string $view Destination frontend view name.
     * @param array<string,mixed> $query Values added to the view route.
     * @return string Joomla-routed frontend URL.
     */
    private function route(string $view, array $query): string
    {
        return SiteRouteHelper::view($view, $query);
    }
}
