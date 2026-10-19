<?php
/**
 * Joomla 5/6-native data helper for the SportsManagement birthday module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementBirthday\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class BirthdayHelper
{
    private const MAX_FILTER_IDS = 100;
    private const MAX_FILTER_VALUES = 200;
    private const MAX_FILTER_VALUE_LENGTH = 4096;

    public function getData(
        Registry $params,
        Registry $componentParams,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $databaseSelector = $this->normaliseDatabaseSelector($params->get(
            'cfg_which_database',
            $componentParams->get('cfg_which_database', 0)
        ));
        $selectedProjects = $params->get('p', []);
        $projectIds = $this->normaliseIds($selectedProjects);
        $selectedTeams = $params->get('teams', []);
        $favoriteTeams = (bool) $params->get('use_fav', 0);

        // A malformed configured filter must not turn into a query returning
        // birthdays from every project or team. Unset/zero means unfiltered.
        if ((self::hasSelection($selectedProjects) && !$projectIds)
            || (!$favoriteTeams && self::hasSelection($selectedTeams)
                && !$this->normaliseIds($selectedTeams))
            || ($favoriteTeams && !$projectIds)) {
            return $this->emptyData($params);
        }

        try {
            $db = $this->database($databaseSelector, $fallbackDatabase);
            $teamIds = $this->resolveTeamIds($db, $params, $projectIds);

            if ($favoriteTeams && !$teamIds) {
                return $this->emptyData($params);
            }

            $personTypes = match ((int) $params->get('use_which', 0)) {
                1 => [1],
                2 => [2],
                default => [1, 2],
            };

            $query = $db->createQuery()
                ->select([
                    $db->quoteName('p.id'),
                    $db->quoteName('p.birthday'),
                    $db->quoteName('p.firstname'),
                    $db->quoteName('p.nickname'),
                    $db->quoteName('p.lastname'),
                    $db->quoteName('p.alias', 'person_alias'),
                    $db->quoteName('p.picture', 'default_picture'),
                    $db->quoteName('p.country'),
                    $db->quoteName('stp.picture'),
                    $db->quoteName('stp.persontype', 'type'),
                    $db->quoteName('st.team_id'),
                    $db->quoteName('pt.project_id'),
                    $db->quoteName('t.name', 'team_name'),
                    $db->quoteName('t.alias', 'team_alias'),
                    $db->quoteName('pro.alias', 'project_alias'),
                    $db->quoteName('s.id', 'season_id'),
                    $db->quoteName('s.alias', 'season_alias'),
                    $db->quoteName('co.alpha2', 'country_alpha2'),
                    $db->quoteName('co.picture', 'country_picture'),
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 'p'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.person_id') . ' = ' . $db->quoteName('p.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('stp.season_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro') . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id') . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('st.season_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('pro.season_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('p.country'))
                ->where($db->quoteName('p.published') . ' = 1')
                ->where($db->quoteName('p.birthday') . ' <> ' . $db->quote('0000-00-00'))
                ->whereIn($db->quoteName('stp.persontype'), $personTypes, ParameterType::INTEGER);

            $position = $db->createQuery()
                ->select($db->quoteName('pos.name'))
                ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_person_project_position', 'ppp') . ' ON ' . $db->quoteName('ppp.project_position_id') . ' = ' . $db->quoteName('ppos.id'))
                ->where($db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('p.id'))
                ->where($db->quoteName('ppp.project_id') . ' = ' . $db->quoteName('pro.id'))
                ->where($db->quoteName('ppp.persontype') . ' = ' . $db->quoteName('stp.persontype'));
            $position->setLimit(1);
            $query->select('(' . $position . ') AS ' . $db->quoteName('position_name'));

            $ageGroupId = max(0, (int) $params->get('agegrouplist', 0));
            if ($ageGroupId > 0) {
                $query->where($db->quoteName('p.agegroup_id') . ' = :ageGroupId')
                    ->bind(':ageGroupId', $ageGroupId, ParameterType::INTEGER);
            }
            if ($projectIds) {
                $query->whereIn($db->quoteName('pt.project_id'), $projectIds, ParameterType::INTEGER);
            }
            if ($teamIds) {
                $query->whereIn($db->quoteName('st.team_id'), $teamIds, ParameterType::INTEGER);
            }
            $query
                ->order($db->quoteName('p.lastname') . ' ASC')
                ->order($db->quoteName('p.firstname') . ' ASC')
                ->order($db->quoteName('p.id') . ' ASC');

            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            // Database and SQL details belong in the administrator log, not in
            // messages rendered to public site visitors.
            Log::add('SportsManagement Birthday query failed: ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');
            $rows = [];
        }

        $today = $this->today($app);
        $maxDays = self::normaliseDaysLimit($params->get('maxdays', ''));
        $people = [];

        foreach ($rows as $row) {
            $birth = $this->date((string) $row->birthday, $today->getTimezone());
            // Ignore invalid or future dates of birth rather than displaying
            // negative ages from inconsistent legacy records.
            if (!$birth || $birth > $today) {
                continue;
            }

            $birthday = $this->nextBirthday($birth, $today);
            $days = (int) $today->diff($birthday)->format('%a');
            if ($maxDays !== null && $days > $maxDays) {
                continue;
            }

            $key = (int) $row->id;
            if (isset($people[$key])) {
                continue;
            }

            $row->date_of_birth = $birth->format('Y-m-d');
            $row->daymonth = $birth->format('m-d');
            $row->year = $birthday->format('Y');
            $row->days_to_birthday = $days;
            $row->age = (int) $birthday->format('Y') - (int) $birth->format('Y');
            $row->display_name = $this->displayName($row, (int) $params->get('name_format', 0));
            $row->person_link = $this->personLink($row, $databaseSelector);
            $row->picture_url = $this->pictureUrl($row, (bool) $params->get('show_picture', 1));
            $row->flag_html = (bool) $params->get('show_player_flag', 0) ? $this->flagHtml($row, $componentParams) : '';
            $row->birthday_text = $this->birthdayText($row, $birthday, $birth, $params);
            $people[$key] = (array) $row;
        }

        $people = self::sortPersons(array_values($people), $params->get('sort_order', '-'), false);
        $limit = max(0, (int) $params->get('limit', 0));
        if ($limit > 0) {
            $people = array_slice($people, 0, $limit);
        }

        return [
            'persons' => $people,
            'mode' => strtoupper((string) $params->get('mode', 'L')),
            'pictureServer' => Uri::root(),
        ];
    }

    /**
     * Reject arrays, booleans, signed values and invalid numbers instead of
     * letting PHP cast them into an unintended external database selector.
     */
    private function normaliseDatabaseSelector(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        $selector = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $selector === false ? 0 : $selector;
    }

    /** Keep the module layout contract even when filtering produces no rows. */
    private function emptyData(Registry $params): array
    {
        return [
            'persons' => [],
            'mode' => strtoupper((string) $params->get('mode', 'L')),
            'pictureServer' => Uri::root(),
        ];
    }

    /** Distinguish an explicitly invalid filter from a disabled filter. */
    private static function hasSelection(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return false;
        }

        if (!is_array($value)) {
            return true;
        }

        foreach ($value as $entry) {
            if (self::hasSelection($entry)) {
                return true;
            }
        }

        return false;
    }

    /** Preserve the historical jsm_birthday_sort() ordering contract. */
    public static function sortPersons(array $rows, $arguments = '-', bool $keys = true): array
    {
        $descendingAge = (string) $arguments === '-';
        usort($rows, static function (array $a, array $b) use ($descendingAge): int {
            $days = ((int) ($a['days_to_birthday'] ?? 0)) <=> ((int) ($b['days_to_birthday'] ?? 0));
            return $days !== 0 ? $days : ($descendingAge
                ? ((int) ($b['age'] ?? 0) <=> (int) ($a['age'] ?? 0))
                : ((int) ($a['age'] ?? 0) <=> (int) ($b['age'] ?? 0)));
        });

        return $rows;
    }


    /**
     * Parse the optional days filter without silently turning invalid input into zero.
     *
     * Empty or malformed limits mean no filter. Zero explicitly means today only.
     */
    private static function normaliseDaysLimit(mixed $value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        // Filter out integers beyond the platform range before converting to int.
        $decimal = ltrim($raw, '0');
        $decimal = $decimal === '' ? '0' : $decimal;
        $max = (string) PHP_INT_MAX;

        if (strlen($decimal) > strlen($max)
            || (strlen($decimal) === strlen($max) && strcmp($decimal, $max) > 0)) {
            return null;
        }

        return (int) $decimal;
    }

    private function resolveTeamIds(DatabaseInterface $db, Registry $params, array $projectIds): array
    {
        if (!(bool) $params->get('use_fav', 0)) {
            return $this->normaliseIds($params->get('teams', []));
        }
        if (!$projectIds) {
            return [];
        }

        $query = $db->createQuery()
            ->select($db->quoteName('fav_team'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->whereIn($db->quoteName('id'), $projectIds, ParameterType::INTEGER);

        try {
            return $this->normaliseIds($db->setQuery($query)->loadColumn() ?: []);
        } catch (\Throwable) {
            return [];
        }
    }

    private function normaliseIds(mixed $values): array
    {
        $values = is_array($values) ? $values : [$values];
        $ids = [];

        // Module parameters and optional project favourite-team fields can
        // contain arbitrarily long selections. Bound the query filter size.
        foreach (array_slice($values, 0, self::MAX_FILTER_VALUES) as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            if (is_string($value) && strlen($value) > self::MAX_FILTER_VALUE_LENGTH) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                // Only canonical positive decimal IDs, not "+12", "12junk",
                // zero, negative numbers or integer-overflowing strings.
                if (!preg_match('/^[1-9][0-9]*$/D', $part)) {
                    continue;
                }

                $id = filter_var($part, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;

                    if (count($ids) >= self::MAX_FILTER_IDS) {
                        return array_values($ids);
                    }
                }
            }
        }

        return array_values($ids);
    }

    private function today(CMSApplicationInterface $app): \DateTimeImmutable
    {
        try {
            $timezone = new \DateTimeZone((string) $app->get('offset', 'UTC'));
        } catch (\Throwable) {
            $timezone = new \DateTimeZone('UTC');
        }
        return new \DateTimeImmutable('today', $timezone);
    }

    private function date(string $value, \DateTimeZone $timezone): ?\DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($value), $timezone);
        // PHP normalises invalid calendar dates (for example 2025-02-31).
        // Reject those instead of displaying a birthday on the wrong day.
        return $date instanceof \DateTimeImmutable
            && (int) $date->format('Y') > 0
            && $date->format('Y-m-d') === trim($value)
            ? $date
            : null;
    }

    private function nextBirthday(\DateTimeImmutable $birth, \DateTimeImmutable $today): \DateTimeImmutable
    {
        $year = (int) $today->format('Y');
        $month = (int) $birth->format('m');
        $day = (int) $birth->format('d');
        $birthday = $today->setDate($year, $month, $day);

        if ($birthday < $today) {
            // Rebuild from the birth month/day, not an already normalised date:
            // a February 29 birthday becomes March 1 in non-leap years but
            // must return to February 29 when the next year is a leap year.
            $birthday = $today->setDate($year + 1, $month, $day);
        }

        return $birthday;
    }

    private function displayName(object $row, int $format): string
    {
        return PersonNameFormatter::format(
            null,
            (string) $row->firstname,
            (string) $row->nickname,
            (string) $row->lastname,
            $format
        );
    }

    private function personLink(object $row, int $databaseSelector): string
    {
        $view = match ((int) $row->type) { 2 => 'staff', 3 => 'referee', default => 'player' };
        $query = [
            'cfg_which_database' => $databaseSelector,
            's' => $this->slug((int) ($row->season_id ?? 0), (string) ($row->season_alias ?? '')),
            'p' => $this->slug((int) ($row->project_id ?? 0), (string) ($row->project_alias ?? '')),
            'pid' => $this->slug((int) ($row->id ?? 0), (string) ($row->person_alias ?? '')),
        ];
        if ((int) $row->type !== 3) {
            $query['tid'] = $this->slug((int) ($row->team_id ?? 0), (string) ($row->team_alias ?? ''));
        }

        return SiteRouteHelper::view($view, $query);
    }

    private function slug(int $id, string $alias): string
    {
        return $id > 0 ? $id . ':' . $alias : '';
    }

    private function pictureUrl(object $row, bool $show): string
    {
        if (!$show) {
            return '';
        }

        $root = realpath(JPATH_ROOT);

        if ($root === false) {
            return '';
        }

        foreach ([(string) ($row->picture ?? ''), (string) ($row->default_picture ?? '')] as $candidate) {
            $path = $this->localImagePath($candidate);

            if ($path === '') {
                continue;
            }

            // Existing image files must also resolve inside the Joomla root;
            // reject directory traversal and symbolic links to external files.
            $absolute = realpath($root . DIRECTORY_SEPARATOR . $path);

            if ($absolute !== false
                && str_starts_with($absolute, $root . DIRECTORY_SEPARATOR)
                && is_file($absolute)
            ) {
                return Uri::root() . $path;
            }
        }

        return '';
    }

    private function localImagePath(string $candidate): string
    {
        $candidate = trim($candidate);

        if ($candidate === ''
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $candidate)
            || str_starts_with($candidate, '//')
            || strpbrk($candidate, '?#%') !== false
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $candidate)
            || preg_match('~(?:^|/)\.\.(?:/|$)~', $candidate)
        ) {
            return '';
        }

        return ltrim($candidate, '/');
    }

    private function flagHtml(object $row, Registry $params): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->country_alpha2 ?? ''));
        $alpha2 = preg_match('/^[a-z]{2}$/', $alpha2) ? $alpha2 : '';
        $label = htmlspecialchars($alpha3, ENT_QUOTES, 'UTF-8');
        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $css = match ($alpha3) { 'WAL' => 'gb-wls', 'SCO' => 'gb-sct', 'GBR' => 'gb-eng', default => $alpha2 };
            return $css !== '' ? '<span class="fi fi-' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '" title="' . $label . '"></span>' : '';
        }
        $candidate = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (string) ($row->country_picture ?? $params->get('ph_flags', ''));
        $path = $this->localImagePath($candidate);

        return $path === '' ? '' : '<img src="' . htmlspecialchars(Uri::root() . $path, ENT_QUOTES, 'UTF-8') . '" alt="' . $label . '" />';
    }

    private function birthdayText(object $row, \DateTimeImmutable $birthday, \DateTimeImmutable $birth, Registry $params): string
    {
        $when = match ((int) $row->days_to_birthday) {
            0 => (string) $params->get('todaymessage', ''),
            1 => (string) $params->get('tomorrowmessage', ''),
            default => str_replace('%DAYS_TO%', (string) $row->days_to_birthday, trim((string) $params->get('futuremessage', ''))),
        };
        return strtr(
            htmlspecialchars(trim(Text::_((string) $params->get('birthdaytext', ''))), ENT_QUOTES, 'UTF-8'),
            [
                '%WHEN%' => htmlspecialchars($when, ENT_QUOTES, 'UTF-8'),
                '%AGE%' => (string) $row->age,
                '%DATE%' => htmlspecialchars($birthday->format((string) $params->get('dayformat', 'd.m.Y')), ENT_QUOTES, 'UTF-8'),
                '%DATE_OF_BIRTH%' => htmlspecialchars($birth->format((string) $params->get('birthdayformat', 'd.m.Y')), ENT_QUOTES, 'UTF-8'),
                '%BR%' => '<br />',
                '%BOLD%' => '<strong>',
                '%BOLDEND%' => '</strong>',
            ]
        );
    }

    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        try {
            return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
        } catch (\Throwable) {
            return $fallbackDatabase;
        }
    }
}
