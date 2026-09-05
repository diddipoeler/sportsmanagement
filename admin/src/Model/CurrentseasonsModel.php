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

/**
 * Read-only list of projects in the configured current seasons.
 */
final class CurrentseasonsModel extends SportsManagementListModel
{
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

    protected function populateState($ordering = 'p.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
    }

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

        $currentSeasons = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);

        if (is_string($currentSeasons)) {
            $currentSeasons = preg_split('/\s*,\s*/', trim($currentSeasons), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } elseif (!is_array($currentSeasons)) {
            $currentSeasons = $currentSeasons === null || $currentSeasons === '' ? [] : [$currentSeasons];
        }

        $currentSeasons = array_values(array_unique(array_filter(
            array_map('intval', $currentSeasons),
            static fn (int $id): bool => $id > 0
        )));

        if ($currentSeasons) {
            $query->whereIn($db->quoteName('p.season_id'), $currentSeasons);
        }

        $ordering = $this->getState('list.ordering', 'p.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $allowedOrdering = [
            'p.name' => $db->quoteName('p.name'),
            'project' => $db->quoteName('p.name'),
            'season' => $db->quoteName('s.name'),
            'league' => $db->quoteName('l.name'),
            'sportstype' => $db->quoteName('st.name'),
        ];

        $query->order(($allowedOrdering[$ordering] ?? $allowedOrdering['p.name']) . ' ' . $direction);

        return $query;
    }
}
