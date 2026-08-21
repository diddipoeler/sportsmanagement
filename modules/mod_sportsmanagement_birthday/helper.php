<?php
/** Joomla 5/6 data helper and legacy facade for the birthday module. */
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class modSportsmanagementBirthdayDataHelper
{
    public function getData(Registry $params, Registry $componentParams, CMSApplicationInterface $app): array
    {
        $db = $this->database((int) $params->get('cfg_which_database', $componentParams->get('cfg_which_database', 0)));
        $projectIds = $this->normaliseIds($params->get('p', []));
        $teamIds = $this->resolveTeamIds($db, $params, $projectIds);
        $personTypes = match ((int) $params->get('use_which', 0)) {
            1 => [1],
            2 => [2],
            default => [1, 2],
        };

        $query = $db->getQuery(true)
            ->select([
                'p.id', 'p.birthday', 'p.firstname', 'p.nickname', 'p.lastname',
                'p.picture AS default_picture', 'p.country', 'stp.picture', 'stp.persontype AS type',
                'st.team_id', 'pt.project_id', 't.name AS team_name',
                'co.alpha2 AS country_alpha2', 'co.picture AS country_picture',
                "CONCAT_WS(':', pro.id, pro.alias) AS project_slug",
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', s.id, s.alias) AS season_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON stp.person_id = p.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = stp.team_id AND st.season_id = stp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro') . ' ON pro.id = pt.project_id AND pro.season_id = st.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON s.id = pro.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.id = st.team_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON co.alpha3 = p.country')
            ->where('p.published = 1')
            ->where("p.birthday <> '0000-00-00'")
            ->where('stp.persontype IN (' . implode(',', $personTypes) . ')');

        $position = $db->getQuery(true)
            ->select('pos.name')
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ppos.position_id = pos.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_person_project_position', 'ppp') . ' ON ppp.project_position_id = ppos.id')
            ->where('ppp.person_id = p.id')
            ->where('ppp.project_id = pro.id')
            ->where('ppp.persontype = stp.persontype');
        $position->setLimit(1);
        $query->select('(' . $position . ') AS position_name');

        $ageGroupId = max(0, (int) $params->get('agegrouplist', 0));
        if ($ageGroupId > 0) {
            $query->where('p.agegroup_id = ' . $ageGroupId);
        }
        if ($projectIds) {
            $query->where('pt.project_id IN (' . implode(',', $projectIds) . ')');
        }
        if ($teamIds) {
            $query->where('st.team_id IN (' . implode(',', $teamIds) . ')');
        }
        $query->order('p.lastname ASC, p.firstname ASC, p.id ASC');

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
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
            $row->person_link = $this->personLink($row, (int) $params->get('cfg_which_database', 0));
            $row->picture_url = $this->pictureUrl($row, (bool) $params->get('show_picture', 1));
            $row->flag_html = (bool) $params->get('show_player_flag', 0) ? $this->flagHtml($row, $componentParams) : '';
            $row->birthday_text = $this->birthdayText($row, $birthday, $birth, $params);
            $people[$key] = (array) $row;
        }

        $people = jsm_birthday_sort(array_values($people), $params->get('sort_order', '-'), false);
        $limit = max(0, (int) $params->get('limit', 0));
        if ($limit > 0) {
            $people = array_slice($people, 0, $limit);
        }

        return ['persons' => $people, 'mode' => strtoupper((string) $params->get('mode', 'L')), 'pictureServer' => Uri::root()];
    }

    private function resolveTeamIds(DatabaseInterface $db, Registry $params, array $projectIds): array
    {
        if (!(bool) $params->get('use_fav', 0)) {
            return $this->normaliseIds($params->get('teams', []));
        }
        if (!$projectIds) {
            return [];
        }
        $query = $db->getQuery(true)->select('fav_team')->from($db->quoteName('#__sportsmanagement_project'))->where('id IN (' . implode(',', $projectIds) . ')');
        return $this->normaliseIds($db->setQuery($query)->loadColumn() ?: []);
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
        $this->loadSportsmanagementHelper();

        if (class_exists('sportsmanagementHelper') && method_exists('sportsmanagementHelper', 'formatName')) {
            return (string) sportsmanagementHelper::formatName(null, (string) $row->firstname, (string) $row->nickname, (string) $row->lastname, $format);
        }
        return trim((string) $row->firstname . ' ' . (string) $row->lastname);
    }

    private function personLink(object $row, int $databaseSelector): string
    {
        $view = match ((int) $row->type) { 2 => 'staff', 3 => 'referee', default => 'player' };
        $query = ['option' => 'com_sportsmanagement', 'view' => $view, 'cfg_which_database' => $databaseSelector,
            's' => (string) ($row->season_slug ?? ''), 'p' => (string) ($row->project_slug ?? ''), 'pid' => (string) ($row->person_slug ?? '')];
        if ((int) $row->type !== 3) {
            $query['tid'] = (string) ($row->team_slug ?? '');
        }
        return Route::_('index.php?' . http_build_query($query), false);
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
            ['%WHEN%' => htmlspecialchars($when, ENT_QUOTES, 'UTF-8'), '%AGE%' => (string) $row->age,
             '%DATE%' => $birthday->format((string) $params->get('dayformat', 'd.m.Y')),
             '%DATE_OF_BIRTH%' => $birth->format((string) $params->get('birthdayformat', 'd.m.Y')),
             '%BR%' => '<br />', '%BOLD%' => '<strong>', '%BOLDEND%' => '</strong>']
        );
    }

    private function database(int $selector): DatabaseInterface
    {
        $this->loadSportsmanagementHelper();

        try {
            $db = sportsmanagementHelper::getDBConnection(true, $selector);
            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    private function loadSportsmanagementHelper(): void
    {
        if (class_exists('sportsmanagementHelper', false)) {
            return;
        }

        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
}

if (!function_exists('jsm_birthday_sort')) {
    function jsm_birthday_sort(array $rows, $arguments = '-', $keys = true): array
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
}

final class modSportsmanagementBirthdayHelper
{
    public static function getData(Registry $params): array
    {
        $app = Factory::getApplication();
        return (new modSportsmanagementBirthdayDataHelper())->getData($params, ComponentHelper::getParams('com_sportsmanagement'), $app);
    }
}
