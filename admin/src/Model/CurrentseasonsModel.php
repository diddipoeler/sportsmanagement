<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Read-only list of projects in the configured current seasons.
 *
 * This is intentionally independent of JSMModelList so it can be used as a
 * low-risk smoke path for the Joomla 5/6 MVCFactory.
 */
class CurrentseasonsModel extends SportsManagementListModel
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
        $query = $db->getQuery(true);

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
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('p.checked_out'));

        $currentSeasons = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
        $currentSeasons = is_array($currentSeasons) ? $currentSeasons : [$currentSeasons];
        $currentSeasons = array_values(array_filter(array_map('intval', $currentSeasons)));

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
