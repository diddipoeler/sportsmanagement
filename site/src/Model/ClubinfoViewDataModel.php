<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 read model for Clubinfo view data that does not depend on
 * ClubinfoModel's historical static state.
 */
final class ClubinfoViewDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function getClubById(int $clubId, bool $incrementHits = false): ?object
    {
        if ($clubId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        if ($incrementHits) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sportsmanagement_club'))
                ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
                ->where($db->quoteName('id') . ' = ' . $clubId);
            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Throwable) {
                // A hit counter must never prevent the club page from loading.
            }
        }

        $query = $db->getQuery(true)
            ->select('c.*')
            ->select("CONCAT_WS(':', c.id, c.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.id') . ' = ' . $clubId);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    public function getAssociationById(int $associationId): ?object
    {
        if ($associationId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName('#__sportsmanagement_associations', 'a'))
            ->where($db->quoteName('a.id') . ' = ' . $associationId);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    public function getTeamsByClub(int $clubId, int $mode = 1): array
    {
        if ($clubId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $latestProject = '(SELECT MAX(pt2.project_id)'
            . ' FROM #__sportsmanagement_project_team AS pt2'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id'
            . ' WHERE st2.team_id = t.id)';
        $latestProjectTeamId = '(SELECT pt3.id'
            . ' FROM #__sportsmanagement_project_team AS pt3'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st3 ON st3.id = pt3.team_id'
            . ' WHERE st3.team_id = t.id'
            . ' ORDER BY pt3.project_id DESC, pt3.id DESC LIMIT 1)';
        $latestProjectTeamPicture = '(SELECT pt3.picture'
            . ' FROM #__sportsmanagement_project_team AS pt3'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st3 ON st3.id = pt3.team_id'
            . ' WHERE st3.team_id = t.id'
            . ' ORDER BY pt3.project_id DESC, pt3.id DESC LIMIT 1)';
        $latestHomeKit = '(SELECT pt3.trikot_home'
            . ' FROM #__sportsmanagement_project_team AS pt3'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st3 ON st3.id = pt3.team_id'
            . ' WHERE st3.team_id = t.id'
            . ' ORDER BY pt3.project_id DESC, pt3.id DESC LIMIT 1)';
        $latestAwayKit = '(SELECT pt3.trikot_away'
            . ' FROM #__sportsmanagement_project_team AS pt3'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st3 ON st3.id = pt3.team_id'
            . ' WHERE st3.team_id = t.id'
            . ' ORDER BY pt3.project_id DESC, pt3.id DESC LIMIT 1)';
        $latestProjectSlug = '(SELECT CONCAT_WS(\':\', p3.id, p3.alias)'
            . ' FROM #__sportsmanagement_project_team AS pt3'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st3 ON st3.id = pt3.team_id'
            . ' INNER JOIN #__sportsmanagement_project AS p3 ON p3.id = pt3.project_id'
            . ' WHERE st3.team_id = t.id'
            . ' ORDER BY pt3.project_id DESC, pt3.id DESC LIMIT 1)';

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name', 'team_shortcut'),
                $db->quoteName('t.info', 'team_description'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                $latestProject . ' AS project_id',
                $latestProjectTeamId . ' AS ptid',
                $latestProjectTeamPicture . ' AS project_team_picture',
                $latestHomeKit . ' AS trikot_home',
                $latestAwayKit . ' AS trikot_away',
                $latestProjectSlug . ' AS pid',
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.club_id') . ' = ' . $clubId)
            ->order('project_id ASC, ' . $db->quoteName('t.name') . ' ASC');

        if ($mode === 2) {
            $seasonIds = $this->normaliseIds(
                ComponentHelper::getParams('com_sportsmanagement')->get('current_season', [])
            );
            if ($seasonIds === []) {
                return [];
            }

            $query->where(
                'EXISTS (SELECT 1 FROM #__sportsmanagement_season_team_id AS st_filter'
                . ' WHERE st_filter.team_id = t.id'
                . ' AND st_filter.season_id IN (' . implode(',', $seasonIds) . '))'
            );
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function getStadiumIds(int $clubId, array $teams): array
    {
        if ($clubId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $ids = [];
        $clubQuery = $db->getQuery(true)
            ->select($db->quoteName('standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->where($db->quoteName('id') . ' = ' . $clubId);

        try {
            $db->setQuery($clubQuery, 0, 1);
            $standard = (int) $db->loadResult();
            if ($standard > 0) {
                $ids[$standard] = $standard;
            }
        } catch (Throwable) {
            $standard = 0;
        }

        $teamIds = [];
        foreach ($teams as $team) {
            $teamId = (int) ($team->id ?? 0);
            if ($teamId > 0) {
                $teamIds[$teamId] = $teamId;
            }
        }
        if ($teamIds === []) {
            return array_values($ids);
        }

        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('pt.standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->where($db->quoteName('st.team_id') . ' IN (' . implode(',', array_values($teamIds)) . ')')
            ->where($db->quoteName('pt.standard_playground') . ' > 0');

        try {
            $db->setQuery($query);
            foreach ($db->loadColumn() ?: [] as $value) {
                $id = (int) $value;
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        } catch (Throwable) {
            // Return the club standard playground if the team lookup fails.
        }

        return array_values($ids);
    }

    public function getPlaygroundsByIds(array $stadiumIds): array
    {
        $ids = [];
        foreach ($stadiumIds as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
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

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function getRssFeeds(string $feedLinks, int $limit): mixed
    {
        $limit = max(0, $limit);
        foreach (explode(',', $feedLinks) as $feedLink) {
            $feedLink = trim($feedLink);
            if ($feedLink === '') {
                continue;
            }

            try {
                $feed = (new FeedFactory())->getFeed($feedLink);
                if ($limit > 0 && method_exists($feed, 'offsetUnset')) {
                    for ($i = count($feed) - 1; $i >= $limit; $i--) {
                        if (isset($feed[$i])) {
                            unset($feed[$i]);
                        }
                    }
                }
                return $feed;
            } catch (\InvalidArgumentException | \RuntimeException) {
                Factory::getApplication()->enqueueMessage(
                    Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'),
                    'notice'
                );
            }
        }

        return [];
    }

    /** @return array<int, int> */
    private function normaliseIds(mixed $value): array
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
}
