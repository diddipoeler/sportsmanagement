<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 reader for presentation data used by the club-plan view.
 */
final class ClubplanViewDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * Return favorite-team and highlight settings indexed by project id.
     *
     * @param array<int, int> $projectIds
     * @return array<int, object>
     */
    public function getFavoriteSettings(array $projectIds): array
    {
        $ids = $this->normaliseIds($projectIds);
        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.fav_team'),
                $db->quoteName('p.fav_team_highlight_type'),
                $db->quoteName('p.fav_team_color'),
                $db->quoteName('p.fav_team_text_bold'),
                $db->quoteName('p.fav_team_text_color'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.id') . ' IN (' . implode(',', $ids) . ')');

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        $settings = [];
        foreach ($rows as $row) {
            $favoriteIds = [];
            foreach (explode(',', (string) ($row->fav_team ?? '')) as $value) {
                $teamId = (int) trim($value);
                if ($teamId > 0) {
                    $favoriteIds[$teamId] = $teamId;
                }
            }

            $row->favorite_team_ids = array_values($favoriteIds);
            $settings[(int) $row->id] = $row;
        }

        return $settings;
    }

    /**
     * Return published referees indexed by match id.
     *
     * @param array<int, int> $matchIds
     * @return array<int, array<int, object>>
     */
    public function getMatchReferees(array $matchIds): array
    {
        $ids = $this->normaliseIds($matchIds);
        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mr.match_id'),
                $db->quoteName('p.id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('mr.project_position_id'),
                $db->quoteName('pref.picture'),
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_referee', 'pref')
                . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_person_id', 'spi')
                . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pref.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('spi.person_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('mr.match_id') . ' IN (' . implode(',', $ids) . ')')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order([
                $db->quoteName('mr.match_id') . ' ASC',
                $db->quoteName('pos.name') . ' ASC',
                $db->quoteName('mr.ordering') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        $referees = [];
        foreach ($rows as $row) {
            $matchId = (int) ($row->match_id ?? 0);
            if ($matchId > 0) {
                $referees[$matchId][] = $row;
            }
        }

        return $referees;
    }

    /** @return array<int, int> */
    private function normaliseIds(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}
