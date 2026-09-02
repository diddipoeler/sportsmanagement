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
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class BirthdayHelper
{
    public function getData(
        Registry $params,
        Registry $componentParams,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $databaseSelector = (int) $params->get(
            'cfg_which_database',
            $componentParams->get('cfg_which_database', 0)
        );
        $db = $this->database($databaseSelector, $fallbackDatabase);
        $projectIds = $this->normaliseIds($params->get('p', []));
        $teamIds = $this->resolveTeamIds($db, $params, $projectIds);
        $personTypes = match ((int) $params->get('use_which', 0)) {
            1 => [1],
            2 => [2],
            default => [1, 2],
        };

        $query = $db->getQuery(true)
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
            ->where($db->quoteName('stp.persontype') . ' IN (' . implode(',', $personTypes) . ')');

        $position = $db->getQuery(true)
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
            $query->where($db->quoteName('p.agegroup_id') . ' = ' . $ageGroupId);
        }
        if ($projectIds) {
            $query->where($db->quoteName('pt.project_id') . ' IN (' . implode(',', $projectIds) . ')');
        }
        if ($teamIds) {
            $query->where($db->quoteName('st.team_id') . ' IN (' . implode(',', $teamIds) . ')');
        }
        $query
            ->order($db->quoteName('p.lastname') . ' ASC')
            ->order($db->quoteName('p.firstname') . ' ASC')
            ->order($db->quoteName('p.id') . ' ASC');

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $rows = [];
        }

        $today = $this->today($app);
        $maxDaysValue = trim((string) $params->get('maxdays', ''));
        $maxDays = $maxDaysValue !== '' && (int) $maxDaysValue >= 0 ? (int) $maxDaysValue : null;
        $people = [];

        foreach ($rows as $row) {
            $birth = $this->date((string) $row->birthday, $today->getTimezone());
            if (!$birth) {
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

    private function resolveTeamIds(DatabaseInterface $db, Registry $params, array $projectIds): array
    {
        if (!(bool) $params->get('use_fav', 0)) {
            return $this->normaliseIds($params->get('teams', []));
        }
        if (!$projectIds) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('fav_team'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $projectIds) . ')');

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
        return $date instanceof \DateTimeImmutable ? $date : null;
    }

    private function nextBirthday(\DateTimeImmutable $birth, \DateTimeImmutable $today): \DateTimeImmutable
    {
        $birthday = $today->setDate((int) $today->format('Y'), (int) $birth->format('m'), (int) $birth->format('d'));
        return $birthday < $today ? $birthday->modify('+1 year') : $birthday;
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
        foreach ([(string) ($row->picture ?? ''), (string) ($row->default_picture ?? '')] as $path) {
            $path = ltrim(trim($path), '/');
            if ($path !== '' && is_file(JPATH_ROOT . '/' . $path)) {
                return Uri::root() . $path;
            }
        }
        return '';
    }

    private function flagHtml(object $row, Registry $params): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->country_alpha2 ?? ''));
        $label = htmlspecialchars($alpha3, ENT_QUOTES, 'UTF-8');
        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $css = match ($alpha3) { 'WAL' => 'gb-wls', 'SCO' => 'gb-sct', 'GBR' => 'gb-eng', default => $alpha2 };
            return $css !== '' ? '<span class="fi fi-' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '" title="' . $label . '"></span>' : '';
        }
        $path = $alpha2 !== '' ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png' : ltrim((string) ($row->country_picture ?? $params->get('ph_flags', '')), '/');
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
                '%DATE%' => $birthday->format((string) $params->get('dayformat', 'd.m.Y')),
                '%DATE_OF_BIRTH%' => $birth->format((string) $params->get('birthdayformat', 'd.m.Y')),
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
