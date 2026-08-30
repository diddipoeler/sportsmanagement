<?php
namespace Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Throwable;

final class ClubBirthdayHelper
{
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $timezone = self::timezone($app);
        $database = SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
        $clubs = $this->getClubs(
            (int) $params->get('limit', 0),
            max(0, (int) $params->get('maxdays', 0)),
            $params->get('s', []),
            $database,
            $timezone,
            (int) $params->get('sort_order', -1)
        );

        foreach ($clubs as $club) {
            $club->birthday_text = self::birthdayText($club, $params, $timezone);
        }

        $mode = strtoupper((string) $params->get('mode', 'L'));

        if ($mode !== 'BC') {
            $mode = 'L';
        }

        return [
            'clubs' => $clubs,
            'mode' => $mode,
        ];
    }

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

    private function getClubs(
        int $limit,
        int $maxDays,
        mixed $seasonIds,
        DatabaseInterface $db,
        DateTimeZone $timezone,
        int $sortOrder
    ): array {
        $seasonIds = self::normaliseIds($seasonIds);
        $query = $db->getQuery(true)
            ->select([
                'c.id',
                'c.country',
                'c.founded',
                'c.name',
                'c.alias',
                'c.founded_year',
                'c.logo_big AS picture',
                'c.founded_timestamp',
                'pt.project_id',
                'co.alpha2 AS country_alpha2',
                'co.picture AS country_picture',
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = t.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON pro.id = pt.project_id AND pro.season_id = st.season_id'
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON co.alpha3 = c.country')
            ->where('c.published = 1')
            ->where('t.published = 1')
            ->where('pt.published = 1')
            ->where('pro.published = 1')
            ->where("c.founded <> '0000-00-00'")
            ->where("c.founded_year <> '0000'")
            ->where("c.founded_year <> ''")
            ->order('c.name ASC');

        if ($seasonIds !== []) {
            $query->where('st.season_id IN (' . implode(',', $seasonIds) . ')');
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

            if (!$birth instanceof DateTimeImmutable) {
                continue;
            }

            $birthday = $today->setDate(
                (int) $today->format('Y'),
                (int) $birth->format('m'),
                (int) $birth->format('d')
            );

            if ($birthday < $today) {
                $birthday = $birthday->modify('+1 year');
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

    private static function clubLink(int $projectId, int $clubId): string
    {
        return Route::_(
            'index.php?' . http_build_query([
                'option' => 'com_sportsmanagement',
                'view' => 'clubinfo',
                'p' => $projectId,
                'cid' => $clubId,
            ], '', '&', PHP_QUERY_RFC3986),
            false
        );
    }

    private static function pictureUrl(string $picture, Registry $params): string
    {
        $picture = ltrim(trim($picture), '/');

        if ($picture !== '' && File::exists(JPATH_ROOT . '/' . $picture)) {
            return Uri::root() . $picture;
        }

        $placeholder = ltrim((string) $params->get('ph_clublogo', ''), '/');

        return $placeholder !== '' && File::exists(JPATH_ROOT . '/' . $placeholder)
            ? Uri::root() . $placeholder
            : '';
    }

    private static function flagHtml(object $row, Registry $params): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->country_alpha2 ?? ''));
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
            : ltrim((string) ($row->country_picture ?? $params->get('ph_flags', '')), '/');

        return $path === ''
            ? ''
            : '<img src="' . htmlspecialchars(Uri::root() . $path, ENT_QUOTES, 'UTF-8')
                . '" alt="' . $label . '" />';
    }

    private static function normaliseIds(mixed $values): array
    {
        $values = is_array($values) ? $values : [$values];
        $ids = [];

        foreach ($values as $value) {
            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                $id = (int) $part;

                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    private static function timezone(CMSApplicationInterface $app): DateTimeZone
    {
        try {
            return new DateTimeZone((string) $app->get('offset', 'UTC'));
        } catch (Throwable) {
            return new DateTimeZone('UTC');
        }
    }
}
