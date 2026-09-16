<?php
/**
 * Joomla 5/6 Clubinfo migration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
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
            $query = $db->createQuery()
                ->update($db->quoteName('#__sportsmanagement_club'))
                ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
                ->where($db->quoteName('id') . ' = :clubHitId')
                ->bind(':clubHitId', $clubId, ParameterType::INTEGER);
            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Throwable) {
                // A hit counter must never prevent the club page from loading.
            }
        }

        $query = $db->createQuery()
            ->select($db->quoteName('c') . '.*')
            ->select(
                "CONCAT_WS(':', " . $db->quoteName('c.id') . ', ' . $db->quoteName('c.alias') . ') AS ' . $db->quoteName('slug')
            )
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.id') . ' = :clubId')
            ->bind(':clubId', $clubId, ParameterType::INTEGER);

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
        $query = $db->createQuery()
            ->select($db->quoteName('a') . '.*')
            ->from($db->quoteName('#__sportsmanagement_associations', 'a'))
            ->where($db->quoteName('a.id') . ' = :associationId')
            ->bind(':associationId', $associationId, ParameterType::INTEGER);

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
        $latestProject = $db->createQuery()
            ->select('MAX(' . $db->quoteName('pt2.project_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt2'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->where($db->quoteName('st2.team_id') . ' = ' . $db->quoteName('t.id'));

        $latestProjectTeamSubquery = static function (string $column) use ($db) {
            return $db->createQuery()
                ->select($db->quoteName($column))
                ->from($db->quoteName('#__sportsmanagement_project_team', 'pt3'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st3')
                    . ' ON ' . $db->quoteName('st3.id') . ' = ' . $db->quoteName('pt3.team_id')
                )
                ->where($db->quoteName('st3.team_id') . ' = ' . $db->quoteName('t.id'))
                ->order($db->quoteName('pt3.project_id') . ' DESC')
                ->order($db->quoteName('pt3.id') . ' DESC')
                ->setLimit(1);
        };

        $latestProjectSlug = $db->createQuery()
            ->select(
                "CONCAT_WS(':', " . $db->quoteName('p3.id') . ', ' . $db->quoteName('p3.alias') . ')'
            )
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt3'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st3')
                . ' ON ' . $db->quoteName('st3.id') . ' = ' . $db->quoteName('pt3.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p3')
                . ' ON ' . $db->quoteName('p3.id') . ' = ' . $db->quoteName('pt3.project_id')
            )
            ->where($db->quoteName('st3.team_id') . ' = ' . $db->quoteName('t.id'))
            ->order($db->quoteName('pt3.project_id') . ' DESC')
            ->order($db->quoteName('pt3.id') . ' DESC')
            ->setLimit(1);

        $latestProjectTeamId = $latestProjectTeamSubquery('pt3.id');
        $latestProjectTeamPicture = $latestProjectTeamSubquery('pt3.picture');
        $latestHomeKit = $latestProjectTeamSubquery('pt3.trikot_home');
        $latestAwayKit = $latestProjectTeamSubquery('pt3.trikot_away');

        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name', 'team_shortcut'),
                $db->quoteName('t.info', 'team_description'),
                "CONCAT_WS(':', " . $db->quoteName('t.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('team_slug'),
                '(' . $latestProject . ') AS ' . $db->quoteName('project_id'),
                '(' . $latestProjectTeamId . ') AS ' . $db->quoteName('ptid'),
                '(' . $latestProjectTeamPicture . ') AS ' . $db->quoteName('project_team_picture'),
                '(' . $latestHomeKit . ') AS ' . $db->quoteName('trikot_home'),
                '(' . $latestAwayKit . ') AS ' . $db->quoteName('trikot_away'),
                '(' . $latestProjectSlug . ') AS ' . $db->quoteName('pid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.club_id') . ' = :teamsClubId')
            ->bind(':teamsClubId', $clubId, ParameterType::INTEGER)
            ->order($db->quoteName('project_id') . ' ASC, ' . $db->quoteName('t.name') . ' ASC');

        if ($mode === 2) {
            $seasonIds = $this->normaliseIds(
                ComponentHelper::getParams('com_sportsmanagement')->get('current_season', [])
            );
            if ($seasonIds === []) {
                return [];
            }

            $query
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st_filter')
                    . ' ON ' . $db->quoteName('st_filter.team_id') . ' = ' . $db->quoteName('t.id')
                )
                ->whereIn($db->quoteName('st_filter.season_id'), $seasonIds, ParameterType::INTEGER)
                ->distinct();
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
        $clubQuery = $db->createQuery()
            ->select($db->quoteName('standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->where($db->quoteName('id') . ' = :stadiumClubId')
            ->bind(':stadiumClubId', $clubId, ParameterType::INTEGER);

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

        $query = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('pt.standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->whereIn($db->quoteName('st.team_id'), array_values($teamIds), ParameterType::INTEGER)
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
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pl.id', 'value'),
                $db->quoteName('pl.name', 'text'),
                $db->quoteName('pl') . '.*',
                "CONCAT_WS(':', " . $db->quoteName('pl.id') . ', ' . $db->quoteName('pl.alias') . ') AS ' . $db->quoteName('slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'pl'))
            ->whereIn($db->quoteName('pl.id'), array_values($ids), ParameterType::INTEGER)
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
                $this->siteApplication()->enqueueMessage(
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
