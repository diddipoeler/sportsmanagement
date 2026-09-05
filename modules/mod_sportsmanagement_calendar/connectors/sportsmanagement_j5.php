<?php
/**
 * Joomla 5/6 SportsManagement data connector for the calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Sportsmanagement
 * @subpackage  mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class SportsmanagementConnector extends JSMCalendar
{
    public static Registry $xparams;
    public static array $favteams = [];

    public static function getEntries(array &$caldates, Registry &$params, array &$matches): array
    {
        self::$params = $params;
        self::$xparams = $params;
        self::$prefix = (string) $params->get('prefix', '');
        self::$favteams = [];

        if ((int) self::$xparams->get('sportsmanagement_use_favteams', 0) === 1) {
            self::$favteams = self::getFavs();
        }

        $matchRows = [];
        $birthdayRows = [];

        if ((int) self::$xparams->get('jlmatches', 0) === 1) {
            $matchRows = self::formatMatches(self::loadMatches($caldates), $matches);
        }

        if ((int) self::$xparams->get('jlbirthdays', 1) === 1) {
            $birthdayRows = self::formatBirthdays(self::getBirthdays($caldates), $matches, $caldates);
        }

        return array_merge($matchRows, $birthdayRows);
    }

    public static function getFavs(): array
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('fav_team')])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('fav_team') . " <> ''");

        $projectIds = self::normaliseIds(self::$xparams->get('p', []));
        if ($projectIds !== []) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $projectIds) . ')');
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public static function loadMatches(array $caldates, string $ordering = 'ASC'): array
    {
        $input = self::siteApplication()->getInput();
        $db = self::database();
        $query = $db->getQuery(true);
        $conditions = [];
        $customTeam = $input->getInt('jlcteam', 0);

        if ($customTeam > 0) {
            $conditions[] = '(m.projectteam1_id = ' . $customTeam . ' OR m.projectteam2_id = ' . $customTeam . ')';
        } else {
            $teamIds = self::normaliseIds(self::$xparams->get('team_ids', []));
            if ($teamIds !== []) {
                $list = implode(',', $teamIds);
                $conditions[] = '(st1.team_id IN (' . $list . ') OR st2.team_id IN (' . $list . '))';
            }

            $clubIds = self::normaliseIds(self::$xparams->get('club_ids', []));
            if ($clubIds !== []) {
                $list = implode(',', $clubIds);
                $conditions[] = '(t1.club_id IN (' . $list . ') OR t2.club_id IN (' . $list . '))';
            }

            if ((int) self::$xparams->get('sportsmanagement_use_favteams', 0) === 1) {
                $favoriteConditions = [];
                foreach (self::$favteams as $favorite) {
                    $favoriteTeamIds = self::normaliseIds($favorite->fav_team ?? []);
                    $projectId = (int) ($favorite->id ?? 0);
                    if ($favoriteTeamIds === [] || $projectId <= 0) {
                        continue;
                    }
                    $list = implode(',', $favoriteTeamIds);
                    $favoriteConditions[] = '((st1.team_id IN (' . $list . ') OR st2.team_id IN (' . $list . ')) AND p.id = ' . $projectId . ')';
                }
                if ($favoriteConditions !== []) {
                    $conditions[] = '(' . implode(' OR ', $favoriteConditions) . ')';
                }
            }
        }

        $query->select([
            'm.id', 'm.round_id', 'm.projectteam1_id', 'm.projectteam2_id', 'm.match_date',
            'm.team1_result', 'm.team2_result', 'm.match_date AS gamematchdate',
            'p.timezone', 'p.name', 'p.alias', 'm.match_date AS caldate',
            'r.roundcode', 'r.name AS roundname', 'r.round_date_first', 'r.round_date_last',
            'm.id AS matchcode', 'p.id AS project_id', 'm.cancel', 'm.cancel_reason',
            'le.country AS leaguecountry', 'le.name AS leaguename',
            'p.alias AS project_alias', 't1.alias AS team1_alias', 't2.alias AS team2_alias',
        ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON r.id = m.round_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON p.id = r.project_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'le') . ' ON le.id = p.league_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'tt1') . ' ON m.projectteam1_id = tt1.id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'tt2') . ' ON m.projectteam2_id = tt2.id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON st1.id = tt1.team_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON st2.id = tt2.team_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON t1.id = st1.team_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON t2.id = st2.team_id')
            ->where('m.published = 1')
            ->where('p.published = 1')
            ->where('m.match_date <> ' . $db->quote('0000-00-00 00:00:00'));

        if ($conditions !== []) {
            $query->where('(' . implode(' OR ', $conditions) . ')');
        }
        if (isset($caldates['starttimestamp'])) {
            $query->where('m.match_timestamp >= ' . (int) $caldates['starttimestamp']);
        }
        if (isset($caldates['endtimestamp'])) {
            $query->where('m.match_timestamp <= ' . (int) $caldates['endtimestamp']);
        }
        if (!empty($caldates['matchcode'])) {
            $query->where('r.matchcode LIKE ' . $db->quote((string) $caldates['matchcode']));
        }

        $projectIds = self::normaliseIds(self::$xparams->get('p', []));
        if ($projectIds !== []) {
            $query->where('p.id IN (' . implode(',', $projectIds) . ')');
        }
        if (!empty($caldates['resultsonly'])) {
            $query->where('m.team1_result IS NOT NULL');
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $query->order('m.match_date ' . $direction . ', p.id ' . $direction);

        $offset = max(0, (int) ($caldates['limitstart'] ?? 0));
        $limit = max(0, (int) ($caldates['limitend'] ?? 0));
        $db->setQuery($query, $offset, $limit);
        $result = $db->loadObjectList() ?: [];

        foreach ($result as $match) {
            $match->timestamp = sportsmanagementHelper::getTimestamp($match->match_date, 1, $match->timezone);
            sportsmanagementHelper::convertMatchDateToTimezone($match);
        }

        return $result;
    }

    public static function formatMatches(array $rows, array &$matches): array
    {
        if ($rows === []) {
            return [];
        }

        $teams = self::getTeamsFromMatches($rows);
        $blank = new \stdClass();
        $blank->name = '';
        $blank->short_name = '';
        $blank->middle_name = '';
        $blank->logo_small = '';
        $blank->logo_middle = '';
        $blank->logo_big = '';
        $teams[0] = $blank;
        $newRows = [];

        foreach ($rows as $key => $row) {
            $home = $teams[(int) $row->projectteam1_id] ?? $blank;
            $away = $teams[(int) $row->projectteam2_id] ?? $blank;
            $formatted = [
                'type' => 'jlm',
                'leaguecountry' => (string) $row->leaguecountry,
                'leaguename' => (string) $row->leaguename,
                'homepic' => self::buildImage($home),
                'awaypic' => self::buildImage($away),
                'date' => sportsmanagementHelper::getMatchStartTimestamp($row),
                'result' => (int) $row->cancel === 1
                    ? (string) $row->cancel_reason
                    : ($row->team1_result !== null ? $row->team1_result . ':' . $row->team2_result : '-:-'),
                'headingtitle' => parent::jl_utf8_convert($row->name . '-' . $row->roundname, 'iso-8859-1', 'utf-8'),
                'homename' => self::formatTeamName($home),
                'awayname' => self::formatTeamName($away),
                'matchcode' => $row->matchcode,
                'project_id' => $row->project_id,
                'timestamp' => $row->timestamp,
            ];

            $routeParameters = [
                'cfg_which_database' => (int) self::$params->get('cfg_which_database', 0),
                's' => self::$params->get('s', 0),
                'p' => (int) $row->project_id . ':' . (string) $row->project_alias,
                'mid' => (int) $row->matchcode . ':' . (string) $row->team1_alias . '_' . (string) $row->team2_alias,
            ];
            $formatted['link'] = SiteRouteHelper::view('nextmatch', $routeParameters);

            $newRows[$key] = $formatted;
            $matches[] = $formatted;
            parent::addTeam((int) $row->projectteam1_id, parent::jl_utf8_convert($home->name), $formatted['homepic']);
            parent::addTeam((int) $row->projectteam2_id, parent::jl_utf8_convert($away->name), $formatted['awaypic']);
        }

        return $newRows;
    }

    public static function getTeamsFromMatches(array $games): array
    {
        if ($games === []) {
            return [];
        }

        $ids = [];
        foreach ($games as $game) {
            $ids[(int) $game->projectteam1_id] = (int) $game->projectteam1_id;
            $ids[(int) $game->projectteam2_id] = (int) $game->projectteam2_id;
        }
        unset($ids[0]);
        if ($ids === []) {
            return [];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                'tl.id AS teamtoolid', 'tl.division_id', 'tl.standard_playground', 'tl.start_points',
                'tl.info', 'tl.team_id', 'tl.checked_out', 'tl.checked_out_time', 'tl.picture', 'tl.project_id',
                't.id', 't.name', 't.short_name', 't.middle_name', 't.info', 't.club_id',
                'c.logo_small', 'c.logo_middle', 'c.logo_big', 'c.country', 'p.name AS project_name',
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = t.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'tl') . ' ON tl.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON p.id = tl.project_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON t.club_id = c.id')
            ->where('tl.id IN (' . implode(',', $ids) . ')');

        return $db->setQuery($query)->loadObjectList('teamtoolid') ?: [];
    }

    public static function buildImage(object $team): string
    {
        $field = (string) self::$xparams->get('team_logos', 'logo_small');
        if ($field === '-') {
            return '';
        }

        $path = (string) ($team->{$field} ?? '');
        if (!sportsmanagementHelper::existPicture($path)) {
            $path = (string) sportsmanagementHelper::getDefaultPlaceholder('logo_big');
        }

        if ($path === '') {
            return '';
        }

        $height = max(0, (int) self::$xparams->get('logo_height', 20));
        $style = $height > 0 ? ' style="height:' . $height . 'px;"' : '';

        return '<img src="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '" alt="'
            . htmlspecialchars(parent::jl_utf8_convert((string) ($team->short_name ?? '')), ENT_QUOTES, 'UTF-8')
            . '" title="' . htmlspecialchars(parent::jl_utf8_convert((string) ($team->name ?? '')), ENT_QUOTES, 'UTF-8')
            . '"' . $style . ' />';
    }

    public static function formatTeamName(object $team): string
    {
        $field = (string) self::$xparams->get('team_names', 'short_name');
        if ($field === '-') {
            return '';
        }

        $fullName = trim(parent::jl_utf8_convert((string) ($team->name ?? '')));
        if ($field === 'short_name') {
            $shortName = trim(parent::jl_utf8_convert((string) ($team->short_name ?? '')));
            return $shortName !== '' ? $shortName : $fullName;
        }

        $value = trim((string) ($team->{$field} ?? ''));
        return parent::jl_utf8_convert($value !== '' ? $value : $fullName);
    }

    public static function getBirthdays(array $caldates, string $ordering = 'ASC'): array
    {
        $start = self::parseDate((string) ($caldates['start'] ?? ''));
        $end = self::parseDate((string) ($caldates['end'] ?? ''));
        if (!$start || !$end) {
            return [];
        }

        $input = self::siteApplication()->getInput();
        $db = self::database();
        $query = $db->getQuery(true);
        $customTeam = $input->getInt('jlcteam', 0);

        $query->select([
            'p.id', 'p.firstname', 'p.lastname', 'p.picture', 'p.country', 'p.birthday',
            'pt.project_id AS project_id', 'team.short_name', 'team.id AS teamid',
            'team.alias AS team_alias', 'pro.alias AS project_alias', 'p.alias AS person_alias',
        ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.person_id = p.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 'team') . ' ON team.id = st.team_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'club') . ' ON club.id = team.club_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro') . ' ON pro.id = pt.project_id AND pro.season_id = st.season_id')
            ->where('p.published = 1')
            ->where('p.birthday <> ' . $db->quote('0000-00-00'));

        if ($customTeam > 0) {
            $query->where('pt.id = ' . $customTeam);
        } else {
            $teamIds = self::normaliseIds(self::$xparams->get('team_ids', []));
            if ($teamIds !== []) {
                $query->where('st.team_id IN (' . implode(',', $teamIds) . ')');
            }
            $clubIds = self::normaliseIds(self::$xparams->get('club_ids', []));
            if ($clubIds !== []) {
                $query->where('team.club_id IN (' . implode(',', $clubIds) . ')');
            }
        }

        $projectIds = self::normaliseIds(self::$xparams->get('p', []));
        if ($projectIds !== []) {
            $query->where('pt.project_id IN (' . implode(',', $projectIds) . ')');
        }
        $query->order('p.birthday ' . (strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC'));

        $rows = $db->setQuery($query)->loadObjectList() ?: [];
        $birthdays = [];
        $seen = [];

        foreach ($rows as $row) {
            $birth = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $row->birthday);
            if (!$birth) {
                continue;
            }

            for ($year = (int) $start->format('Y'); $year <= (int) $end->format('Y'); $year++) {
                try {
                    $birthday = $birth->setDate($year, (int) $birth->format('m'), (int) $birth->format('d'));
                } catch (\Throwable) {
                    continue;
                }
                if ($birthday < $start || $birthday > $end) {
                    continue;
                }

                $key = $row->id . ':' . $row->project_id . ':' . $birthday->format('Y-m-d');
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $copy = clone $row;
                $copy->month_day = $birthday->format('m-d');
                $copy->event_date = $birthday->format('Y-m-d');
                $copy->age = $year - (int) $birth->format('Y');
                $copy->date_of_birth = $birth->format('Y-m-d');
                $birthdays[] = $copy;
            }
        }

        return $birthdays;
    }

    public static function formatBirthdays(array $rows, array &$matches, array $dates): array
    {
        $year = substr((string) ($dates['start'] ?? date('Y')), 0, 4);
        $newRows = [];

        foreach ($rows as $key => $row) {
            $image = '';
            $picture = ltrim((string) ($row->picture ?? ''), '/');
            if ($picture !== '' && is_file(JPATH_ROOT . '/' . $picture)) {
                $image = '<img src="' . htmlspecialchars(Uri::root() . $picture, ENT_QUOTES, 'UTF-8')
                    . '" alt="" loading="lazy" style="height:40px;vertical-align:middle;margin:0 5px;" />';
            }

            $name = trim(
                parent::jl_utf8_convert((string) $row->firstname) . ' '
                . parent::jl_utf8_convert((string) $row->lastname) . ' - '
                . parent::jl_utf8_convert((string) $row->short_name)
            );

            $formatted = [
                'type' => 'jlb',
                'homepic' => '',
                'awaypic' => '',
                'image' => $image,
                'date' => (string) ($row->event_date ?? ($year . '-' . $row->month_day)),
                'age' => '(' . (int) $row->age . ')',
                'headingtitle' => (string) self::$xparams->get('birthday_text', 'Birthday'),
                'name' => $name,
                'matchcode' => 0,
                'project_id' => $row->project_id,
            ];
            $formatted['link'] = SiteRouteHelper::view('player', [
                'cfg_which_database' => self::$params->get('cfg_which_database'),
                's' => self::$params->get('s'),
                'p' => (int) $row->project_id . ':' . (string) $row->project_alias,
                'tid' => (int) $row->teamid . ':' . (string) $row->team_alias,
                'pid' => (int) $row->id . ':' . (string) $row->person_alias,
            ]);

            $newRows[$key] = $formatted;
            $matches[] = $formatted;
        }

        return $newRows;
    }

    private static function siteApplication(): SiteApplication
    {
        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);

        return $app;
    }

    private static function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = (int) self::$xparams->get('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
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

    private static function parseDate(string $value): ?\DateTimeImmutable
    {
        $value = substr(trim($value), 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof \DateTimeImmutable ? $date : null;
    }
}
