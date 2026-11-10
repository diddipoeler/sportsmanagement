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

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class SportsmanagementConnector extends JSMCalendar
{
    public static Registry $xparams;
    public static array $favteams = [];

    /**
     * Collect SportsManagement matches and birthdays for the current calendar window.
     * Loads the configured project and team filters, optionally collects favourite-team restrictions, and appends formatted fixtures and birthdays to the shared calendar matches.
     *
     * @param array $caldates Requested month boundaries and query limits.
     * @param Registry $params Calendar module settings retained by the connector.
     * @param array $matches Shared event rows filled by reference.
     * @return array<int,array> New calendar match and birthday rows.
     */
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

    /**
     * Retrieve saved favourite-team identifiers from selected SportsManagement projects.
     * Reads only projects with nonempty favourite-team lists and optionally restricts the query to validated project IDs.
     *
     * @return array<int,object> Projects and their saved favourite-team lists.
     */
    public static function getFavs(): array
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('fav_team')])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('fav_team') . " <> ''");

        $projectIds = self::normaliseIds(self::$xparams->get('p', []));
        if ($projectIds !== []) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $projectIds) . ')');
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Load published project fixtures matching the selected calendar month and filters.
     * Joins matches to rounds, projects, leagues and both teams, filters project/team/club/favourites and result settings, then normalises dates in the returned rows.
     *
     * @param array $caldates Time and date boundaries, result flags and paging limits.
     * @param string $ordering Date sort direction, ASC or DESC.
     * @return array<int,object> Matching fixtures with normalised match dates.
     */
    public static function loadMatches(array $caldates, string $ordering = 'ASC'): array
    {
        $input = self::siteApplication()->getInput();
        $db = self::database();
        $query = $db->createQuery();
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
            self::normaliseMatchDate($match);
        }

        return $result;
    }

    /**
     * Convert SportsManagement fixtures into the calendar's common match format.
     * Resolves both teams and logos, formats result and date labels, builds routed match-detail links and registers each displayed team for the filter dropdown.
     *
     * @param array<int,object> $rows Raw match, project and team relation rows.
     * @param array $matches Shared calendar entries to extend by reference.
     * @return array<int,array> Formatted fixture rows for the module.
     */
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
                'date' => self::matchStart($row),
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
            // Preserve the original IDs so addTeam can reject malformed
            // connector data instead of accepting a lossy integer cast.
            parent::addTeam($row->projectteam1_id, parent::jl_utf8_convert($home->name), $formatted['homepic']);
            parent::addTeam($row->projectteam2_id, parent::jl_utf8_convert($away->name), $formatted['awaypic']);
        }
        return $newRows;
    }

    /**
     * Load team and club information for all referenced project-team IDs.
     * Deduplicates the home and away project-team relations before joining their team names, logo fields and associated projects.
     *
     * @param array<int,object> $games Fixtures containing home and away project-team IDs.
     * @return array<int,object> Team objects keyed by project-team ID.
     */
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
        $query = $db->createQuery()
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

    /**
     * Build a safe HTML team-logo image according to the calendar settings.
     * Chooses the configured logo field, falls back to the default club image and escapes URLs and captions before rendering the optional height attribute.
     *
     * @param object $team Team record including logo and display-name fields.
     * @return string Escaped image HTML, or an empty string when disabled.
     */
    public static function buildImage(object $team): string
    {
        $field = (string) self::$xparams->get('team_logos', 'logo_small');
        if ($field === '-') {
            return '';
        }

        $path = trim((string) ($team->{$field} ?? ''));
        if (!self::imageExists($path)) {
            $path = self::defaultClubLogo();
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

    /**
     * Format a calendar team name using the configured display preference.
     * Chooses between the short or configured alternate name, falling back to the full team name when the preferred field is empty.
     *
     * @param object $team Team data with short, middle and full names.
     * @return string Team name converted for calendar presentation.
     */
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

    /**
     * Load calendar birthdays for published players within the selected date range.
     * Joins players to their season teams and projects, applies project/team/club filters and projects each person's birth date into the requested years without duplicate events.
     *
     * @param array $caldates Requested calendar start and end dates.
     * @param string $ordering Order of stored birth dates, ASC or DESC.
     * @return array<int,object> Birthday events with date and age information.
     */
    public static function getBirthdays(array $caldates, string $ordering = 'ASC'): array
    {
        $start = self::parseDate((string) ($caldates['start'] ?? ''));
        $end = self::parseDate((string) ($caldates['end'] ?? ''));
        if (!$start || !$end) {
            return [];
        }

        $input = self::siteApplication()->getInput();
        $db = self::database();
        $query = $db->createQuery();
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

    /**
     * Convert player birthdays into dated calendar events with player routes.
     * Optionally embeds existing player portraits, formats team and person names, and builds links to the frontend player page.
     *
     * @param array<int,object> $rows Birthday records with project and person details.
     * @param array $matches Shared event list appended by reference.
     * @param array $dates Calendar date boundaries used for fallback event years.
     * @return array<int,array> Formatted player-birthday calendar events.
     */
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

    /**
     * Convert a stored match date to the frontend user's calendar timezone.
     * Sets a comparable timestamp, replaces missing legacy dates with null and converts valid fixture dates to Joomla Date while tolerating unavailable timezone data.
     *
     * @param object $match Match record modified with timestamp and converted date.
     * @return void
     */
    private static function normaliseMatchDate(object $match): void
    {
        $rawDate = (string) ($match->match_date ?? '');
        $timestamp = strtotime($rawDate);
        $match->timestamp = $timestamp === false ? 0 : $timestamp;

        if ($rawDate === '' || $rawDate === '0000-00-00 00:00:00') {
            $match->match_date = null;
            return;
        }

        $app = self::siteApplication();
        $projectTimezone = trim((string) ($match->timezone ?? ''));
        $timezone = trim((string) $app->getIdentity()->getParam('timezone', $projectTimezone));
        if ($timezone === '') {
            $timezone = trim((string) $app->get('offset', 'UTC')) ?: 'UTC';
        }

        try {
            $matchDate = new Date($rawDate);
            $matchDate->setTimezone(new \DateTimeZone($timezone));
            $match->match_date = $matchDate;
            $match->timezone = $timezone;
        } catch (\Throwable) {
            // Keep the original database value if the timezone is invalid.
        }
    }

    /**
     * Format the start time of a fixture for the calendar's match list.
     * Accepts Joomla Date and other DateTimeInterface values or parses the stored database date string, keeping the original value when parsing fails.
     *
     * @param object $match Fixture with its normalised or legacy match_date property.
     * @return string Match start formatted as YYYY-MM-DD HH:MM when available.
     */
    private static function matchStart(object $match): string
    {
        $matchDate = $match->match_date ?? null;

        if ($matchDate instanceof \DateTimeInterface) {
            return $matchDate->format('Y-m-d H:i');
        }

        $timestamp = strtotime((string) $matchDate);

        return $timestamp === false ? (string) $matchDate : date('Y-m-d H:i', $timestamp);
    }

    /**
     * Check whether a configured image path resolves to a local file.
     * Supports absolute file references and paths relative to the Joomla root, rejecting empty paths.
     *
     * @param string $path Stored image location.
     * @return bool Whether the referenced file exists on the server.
     */
    private static function imageExists(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (is_file($path)) {
            return true;
        }

        return is_file(JPATH_ROOT . '/' . ltrim($path, '/'));
    }

    /**
     * Read the component's default club logo fallback.
     * Retrieves the placeholder logo configured in com_sportsmanagement when a team's preferred logo file is unavailable.
     *
     * @return string Configured fallback image path or an empty string.
     */
    private static function defaultClubLogo(): string
    {
        return trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big', ''));
    }

    /**
     * Resolve and verify the Joomla site application for calendar access.
     * Uses the shared site-application resolver and rejects administrator clients before reading frontend settings.
     *
     * @return \Joomla\CMS\Application\CMSApplicationInterface Active frontend application.
     */
    private static function siteApplication(): \Joomla\CMS\Application\CMSApplicationInterface
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Calendar requires the Joomla site application.', 500);
        }

        return $app;
    }

    /**
     * Resolve Joomla's database interface through its public dependency container.
     * Verifies the container service before allowing SQL queries, avoiding deprecated global database accessors.
     *
     * @return DatabaseInterface Valid Joomla database connection.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Calendar database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Choose the default or configured external SportsManagement database.
     * Verifies site context, reads the calendar database selector and delegates to the component's database resolver.
     *
     * @return DatabaseInterface Selected SportsManagement database connection.
     */
    private static function database(): DatabaseInterface
    {
        self::siteApplication();
        $selector = (int) self::$xparams->get('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase(), $selector);
    }

    /**
     * Parse safe positive database IDs from calendar configuration filters.
     * Supports comma-, space- and semicolon-separated decimal identifiers and deduplicates them, rejecting invalid arrays, partial numbers and overflowing values.
     *
     * @param mixed $values Scalar or array of configured project, team or club identifiers.
     * @return array<int,int> Unique validated positive database IDs.
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
                if (preg_match('/^[0-9]+$/D', $part) !== 1) {
                    continue;
                }

                $digits = ltrim($part, '0');
                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Parse a calendar date boundary without accepting PHP's rollover of invalid days.
     * Extracts the YYYY-MM-DD portion of the boundary and rejects impossible calendar dates instead of silently normalising them into another month.
     *
     * @param string $value Calendar date or date-time boundary string.
     * @return \DateTimeImmutable|null Valid requested day, or null.
     */
    private static function parseDate(string $value): ?\DateTimeImmutable
    {
        $value = substr(trim($value), 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof \DateTimeImmutable && $date->format('Y-m-d') === $value ? $date : null;
    }
}
