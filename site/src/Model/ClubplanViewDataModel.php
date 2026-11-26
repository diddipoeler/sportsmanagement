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
use Joomla\Database\ParameterType;
use Throwable;

/**
 * Native Joomla 5/6 reader for presentation data used by the club-plan view.
 */
final class ClubplanViewDataModel extends SportsManagementProjectModel
{
    /**
     * Initialise the Joomla 5/6 club-plan presentation reader.
     *
     * Delegate the MVC factory and configuration to the project-bound
     * base model so database selection and current project context are
     * resolved consistently for view-data queries.
     *
     * @param array $config Joomla MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * Return favorite-team and highlight settings indexed by project id.
     * Filters malformed or out-of-scope rows, validates favorite team IDs
     * and isolates failures of optional presentation data queries.
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

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('p.id'),
                    $db->quoteName('p.fav_team'),
                    $db->quoteName('p.fav_team_highlight_type'),
                    $db->quoteName('p.fav_team_color'),
                    $db->quoteName('p.fav_team_text_bold'),
                    $db->quoteName('p.fav_team_text_color'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project', 'p'))
                ->whereIn($db->quoteName('p.id'), $ids, ParameterType::INTEGER);
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        $settings = [];
        $allowed = array_fill_keys($ids, true);
        foreach (is_array($rows) ? $rows : [] as $row) {
            $projectId = is_object($row) ? ($this->normaliseIds([$row->id ?? null])[0] ?? 0) : 0;
            if (!isset($allowed[$projectId])) {
                continue;
            }

            $favorites = $row->fav_team ?? '';
            $row->favorite_team_ids = is_string($favorites)
                ? $this->normaliseIds(array_map('trim', explode(',', $favorites))) : [];
            $settings[$projectId] = $row;
        }

        return $settings;
    }

    /**
     * Return published referees indexed by match id.
     * Rejects malformed and unrelated match records and keeps the schedule
     * usable if the optional query cannot be constructed or executed.
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

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('mr.match_id'),
                    $db->quoteName('p.id'),
                    $db->quoteName('p.firstname'),
                    $db->quoteName('p.nickname'),
                    $db->quoteName('p.lastname'),
                    $db->quoteName('pos.name', 'position_name'),
                    $db->quoteName('mr.project_position_id'),
                    $db->quoteName('pref.picture'),
                    "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('person_slug'),
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
                ->whereIn($db->quoteName('mr.match_id'), $ids, ParameterType::INTEGER)
                ->where($db->quoteName('p.published') . ' = 1')
                ->order([
                    $db->quoteName('mr.match_id') . ' ASC',
                    $db->quoteName('pos.name') . ' ASC',
                    $db->quoteName('mr.ordering') . ' ASC',
                ]);
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        $referees = [];
        $allowed = array_fill_keys($ids, true);
        foreach (is_array($rows) ? $rows : [] as $row) {
            $matchId = is_object($row) ? ($this->normaliseIds([$row->match_id ?? null])[0] ?? 0) : 0;
            if (isset($allowed[$matchId])) {
                $referees[$matchId][] = $row;
            }
        }

        return $referees;
    }

    /**
     * Validate project, match and favorite-team IDs for bound queries.
     * Accepts positive integers and decimal strings including leading zeros,
     * rejecting nested values, booleans, overflow and numeric suffixes.
     * Preserves first occurrence order while removing duplicates.
     * @param array $values Raw identifiers.
     * @return array<int,int> Unique positive integer IDs.
     */
    private function normaliseIds(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if (is_string($value)) {
                if (strlen($value) > 128 || preg_match('/^[0-9]+$/D', $value) !== 1) {
                    continue;
                }
                $value = filter_var(ltrim($value, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            }
            if (is_int($value) && $value > 0) {
                $ids[$value] = $value;
            }
        }

        return array_values($ids);
    }
}
