<?php
/**
 * Native Joomla 5/6 list model for projects in current seasons.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/**
 * Read-only list of projects in the configured current seasons.
 */
final class CurrentseasonsModel extends SportsManagementListModel
{
    /**
     * Initialise the current-season projects list with sortable fields.
     * @param array $config Administrator list-model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'p.name',
            'project',
            'season',
            'league',
            'sportstype',
        ];

        parent::__construct($config, $factory);
    }

    /**
     * Initialise the current-season list ordering via the parent model.
     * @param string $ordering Default project sort column.
     * @param string $direction Default sort direction.
     * @return void
     */
    protected function populateState($ordering = 'p.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
    }

    /**
     * Build a read-only project overview for configured current seasons.
     * Aggregates division, team, position, referee and round counts,
     * filters the configured season IDs and allowlists ordering fields.
     * @return mixed Joomla database query for current-season projects.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();

        $divisionCount = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = ' . $db->quoteName('p.id'));
        $positionCount = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->where($db->quoteName('pp.project_id') . ' = ' . $db->quoteName('p.id'));
        $refereeCount = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->where($db->quoteName('pr.project_id') . ' = ' . $db->quoteName('p.id'));
        $teamCount = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id'));
        $roundCount = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $db->quoteName('p.id'));

        $query = $db->createQuery();
        $query
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.project_art_id'),
                $db->quoteName('p.project_type'),
                $db->quoteName('p.name'),
                $db->quoteName('p.teams_as_referees'),
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('s.name', 'season'),
                $db->quoteName('l.name', 'league'),
                $db->quoteName('l.country', 'country'),
                $db->quoteName('u.name', 'editor'),
                '(' . $divisionCount . ') AS ' . $db->quoteName('count_projectdivisions'),
                '(' . $positionCount . ') AS ' . $db->quoteName('count_projectpositions'),
                '(' . $refereeCount . ') AS ' . $db->quoteName('count_projectreferees'),
                '(' . $teamCount . ') AS ' . $db->quoteName('count_projectteams'),
                '(' . $roundCount . ') AS ' . $db->quoteName('count_matchdays'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('p.checked_out'));

        $currentSeasons = self::normaliseSeasonIds(
            ComponentHelper::getParams('com_sportsmanagement')->get('current_season', [])
        );

        if ($currentSeasons) {
            $query->whereIn($db->quoteName('p.season_id'), $currentSeasons, ParameterType::INTEGER);
        } else {
            // No valid current season is not a request for all projects.
            $query->where('1 = 0');
        }

        $ordering = $this->getState('list.ordering', 'p.name');
        $rawDirection = $this->getState('list.direction', 'ASC');
        $direction = is_string($rawDirection) && strtoupper($rawDirection) === 'DESC' ? 'DESC' : 'ASC';

        $allowedOrdering = [
            'p.name' => $db->quoteName('p.name'),
            'project' => $db->quoteName('p.name'),
            'season' => $db->quoteName('s.name'),
            'league' => $db->quoteName('l.name'),
            'sportstype' => $db->quoteName('st.name'),
        ];

        $column = is_string($ordering) ? ($allowedOrdering[$ordering] ?? $allowedOrdering['p.name']) : $allowedOrdering['p.name'];
        $query->order($column . ' ' . $direction);

        return $query;
    }

    /**
     * Parse the configured current seasons without lossy integer casts.
     *
     * Accepts positive integer IDs and bounded decimal strings, including
     * zero-padded historical selections. Deduplicates valid values and
     * bounds the number of bound query parameters. A missing or invalid
     * selection yields no IDs rather than an unrestricted project list.
     *
     * @param mixed $value Raw component season configuration.
     * @return array<int,int> At most 200 unique positive season IDs.
     */
    private static function normaliseSeasonIds(mixed $value): array
    {
        if (is_string($value)) {
            if (strlen($value) > 4096) {
                return [];
            }
            $value = preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $ids = [];
        foreach (array_slice(is_array($value) ? $value : [$value], 0, 1000) as $candidate) {
            if (is_string($candidate)) {
                if (strlen($candidate) > 128 || preg_match('/^[0-9]+$/D', $candidate) !== 1) {
                    continue;
                }
                $candidate = filter_var(ltrim($candidate, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            }

            if (is_int($candidate) && $candidate > 0) {
                $ids[$candidate] = $candidate;
                if (count($ids) >= 200) {
                    break;
                }
            }
        }

        return array_values($ids);
    }

}
