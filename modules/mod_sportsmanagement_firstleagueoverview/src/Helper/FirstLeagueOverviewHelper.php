<?php
/**
 * Native Joomla 5/6 first league overview module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class FirstLeagueOverviewHelper
{
    /**
     * @return array{projects: array<int,object>, federations: array<int,object>}
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        try {
            $db = SportsManagementDatabaseResolver::resolve(
                $fallbackDatabase,
                self::databaseSelector($params->get('cfg_which_database', 0))
            );
            $componentParams = ComponentHelper::getParams('com_sportsmanagement');
            $projects = $this->latestProjects($db);

            foreach ($projects as $project) {
                $project->ranking_link = $this->rankingLink($project, $params);
                $project->country_label = Text::_((string) ($project->country_name ?: $project->country));
                $project->flag_html = $this->flagHtml($project, $componentParams);
            }

            return [
                'projects' => $projects,
                'federations' => self::visibleFederations($projects, $this->getFederations($db)),
            ];
        } catch (\Throwable $e) {
            // Missing tables and optional external database failures must
            // not disclose SQL details or break the whole frontend page.
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');

            return ['projects' => [], 'federations' => []];
        }
    }

    /** @return array<int,object> */
    private function latestProjects(DatabaseInterface $db): array
    {
        $championsComplete = 1;
        $leagueLevels = [1, 41];
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.name'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.league_id'),
                $db->quoteName('l.country'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('c.alpha2'),
                $db->quoteName('c.name', 'country_name'),
                $db->quoteName('c.picture', 'country_picture'),
                $db->quoteName('c.federation'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'c')
                . ' ON ' . $db->quoteName('c.alpha3') . ' = ' . $db->quoteName('l.country')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('l.champions_complete') . ' = :championsComplete')
            ->bind(':championsComplete', $championsComplete, ParameterType::INTEGER)
            ->whereIn($db->quoteName('l.league_level'), $leagueLevels, ParameterType::INTEGER)
            ->order([
                $db->quoteName('l.country') . ' ASC',
                $db->quoteName('l.name') . ' ASC',
                // A project name is not a reliable indication of recency.
                $db->quoteName('p.season_id') . ' DESC',
                $db->quoteName('p.id') . ' DESC',
            ]);

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $projects = [];
        $seenLeagues = [];

        foreach ($rows as $row) {
            $leagueId = (int) ($row->league_id ?? 0);
            if ($leagueId <= 0 || isset($seenLeagues[$leagueId])) {
                continue;
            }

            $seenLeagues[$leagueId] = true;
            // Build the Joomla ID:alias slug in PHP instead of relying on
            // a database-specific CONCAT_WS expression.
            $row->project_slug = (int) $row->id . ':' . (string) ($row->alias ?? '');
            $projects[] = $row;
        }

        return $projects;
    }

    /** @return array<int,object> */
    private function getFederations(DatabaseInterface $db): array
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        $result = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $picture = $row->picture ?? '';
            $row->picture_url = self::mediaUrl(is_string($picture) ? $picture : '');
            $result[(int) $row->id] = $row;
        }

        return $result;
    }

    /**
     * Return only federation tabs which actually contain projects. Unassigned
     * countries and references to deleted federations must not disappear.
     *
     * @param array<int, object> $projects
     * @param array<int, object> $federations
     *
     * @return array<int, object>
     */
    public static function visibleFederations(array $projects, array $federations): array
    {
        $usedIds = [];

        foreach ($projects as $project) {
            $id = max(0, (int) ($project->federation ?? 0));
            $usedIds[$id] = true;
        }

        $visible = [];

        foreach ($federations as $id => $federation) {
            if (isset($usedIds[(int) $id])) {
                $visible[(int) $id] = $federation;
            }
        }

        foreach ($usedIds as $id => $_) {
            if (isset($visible[$id])) {
                continue;
            }

            $visible[$id] = (object) [
                'id' => $id,
                'name' => $id === 0
                    ? Text::_('JNONE')
                    : Text::_('JUNKNOWN') . ' (' . $id . ')',
                'picture_url' => '',
            ];
        }

        return $visible;
    }

    private function rankingLink(object $project, Registry $params): string
    {
        return SiteRouteHelper::view('ranking', [
            'cfg_which_database' => self::databaseSelector($params->get('cfg_which_database', 0)),
            's' => (int) ($project->season_id ?? 0),
            'p' => (string) ($project->project_slug ?? $project->id ?? ''),
            'type' => 0,
            'r' => 0,
            'from' => 0,
            'to' => 0,
            'division' => 0,
        ]);
    }

    private function flagHtml(object $row, Registry $componentParams): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->alpha2 ?? ''));
        $alpha3 = preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : '';
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $label = htmlspecialchars((string) ($row->country_label ?? $alpha3), ENT_QUOTES, 'UTF-8');

        if ((int) $componentParams->get('cfg_flags_css', 0) === 1) {
            $cssCode = match ($alpha3) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $cssCode !== ''
                ? '<span class="fi fi-' . htmlspecialchars($cssCode, ENT_QUOTES, 'UTF-8') . '" title="' . $label . '"></span>'
                : '';
        }

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (string) ($row->country_picture ?? $componentParams->get('ph_flags', ''));

        if ($path === '') {
            return '';
        }

        $url = self::mediaUrl($path);

        if ($url === '') {
            return '';
        }

        return '<img src="'
            . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
            . '" alt="' . $label . '" title="' . $label . '" loading="lazy" />';
    }

    /** Keep malformed legacy config values from silently selecting database 1. */
    private static function databaseSelector(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $selector = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $selector === false ? 0 : $selector;
    }

    /**
     * Build HTTP(S) or site-local image URLs without protocol-relative paths,
     * userinfo, traversal, encoded control bytes or executable URI schemes.
     */
    private static function mediaUrl(string $path): string
    {
        $path = trim($path);

        if ($path === ''
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || str_contains($path, chr(92))
            || str_starts_with($path, '//')) {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            $parts = parse_url($path);

            return filter_var($path, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                    ? $path
                    : '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)) {
            return '';
        }

        $local = ltrim($path, '/');
        $decoded = $local;

        for ($depth = 0; $depth < 3; $depth++) {
            if (preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)
                || preg_match('/[\x00-\x1F\x7F]/', $decoded)
                || str_contains($decoded, chr(92))
                || str_contains($decoded, '?')
                || str_contains($decoded, '#')) {
                return '';
            }

            $next = rawurldecode($decoded);

            if ($next === $decoded) {
                break;
            }

            $decoded = $next;
        }

        return rtrim((string) Uri::root(), '/') . '/' . $local;
    }
}
