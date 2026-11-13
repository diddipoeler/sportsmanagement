<?php
/**
 * Native Joomla 5/6 club birthday helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Throwable;

final class ClubBirthdayHelper
{
    /**
     * Prepare club anniversaries for the native Joomla birthday module.
     * Validates mode and season settings, resolves the chosen database,
     * loads eligible clubs and formats anniversary information for the
     * frontend layout without expanding malformed season selections.
     * Invalid database selectors are rejected before opening a connection;
     * malformed numeric display limits use conservative defaults.
     * @param Registry $params Club birthday module options.
     * @param CMSApplicationInterface $app Current Joomla site application.
     * @param DatabaseInterface $fallbackDatabase Default Joomla database.
     * @return array Module presentation data and anniversary entries.
     */
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $rawMode = $params->get('mode', 'L');
        $mode = is_string($rawMode) && strtoupper($rawMode) === 'BC' ? 'BC' : 'L';

        $seasonSelection = $params->get('s', []);

        // An explicitly configured but invalid season selection must not
        // silently become an unfiltered query across all clubs.
        if (self::hasSeasonSelection($seasonSelection) && self::normaliseIds($seasonSelection) === []) {
            return ['clubs' => [], 'mode' => $mode];
        }

        $databaseSelector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($databaseSelector === null) {
            return ['clubs' => [], 'mode' => $mode];
        }

        try {
            $timezone = self::timezone($app);
            $database = SportsManagementDatabaseResolver::resolve(
                $fallbackDatabase,
                $databaseSelector
            );
            $clubs = $this->getClubs(
                self::nonNegativeOption($params->get('limit', 0)),
                self::nonNegativeOption($params->get('maxdays', 0)),
                $seasonSelection,
                $database,
                $timezone,
                self::sortOrder($params->get('sort_order', -1))
            );

            foreach ($clubs as $club) {
                $club->birthday_text = self::birthdayText($club, $params, $timezone);
            }
        } catch (Throwable $e) {
            // Optional external databases and missing Joomla tables must not
            // interrupt site rendering or reveal credentials to visitors.
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            $clubs = [];
        }

        return [
            'clubs' => $clubs,
            'mode' => $mode,
        ];
    }

    /**
     * Validate the configured external database selector without coercion.
     *
     * Accepts nonnegative integer and zero-padded decimal string values.
     * Invalid or overflowing selections return null instead of silently
     * selecting the default database and exposing unrelated club records.
     *
     * @param mixed $raw Raw Joomla module database parameter.
     * @return int|null Valid selector, or null for invalid configuration.
     */
    private static function databaseSelector(mixed $raw): ?int
    {
        if (!is_int($raw) && (!is_string($raw) || !preg_match('/^[0-9]+$/D', $raw))) {
            return null;
        }

        if (is_string($raw)) {
            $digits = ltrim($raw, '0');
            $raw = $digits === '' ? '0' : $digits;
        }

        $selector = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $selector === false ? null : $selector;
    }

    /**
     * Validate a nonnegative module count or days-limit setting.
     *
     * Rejects arrays, signs, non-decimal text and integer overflows; an
     * invalid parameter falls back to zero without PHP 8 conversion warnings.
     *
     * @param mixed $raw Raw Joomla limit or day-count setting.
     * @return int Nonnegative validated value, or zero by default.
     */
    private static function nonNegativeOption(mixed $raw): int
    {
        if (!is_int($raw) && (!is_string($raw) || !preg_match('/^[0-9]+$/D', $raw))) {
            return 0;
        }

        $value = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $value === false ? 0 : $value;
    }

    /**
     * Validate the signed age sorting direction configured for club entries.
     *
     * Preserves legacy negative or nonnegative integer values while
     * replacing malformed array or text settings with descending age order.
     *
     * @param mixed $raw Raw configured age-sort direction.
     * @return int Valid sorting option, or minus one by default.
     */
    private static function sortOrder(mixed $raw): int
    {
        $value = filter_var($raw, FILTER_VALIDATE_INT);

        return $value === false ? -1 : $value;
    }

    /**
     * Order upcoming club anniversaries by date and configured age rule.
     * Uses the number of days until the next anniversary as the primary
     * sort key, with age ordering to break otherwise equivalent entries.
     * @param array<int,object> $clubs Anniversary records to sort.
     * @param int $sort Signed ordering option for club age.
     * @return array<int,object> Sorted club anniversaries.
     */
    public static function sortClubs(array $clubs, int $sort): array
    {
        $ageDirection = $sort < 0 ? -1 : 1;

        usort(
            $clubs,
            static function (object $a, object $b) use ($ageDirection): int {
                $days = (int) ($a->days_to_birthday ?? 0) <=> (int) ($b->days_to_birthday ?? 0);

                if ($days !== 0) {
                    return $days;
                }

                return (((int) ($a->age ?? 0) <=> (int) ($b->age ?? 0)) * $ageDirection);
            }
        );

        return $clubs;
    }

    /**
     * Retrieve eligible clubs and calculate upcoming anniversaries.
     * Filters by configured seasons, computes date differences in the
     * site timezone and limits results before returning display records.
     * @param int $limit Maximum number of clubs to return.
     * @param int $maxDays Maximum days until the next anniversary.
     * @param mixed $seasonIds Requested season IDs.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param DateTimeZone $timezone Joomla site timezone.
     * @param int $sortOrder Configured anniversary ordering.
     * @return array<int,object> Club records with computed anniversary data.
     */
    private function getClubs(
        int $limit,
        int $maxDays,
        mixed $seasonIds,
        DatabaseInterface $db,
        DateTimeZone $timezone,
        int $sortOrder
    ): array {
        $seasonIds = self::normaliseIds($seasonIds);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.country'),
                $db->quoteName('c.founded'),
                $db->quoteName('c.name'),
                $db->quoteName('c.alias'),
                $db->quoteName('c.founded_year'),
                $db->quoteName('c.logo_big', 'picture'),
                $db->quoteName('c.founded_timestamp'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('co.alpha2', 'country_alpha2'),
                $db->quoteName('co.picture', 'country_picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co')
                . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('c.country')
            )
            ->where('c.published = 1')
            ->where('t.published = 1')
            ->where('pt.published = 1')
            ->where('pro.published = 1')
            ->where($db->quoteName('c.founded') . ' <> ' . $db->quote('0000-00-00'))
            ->where($db->quoteName('c.founded_year') . ' <> ' . $db->quote('0000'))
            ->where($db->quoteName('c.founded_year') . ' <> ' . $db->quote(''))
            ->order($db->quoteName('c.name') . ' ASC');

        if ($seasonIds !== []) {
            $query->whereIn($db->quoteName('st.season_id'), $seasonIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $today = new DateTimeImmutable('today', $timezone);
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $clubs = [];

        foreach ($rows as $row) {
            $id = (int) $row->id;

            if ($id <= 0 || isset($clubs[$id])) {
                continue;
            }

            $birth = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $row->founded, $timezone);

            // DateTimeImmutable normalises impossible dates instead of rejecting them.
            if (!$birth instanceof DateTimeImmutable
                || $birth->format('Y-m-d') !== (string) $row->founded
                || (int) $birth->format('Y') <= 0) {
                continue;
            }

            $year = (int) $today->format('Y');
            $month = (int) $birth->format('m');
            $day = (int) $birth->format('d');

            // PHP normalises 29 February into March in non-leap years.
            // Find the next actual anniversary without changing its day/month.
            while (!checkdate($month, $day, $year)
                || ($birthday = $today->setDate($year, $month, $day)) < $today) {
                ++$year;
            }

            $daysToBirthday = (int) $today->diff($birthday)->format('%a');

            if ($maxDays > 0 && $daysToBirthday > $maxDays) {
                continue;
            }

            $row->date_of_birth = $birth->format('Y-m-d');
            $row->daymonth = $birth->format('m-d');
            $row->year = $birthday->format('Y');
            $row->days_to_birthday = $daysToBirthday;
            $row->age = (int) $birthday->format('Y') - (int) $birth->format('Y');
            $row->age_year = (int) $today->format('Y') - (int) $row->founded_year;
            $row->club_link = self::clubLink((int) $row->project_id, $id);
            $row->picture_url = self::pictureUrl((string) $row->picture, $componentParams);
            $row->flag_html = self::flagHtml($row, $componentParams);
            $clubs[$id] = $row;
        }

        $clubs = self::sortClubs(array_values($clubs), $sortOrder);
        $limit = max(0, $limit);

        return $limit > 0 ? array_slice($clubs, 0, $limit) : $clubs;
    }

    /**
     * Format the message displayed for a club's next anniversary.
     * Uses today, tomorrow or days-remaining messages and applies the
     * module's language and date-format preferences.
     * @param object $club Club with computed remaining days.
     * @param Registry $params Module text and formatting options.
     * @param DateTimeZone $timezone Site date timezone.
     * @return string Display-ready anniversary label.
     */
    private static function birthdayText(object $club, Registry $params, DateTimeZone $timezone): string
    {
        $when = match ((int) $club->days_to_birthday) {
            0 => (string) $params->get('todaymessage', ''),
            1 => (string) $params->get('tomorrowmessage', ''),
            default => str_replace(
                '%DAYS_TO%',
                (string) $club->days_to_birthday,
                trim((string) $params->get('futuremessage', ''))
            ),
        };
        $text = htmlspecialchars(
            trim(Text::_((string) $params->get('birthdaytext', ''))),
            ENT_QUOTES,
            'UTF-8'
        );
        $birth = new DateTimeImmutable((string) $club->date_of_birth, $timezone);
        $next = new DateTimeImmutable((string) $club->year . '-' . (string) $club->daymonth, $timezone);

        return strtr($text, [
            '%WHEN%' => htmlspecialchars($when, ENT_QUOTES, 'UTF-8'),
            '%AGE%' => (string) $club->age,
            '%DATE%' => $next->format((string) $params->get('dayformat', 'd.m.Y')),
            '%DATE_OF_BIRTH%' => $birth->format((string) $params->get('birthdayformat', 'd.m.Y')),
            '%BR%' => '<br />',
            '%BOLD%' => '<strong>',
            '%BOLDEND%' => '</strong>',
        ]);
    }

    /**
     * Create a Joomla frontend URL for a club in a project.
     * Builds a routed club information link using the project and club
     * identifiers instead of manually concatenating template parameters.
     * @param int $projectId Context project identifier.
     * @param int $clubId Club to display.
     * @return string Joomla-routed club information URL.
     */
    private static function clubLink(int $projectId, int $clubId): string
    {
        return Route::_(SiteRouteHelper::view('clubinfo', [
            'p' => $projectId,
            'cid' => $clubId,
        ]));
    }

    /**
     * Resolve the club picture or configured fallback logo safely.
     * Accepts only local media paths beneath the Joomla installation
     * and returns an empty URL when neither candidate is valid.
     * @param string $picture Club's configured logo or picture path.
     * @param Registry $params Module placeholder and presentation settings.
     * @return string Safe browser image URL or an empty string.
     */
    private static function pictureUrl(string $picture, Registry $params): string
    {
        $root = realpath(JPATH_ROOT);

        if ($root === false) {
            return '';
        }

        foreach ([$picture, (string) $params->get('ph_clublogo', '')] as $candidate) {
            $path = self::localImagePath($candidate);

            if ($path === '') {
                continue;
            }

            // A configured image must resolve to a regular file inside Joomla's
            // document root, never to a parent path or an external symlink.
            $absolute = realpath($root . DIRECTORY_SEPARATOR . $path);

            if ($absolute !== false
                && str_starts_with($absolute, $root . DIRECTORY_SEPARATOR)
                && File::exists($absolute)
            ) {
                return Uri::root() . $path;
            }
        }

        return '';
    }

    /**
     * Validate a local club image path before using it in HTML.
     * Rejects schemes, protocol-relative paths, path traversal and
     * control characters in untrusted picture fields.
     * @param string $candidate Raw local media path.
     * @return string Valid relative path, or an empty string.
     */
    private static function localImagePath(string $candidate): string
    {
        $candidate = trim($candidate);

        if ($candidate === ''
            || preg_match('/[\x00-\x1F\x7F]/', $candidate)
            || str_contains($candidate, '\\')
            || str_starts_with($candidate, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $candidate)
            || preg_match('~(?:^|/)\.\.(?:/|$)~', $candidate)
        ) {
            return '';
        }

        return ltrim($candidate, '/');
    }

    /**
     * Create an escaped country flag marker for a club anniversary.
     * Normalises country codes, respects the flag display setting and
     * keeps untrusted country names safe when rendered as HTML.
     * @param object $row Club and country data from the database.
     * @param Registry $params Module flag presentation preferences.
     * @return string Safe country flag HTML fragment.
     */
    private static function flagHtml(object $row, Registry $params): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->country_alpha2 ?? ''));
        $alpha3 = preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : '';
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $label = htmlspecialchars($alpha3, ENT_QUOTES, 'UTF-8');

        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $css = match ($alpha3) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $css !== ''
                ? '<span class="fi fi-' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8')
                    . '" title="' . $label . '"></span>'
                : '';
        }

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : self::localImagePath((string) ($row->country_picture ?? $params->get('ph_flags', '')));

        return $path === ''
            ? ''
            : '<img src="' . htmlspecialchars(Uri::root() . $path, ENT_QUOTES, 'UTF-8')
                . '" alt="' . $label . '" />';
    }

    /**
     * Distinguish a genuinely empty legacy filter from a malformed, nonempty
     * selection. This prevents invalid input from widening a query.
     *
     * @param mixed $value Raw season-filter configuration.
     * @return bool Whether a nonempty filter has been explicitly configured.
     */
    private static function hasSeasonSelection(mixed $value): bool
    {
        foreach (is_array($value) ? $value : [$value] as $entry) {
            if (!is_string($entry) && !is_int($entry)) {
                return true;
            }

            $text = trim((string) $entry);

            if ($text !== '' && $text !== '0') {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse season identifiers from legacy module selector values.
     * Accepts numeric scalars and delimited lists, deduplicates valid
     * positive IDs and ignores malformed or overflowing inputs.
     * @param mixed $values Raw Joomla season selection.
     * @return array<int> Normalised positive season identifiers.
     */
    private static function normaliseIds(mixed $values): array
    {
        $values = is_array($values) ? $values : [$values];
        $ids = [];

        foreach ($values as $value) {
            if (!is_string($value) && !is_int($value)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                // Accept positive IDs and Joomla's id:alias values only.
                if (!preg_match('/^(\d+)(?::[A-Za-z0-9_-]+)?$/', $part, $match)) {
                    continue;
                }

                // Reject decimal IDs exceeding PHP_INT_MAX rather than silently
                // saturating them to an unrelated database record identifier.
                $decimal = ltrim($match[1], '0');
                $id = $decimal === ''
                    ? false
                    : filter_var($decimal, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Resolve the club-anniversary timezone from Joomla configuration.
     * Falls back to UTC if the configured timezone cannot be instantiated
     * so anniversary calculations remain available.
     * @param CMSApplicationInterface $app Joomla application with an offset.
     * @return DateTimeZone Effective module timezone.
     */
    private static function timezone(CMSApplicationInterface $app): DateTimeZone
    {
        try {
            return new DateTimeZone((string) $app->get('offset', 'UTC'));
        } catch (Throwable) {
            return new DateTimeZone('UTC');
        }
    }
}
