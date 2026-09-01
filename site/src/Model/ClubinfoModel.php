<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

final class ClubinfoModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $clubid = 0;
    public static $club = null;
    public static int $new_club_id = 0;
    public static int $first_club_id = 0;
    public static string $historyhtml = '';
    public static array $historyobj = [];
    public static array $jgcat_rows = [];
    public static array $jgcat_rows_sorted = [];
    public static int $treedepth = 0;
    public static int $treedepthold = 0;
    public static int $cfg_which_database = 0;
    public static $tree_fusion = [];
    public static $arrPCat = [];
    public static string $historyhtmltree = '';

    public array $catssorted = [];

    private static $database = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->getProjectId();
        self::$clubid = max(0, $input->getInt('cid', 0));
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));
        self::$database = $this->getDatabase();

        if (self::$projectid <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::ERROR, 'jsmerror');
        }
    }

    public static function getFirstClubId($club_id = 0, $new_club_id = 0)
    {
        $clubId = max(0, (int) $club_id);
        $newClubId = max(0, (int) $new_club_id);
        $seen = [];

        while ($newClubId > 0 && !isset($seen[$newClubId])) {
            $seen[$newClubId] = true;
            $db = self::database();
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('new_club_id')])
                ->from($db->quoteName('#__sportsmanagement_club'))
                ->where($db->quoteName('id') . ' = ' . $newClubId);
            $db->setQuery($query, 0, 1);
            $club = $db->loadObject();

            if (!$club) {
                break;
            }

            $clubId = (int) $club->id;
            $newClubId = (int) ($club->new_club_id ?? 0);
        }

        self::$first_club_id = $clubId;

        return $clubId;
    }

    public static function generateTree($parent, $tree = 0): void
    {
        $parentId = (int) $parent;
        if (!is_array(self::$arrPCat) || !isset(self::$arrPCat[$parentId])) {
            return;
        }

        self::$historyhtmltree .= '<ul' . ($parentId === 0 ? ' class="tree"' : '') . '>';

        foreach (self::$arrPCat[$parentId] as $arrC) {
            $treespan = (int) $tree === 0
                ? '<span><i class="icon-minus-sign"></i>' . HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/arrow_left.png', '') . '</span>'
                : '';

            $name = (string) ($arrC['name'] ?? '');
            $logo = (string) ($arrC['logo_big'] ?? '');
            $link = (string) ($arrC['clublink'] ?? '');
            $color = (string) ($arrC['color'] ?? '');
            $id = (int) ($arrC['id'] ?? 0);

            self::$historyhtmltree .= '<li>' . $treespan
                . '<span style="background-color:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '">'
                . '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">'
                . HTMLHelper::_('image', $logo, $name, 'width="30"') . ' '
                . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                . '</a></span>';

            if ($id > 0 && $id !== $parentId) {
                self::generateTree($id, $tree);
            }

            self::$historyhtmltree .= '</li>';
        }

        self::$historyhtmltree .= '</ul>';
    }

    public static function fbTreeRecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
    {
        $id = (int) $id;
        $level = (int) $level;
        $maxlevel = (int) $maxlevel;

        if (!is_array($children) || !isset($children[$id]) || $level > $maxlevel) {
            return $list;
        }

        $spacer = $type ? '...' : '&nbsp;&nbsp;';
        foreach ($children[$id] as $value) {
            if (!is_object($value)) {
                continue;
            }

            $childId = (int) ($value->id ?? 0);
            if ($childId <= 0) {
                continue;
            }

            $list[$childId] = $value;
            $list = self::fbTreeRecurse(
                $childId,
                (string) $indent . $spacer,
                $list,
                $children,
                $maxlevel,
                $level + 1,
                $type
            );
        }

        return $list;
    }

    public static function getRssFeeds($rssfeedlink, $rssitems)
    {
        $limit = max(0, (int) $rssitems);
        foreach (explode(',', (string) $rssfeedlink) as $rssId) {
            $rssId = trim($rssId);
            if ($rssId === '') {
                continue;
            }

            try {
                $feed = (new FeedFactory())->getFeed($rssId);
                if ($limit > 0 && method_exists($feed, 'offsetUnset')) {
                    for ($i = count($feed) - 1; $i >= $limit; $i--) {
                        if (isset($feed[$i])) {
                            unset($feed[$i]);
                        }
                    }
                }
                return $feed;
            } catch (\InvalidArgumentException | \RuntimeException $e) {
                self::frontendApplication()->enqueueMessage(
                    Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'),
                    'notice'
                );
            }
        }

        return [];
    }

    public function getLogoHistory($clubId = 0, $seasonId = 0): array
    {
        $clubId = max(0, (int) $clubId);
        $seasonId = max(0, (int) $seasonId);
        if ($clubId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('cl.*, se.name AS seasonname')
            ->from($db->quoteName('#__sportsmanagement_club_logos', 'cl'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 'se') . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('cl.season_id'))
            ->where($db->quoteName('cl.club_id') . ' = ' . $clubId)
            ->order($db->quoteName('se.name') . ' DESC');

        if ($seasonId > 0) {
            $query->where($db->quoteName('se.id') . ' = ' . $seasonId);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->siteApplication()->enqueueMessage($e->getMessage(), 'warning');
            return [];
        }
    }

    public static function getClubAssociation($associations)
    {
        $associationId = max(0, (int) $associations);
        if ($associationId <= 0) {
            return null;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('asoc.*')
            ->from($db->quoteName('#__sportsmanagement_associations', 'asoc'))
            ->where($db->quoteName('asoc.id') . ' = ' . $associationId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public static function getFirstClub($club_id = 0)
    {
        $clubId = max(0, (int) $club_id);
        if ($clubId <= 0) {
            return null;
        }

        self::ensureHelpers();
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('c.*')
            ->select("CONCAT_WS(':', c.id, c.alias) AS club_slug")
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.id') . ' = ' . $clubId);
        $db->setQuery($query, 0, 1);
        $club = $db->loadObject();

        if (!$club) {
            return null;
        }

        $projectQuery = $db->getQuery(true)
            ->select("CONCAT_WS(':', p.id, p.alias)")
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $clubId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.id') . ' DESC');
        $db->setQuery($projectQuery, 0, 1);
        $club->pro_slug = $db->loadResult() ?: '0';
        $club->clublink = \sportsmanagementHelperRoute::getClubInfoRoute(
            $club->pro_slug,
            $club->club_slug,
            null,
            self::$cfg_which_database
        );

        return $club;
    }

    public static function getPlaygrounds($show_teams_of_club = 1)
    {
        $stadiums = self::getStadiums($show_teams_of_club);
        if ($stadiums === null) {
            return null;
        }

        $ids = [];
        foreach ((array) $stadiums as $stadium) {
            $id = (int) $stadium;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return [];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pl.id', 'value'),
                $db->quoteName('pl.name', 'text'),
                'pl.*',
                "CONCAT_WS(':', pl.id, pl.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'pl'))
            ->where($db->quoteName('pl.id') . ' IN (' . implode(',', array_values($ids)) . ')')
            ->order($db->quoteName('pl.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public static function getStadiums($show_teams_of_club = 1)
    {
        $club = self::getClub();
        if (!$club) {
            return null;
        }

        $stadiums = [];
        $standard = (int) ($club->standard_playground ?? 0);
        if ($standard > 0) {
            $stadiums[$standard] = $standard;
        }

        $teams = self::getTeamsByClubId($show_teams_of_club);
        if (!is_array($teams) || !$teams) {
            return array_values($stadiums);
        }

        $teamIds = [];
        foreach ($teams as $team) {
            $id = (int) ($team->id ?? 0);
            if ($id > 0) {
                $teamIds[$id] = $id;
            }
        }
        if (!$teamIds) {
            return array_values($stadiums);
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('pt.standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->where($db->quoteName('st.team_id') . ' IN (' . implode(',', array_values($teamIds)) . ')')
            ->where($db->quoteName('pt.standard_playground') . ' > 0');

        if ($standard > 0) {
            $query->where($db->quoteName('pt.standard_playground') . ' <> ' . $standard);
        }

        $db->setQuery($query);
        foreach ($db->loadColumn() ?: [] as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $stadiums[$id] = $id;
            }
        }

        return array_values($stadiums);
    }

    public static function getClub($inserthits = 0, $club_id = 0)
    {
        $input = self::frontendApplication()->getInput();
        $requestProjectId = max(0, $input->getInt('p', self::$projectid));
        if ($requestProjectId > 0) {
            self::$projectid = $requestProjectId;
        }

        if (self::$projectid <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::ERROR, 'jsmerror');
        }

        self::$clubid = (int) $club_id > 0
            ? (int) $club_id
            : max(0, $input->getInt('cid', self::$clubid));

        self::updateHits(self::$clubid, $inserthits);

        if (self::$club === null && self::$clubid > 0) {
            $db = self::database();
            $query = $db->getQuery(true)
                ->select('c.*')
                ->select("CONCAT_WS(':', c.id, c.alias) AS slug")
                ->from($db->quoteName('#__sportsmanagement_club', 'c'))
                ->where($db->quoteName('c.id') . ' = ' . self::$clubid);
            $db->setQuery($query, 0, 1);
            self::$club = $db->loadObject() ?: null;
        }

        return self::$club;
    }

    public static function updateHits($clubid = 0, $inserthits = 0): void
    {
        $clubId = max(0, (int) $clubid);
        if (!$inserthits || $clubId <= 0) {
            return;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_club'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = ' . $clubId);
        $db->setQuery($query);
        $db->execute();
    }

    public static function getTeamsByClubId($show_teams_of_club = 1)
    {
        if (self::$clubid <= 0 || self::$projectid <= 0) {
            return [0];
        }

        $started = microtime(true);
        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name', 'team_shortcut'),
                $db->quoteName('t.info', 'team_description'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                'COALESCE(('
                    . 'SELECT MAX(pt2.project_id) '
                    . 'FROM #__sportsmanagement_project_team AS pt2 '
                    . 'INNER JOIN #__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id '
                    . 'WHERE st2.team_id = t.id'
                    . '), 0) AS project_id',
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.club_id') . ' = ' . self::$clubid)
            ->order('project_id ASC');

        if ((int) $show_teams_of_club === 2) {
            $seasonIds = self::normaliseIds(
                ComponentHelper::getParams('com_sportsmanagement')->get('current_season', [])
            );
            if (!$seasonIds) {
                return [];
            }

            $query->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st_filter') . ' ON ' . $db->quoteName('st_filter.team_id') . ' = ' . $db->quoteName('t.id'))
                ->where($db->quoteName('st_filter.season_id') . ' IN (' . implode(',', $seasonIds) . ')')
                ->group([
                    $db->quoteName('t.id'),
                    $db->quoteName('t.name'),
                    $db->quoteName('t.short_name'),
                    $db->quoteName('t.info'),
                    $db->quoteName('t.alias'),
                ]);
        }

        try {
            $db->setQuery($query);
            $teams = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            self::frontendApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }

        foreach ($teams as $team) {
            $projectId = (int) ($team->project_id ?? 0);
            $team->ptid = null;
            $team->project_team_picture = null;
            $team->trikot_home = null;
            $team->trikot_away = null;
            $team->pid = 0;

            if ($projectId <= 0) {
                continue;
            }

            $projectTeamQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('pt.id', 'ptid'),
                    $db->quoteName('pt.picture', 'project_team_picture'),
                    $db->quoteName('pt.trikot_home'),
                    $db->quoteName('pt.trikot_away'),
                    "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                ])
                ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
                ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
                ->where($db->quoteName('st.team_id') . ' = ' . (int) $team->id);
            $db->setQuery($projectTeamQuery, 0, 1);
            $projectTeam = $db->loadObject();

            if ($projectTeam) {
                $team->ptid = $projectTeam->ptid;
                $team->project_team_picture = $projectTeam->project_team_picture;
                $team->trikot_home = $projectTeam->trikot_home;
                $team->trikot_away = $projectTeam->trikot_away;
                $team->pid = $projectTeam->project_slug;
            }
        }

        if (ComponentHelper::getParams('com_sportsmanagement')->get('show_query_debug_info')) {
            Log::add(
                json_encode([
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'zeit' => microtime(true) - $started,
                ]),
                Log::INFO,
                'dbperformance'
            );
        }

        return $teams;
    }

    public static function getClubHistory($clubid)
    {
        self::$historyobj = [];
        self::loadClubHistory(max(0, (int) $clubid), []);

        return self::$historyobj;
    }

    public static function getClubHistoryHTML($clubid)
    {
        self::ensureHelpers();
        self::$historyhtml = '';
        self::$treedepth = 0;
        self::$treedepthold = 0;
        self::$arrPCat = [];
        if (!is_array(self::$tree_fusion)) {
            self::$tree_fusion = [];
        }

        self::loadClubHistoryHtml(max(0, (int) $clubid), []);

        return self::$historyhtml;
    }

    public static function getClubHistoryTree($clubid, $new_club_id)
    {
        self::ensureHelpers();
        self::$jgcat_rows = [];
        self::$treedepth = 0;
        self::loadClubHistoryTree(
            max(0, (int) $clubid),
            max(0, (int) $new_club_id),
            []
        );

        return self::$jgcat_rows;
    }

    public static function getSortClubHistoryTree($clubtree, $root_catid, $cat_name)
    {
        $rootId = max(0, (int) $root_catid);
        $children = [];
        $sorted = [];
        $cats = is_array($clubtree) ? $clubtree : [];
        $children = self::sortCategoryList($cats, $sorted);

        $base = rtrim(Uri::base(), '/') . '/components/com_sportsmanagement/assets/img/standard2/';
        $var = 'd' . $rootId;
        $script = $var . " = new dTree('" . $var . "','" . addslashes($base) . "');\n";
        $script .= $var . ".add(0,-1,'" . addslashes((string) $cat_name) . "','','true');\n";

        foreach ($children as $rows) {
            foreach ((array) $rows as $row) {
                if (!is_object($row)) {
                    continue;
                }

                $id = (int) ($row->id ?? 0);
                $parentId = (int) ($row->new_club_id ?? 0);
                $parentNode = $parentId === $rootId ? 0 : $parentId;
                $label = (string) ($row->name ?? '') . ' (' . (string) ($row->founded_year ?? '') . ')';
                $link = (string) ($row->link ?? '');
                $icon = $base . (string) ($row->icon ?? 'from_club.png');

                $script .= $var . '.add(' . $id . ',' . $parentNode . ",'"
                    . addslashes($label) . "','" . addslashes($link) . "','','"
                    . addslashes($label) . "','','" . addslashes($icon) . "');\n";
            }
        }

        $script .= 'document.write(' . $var . ");\n";

        return $script;
    }

    public static function sortCategoryList(&$cats, &$catssorted)
    {
        $children = [];
        foreach ((array) $cats as $cat) {
            if (!is_object($cat)) {
                continue;
            }
            $parentId = (int) ($cat->new_club_id ?? 0);
            $children[$parentId][] = $cat;
        }

        self::sortCategoryListRecurse(0, $children, $catssorted);

        return $children;
    }

    public static function sortCategoryListRecurse($catid, &$children, &$catssorted): void
    {
        $catId = (int) $catid;
        if (!isset($children[$catId])) {
            return;
        }

        foreach ($children[$catId] as $cat) {
            $catssorted[] = $cat;
            $childId = (int) ($cat->id ?? $cat->cid ?? 0);
            if ($childId > 0 && $childId !== $catId) {
                self::sortCategoryListRecurse($childId, $children, $catssorted);
            }
        }
    }

    public static function getAddressString()
    {
        $club = self::getClub();
        if (!$club) {
            return null;
        }

        $parts = [];
        foreach (['address', 'state'] as $field) {
            $value = trim((string) ($club->{$field} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $location = trim((string) ($club->location ?? ''));
        $zipcode = trim((string) ($club->zipcode ?? ''));
        if ($location !== '') {
            $parts[] = trim($zipcode . ' ' . $location);
        }

        $country = trim((string) ($club->country ?? ''));
        if ($country !== '') {
            self::ensureHelpers();
            $parts[] = class_exists('JSMCountries')
                ? (string) \JSMCountries::getShortCountryName($country)
                : $country;
        }

        return implode(', ', $parts);
    }

    public function limitText($text, $wordcount)
    {
        $wordCount = max(0, (int) $wordcount);
        if ($wordCount <= 0) {
            return $text;
        }

        $words = preg_split('/\s+/', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) <= $wordCount) {
            return (string) $text;
        }

        return implode(' ', array_slice($words, 0, $wordCount)) . '...';
    }

    public function hasEditPermission($task = null): bool
    {
        $identity = $this->siteApplication()->getIdentity();
        $action = trim((string) $task);
        $allowed = $identity->authorise('core.edit', 'com_sportsmanagement');
        if (!$allowed && $action !== '') {
            $allowed = $identity->authorise($action, 'com_sportsmanagement');
        }

        if ((int) $identity->id > 0 && !$allowed) {
            $club = self::getClub();
            $allowed = $club && (int) ($club->admin ?? 0) === (int) $identity->id;
        }

        return $allowed;
    }

    private static function loadClubHistory(int $clubId, array $seen): void
    {
        if ($clubId <= 0 || isset($seen[$clubId])) {
            return;
        }
        $seen[$clubId] = true;

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name'),
                $db->quoteName('c.new_club_id'),
                "CONCAT_WS(':', c.id, c.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.new_club_id') . ' = ' . $clubId);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            self::$historyobj[] = (object) [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
            ];
            self::loadClubHistory((int) $row->id, $seen);
        }
    }

    private static function loadClubHistoryHtml(int $clubId, array $seen): void
    {
        if ($clubId <= 0 || isset($seen[$clubId])) {
            return;
        }
        $seen[$clubId] = true;

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name'),
                $db->quoteName('c.new_club_id'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.founded_year'),
                "CONCAT_WS(':', c.id, c.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.new_club_id') . ' = ' . $clubId);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $row->pid = self::findLatestProjectSlugForClub((int) $row->id);
            $parentId = (int) $row->new_club_id;

            if (!isset(self::$tree_fusion[$parentId]) || !is_array(self::$tree_fusion[$parentId])) {
                self::$tree_fusion[$parentId] = [];
            }
            self::$tree_fusion[$parentId][] = $row;

            if (!is_array(self::$arrPCat)) {
                self::$arrPCat = [];
            }
            self::$arrPCat[$parentId][] = [
                'id' => (int) $row->id,
                'name' => (string) $row->name . ' (' . (string) $row->founded_year . ')',
                'pid' => $row->pid,
                'slug' => (string) $row->slug,
                'color' => (int) $row->id === self::$clubid ? 'lawngreen' : '',
                'logo_big' => (string) ($row->logo_big ?? ''),
                'clublink' => \sportsmanagementHelperRoute::getClubInfoRoute($row->pid, $row->slug),
            ];

            $link = \sportsmanagementHelperRoute::getClubInfoRoute(
                $row->pid,
                $row->slug,
                null,
                self::$cfg_which_database
            );
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');
            self::$historyhtml .= '<li>'
                . HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/club_from.png', $imageTitle, 'title="' . $imageTitle . '"')
                . '&nbsp;' . HTMLHelper::link($link, (string) $row->name)
                . '</li>';

            if ((int) $row->id > 0) {
                self::$historyhtml .= '<ul>';
                self::loadClubHistoryHtml((int) $row->id, $seen);
                self::$historyhtml .= '</ul>';
            }
        }
    }

    private static function loadClubHistoryTree(int $clubId, int $newClubId, array $seen): void
    {
        if ($clubId <= 0 || isset($seen[$clubId])) {
            return;
        }
        $seen[$clubId] = true;

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name'),
                $db->quoteName('c.new_club_id'),
                $db->quoteName('c.founded_year'),
                "CONCAT_WS(':', c.id, c.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'));

        $icon = 'from_club.png';
        if (self::$new_club_id > 0) {
            $icon = 'to_club.png';
            $query->where($db->quoteName('c.id') . ' = ' . self::$new_club_id);
        } else {
            $query->where($db->quoteName('c.new_club_id') . ' = ' . $clubId);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $row->pid = self::findLatestProjectIdForClub((int) $row->id);
            $row->link = \sportsmanagementHelperRoute::getClubInfoRoute($row->pid, $row->slug);
            $row->icon = $icon;
            self::$jgcat_rows[] = $row;

            if ((int) $row->id > 0 && self::$new_club_id === 0) {
                self::loadClubHistoryTree((int) $row->id, (int) $row->new_club_id, $seen);
            }
        }
    }

    private static function findLatestProjectSlugForClub(int $clubId): string
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', p.id, p.alias)")
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $clubId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.id') . ' DESC');
        $db->setQuery($query, 0, 1);

        return (string) ($db->loadResult() ?: '0');
    }

    private static function findLatestProjectIdForClub(int $clubId): int
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('MAX(' . $db->quoteName('pt.project_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $clubId)
            ->where($db->quoteName('p.published') . ' = 1');
        $db->setQuery($query, 0, 1);

        return (int) ($db->loadResult() ?: 0);
    }

    private static function normaliseIds($value): array
    {
        $parts = is_array($value)
            ? $value
            : preg_split('/[|,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);

        $ids = [];
        foreach ($parts ?: [] as $part) {
            $id = (int) $part;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private static function frontendApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    private static function database(): DatabaseInterface
    {
        if (self::$database instanceof DatabaseInterface) {
            return self::$database;
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        self::$database = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            self::$cfg_which_database
        );

        return self::$database;
    }

    private static function ensureHelpers(): void
    {
        if (!class_exists('sportsmanagementHelperRoute')) {
            if (is_file(JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php')) {
                require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php';
            }
        }
        if (!class_exists('JSMCountries')) {
            if (is_file(JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php')) {
                require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php';
            }
        }
    }
}
