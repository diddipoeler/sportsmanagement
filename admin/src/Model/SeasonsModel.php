<?php
/**
 * Native Joomla 5/6 season list model for SportsManagement.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 season list model.
 *
 * Besides the standard season list, this model preserves the legacy
 * assignteams/assignpersons query paths and helper methods so the old
 * admin/models/seasons.php file can remain a compatibility-only bridge.
 */
final class SeasonsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            's.name', 'name',
            's.alias', 'alias',
            's.id', 'id',
            's.ordering', 'ordering',
            's.published', 'published', 'state',
            's.modified', 'modified',
            's.modified_by', 'modified_by',
            's.checked_out', 'checked_out',
            's.checked_out_time', 'checked_out_time',
            't.name',
            'p.lastname',
            'p.firstname',
            'p.nickname',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 's.name', $direction = 'DESC')
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $layout = $input->getCmd('layout');

        $defaultOrdering = match ($layout) {
            'assignteams' => 't.name',
            'assignpersons' => 'p.lastname',
            default => 's.name',
        };
        $defaultDirection = $layout === 'assignteams' || $layout === 'assignpersons' ? 'ASC' : 'DESC';

        parent::populateState($defaultOrdering, $defaultDirection);

        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
        $this->setState(
            'filter.search_nation',
            $app->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '', 'string')
        );

        $seasonId = $input->getInt('id');

        if ($seasonId <= 0) {
            $seasonId = $input->getInt('season_id');
        }

        $this->setState('layout', $layout);
        $this->setState('season.id', max(0, $seasonId));

        // Preserve the ordering used by the legacy assignment layouts.
        if ($layout === 'assignteams' || $layout === 'assignpersons') {
            $this->setState('list.ordering', $defaultOrdering);
            $this->setState('list.direction', $defaultDirection);
        }
    }

    protected function getListQuery()
    {
        $layout = (string) $this->getState('layout');
        $seasonId = (int) $this->getState('season.id');

        return match ($layout) {
            'assignteams' => $this->getAssignTeamsQuery($seasonId),
            'assignpersons' => $this->getAssignPersonsQuery($seasonId),
            default => $this->getDefaultSeasonsQuery(),
        };
    }

    private function getDefaultSeasonsQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.name'),
                $db->quoteName('s.alias'),
                $db->quoteName('s.published'),
                $db->quoteName('s.ordering'),
                $db->quoteName('s.checked_out'),
                $db->quoteName('s.checked_out_time'),
                $db->quoteName('s.modified'),
                $db->quoteName('s.modified_by'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('s.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('s.name') . ') LIKE LOWER(:seasonSearch)')
                ->bind(':seasonSearch', $token, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $publishedState = (int) $state;
            $query->where($db->quoteName('s.published') . ' = :seasonPublished')
                ->bind(':seasonPublished', $publishedState, ParameterType::INTEGER);
        }

        $orderMap = [
            's.name' => $db->quoteName('s.name'),
            'name' => $db->quoteName('s.name'),
            's.alias' => $db->quoteName('s.alias'),
            'alias' => $db->quoteName('s.alias'),
            's.published' => $db->quoteName('s.published'),
            'published' => $db->quoteName('s.published'),
            'state' => $db->quoteName('s.published'),
            's.ordering' => $db->quoteName('s.ordering'),
            'ordering' => $db->quoteName('s.ordering'),
            's.id' => $db->quoteName('s.id'),
            'id' => $db->quoteName('s.id'),
            's.modified' => $db->quoteName('s.modified'),
            'modified' => $db->quoteName('s.modified'),
            's.modified_by' => $db->quoteName('s.modified_by'),
            'modified_by' => $db->quoteName('s.modified_by'),
        ];

        $ordering = (string) $this->getState('list.ordering', 's.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $query->order(($orderMap[$ordering] ?? $orderMap['s.name']) . ' ' . $direction);

        return $query;
    }

    private function getAssignTeamsQuery(int $seasonId)
    {
        $db = $this->getDatabase();
        $subQuery = $db->getQuery(true)
            ->select($db->quoteName('stp.team_id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_id', 'stp'))
            ->where($db->quoteName('stp.season_id') . ' = :assignedTeamSeasonId');

        $query = $db->getQuery(true)
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->where($db->quoteName('t.id') . ' NOT IN (' . $subQuery . ')')
            ->bind(':assignedTeamSeasonId', $seasonId, ParameterType::INTEGER);

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('c.country') . ' = :assignedTeamCountry')
                ->bind(':assignedTeamCountry', $country, ParameterType::STRING);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('t.name') . ') LIKE LOWER(:assignedTeamSearch)')
                ->bind(':assignedTeamSearch', $token, ParameterType::STRING);
        }

        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order($db->quoteName('t.name') . ' ' . $direction);

        return $query;
    }

    private function getAssignPersonsQuery(int $seasonId)
    {
        $db = $this->getDatabase();
        $subQuery = $db->getQuery(true)
            ->select($db->quoteName('stp.person_id'))
            ->from($db->quoteName('#__sportsmanagement_season_person_id', 'stp'))
            ->where($db->quoteName('stp.season_id') . ' = :assignedPersonSeasonId');

        $query = $db->getQuery(true)
            ->select($db->quoteName('p') . '.*')
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->where($db->quoteName('p.id') . ' NOT IN (' . $subQuery . ')')
            ->bind(':assignedPersonSeasonId', $seasonId, ParameterType::INTEGER);

        if ($seasonId > 0) {
            $seasonName = $this->getSeasonName($seasonId);

            if (preg_match('/^(\d{4})/', $seasonName, $match)) {
                $birthdayCutoff = $match[1] . '-01-01';
                $query->where($db->quoteName('p.birthday') . ' < :birthdayCutoff')
                    ->bind(':birthdayCutoff', $birthdayCutoff, ParameterType::STRING);
            }
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('p.country') . ' = :assignedPersonCountry')
                ->bind(':assignedPersonCountry', $country, ParameterType::STRING);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where(
                '('
                . 'LOWER(' . $db->quoteName('p.lastname') . ') LIKE LOWER(:personLastSearch)'
                . ' OR LOWER(' . $db->quoteName('p.firstname') . ') LIKE LOWER(:personFirstSearch)'
                . ' OR LOWER(' . $db->quoteName('p.nickname') . ') LIKE LOWER(:personNickSearch)'
                . ' OR LOWER(' . $db->quoteName('p.info') . ') LIKE LOWER(:personInfoSearch)'
                . ')'
            )
                ->bind(':personLastSearch', $token, ParameterType::STRING)
                ->bind(':personFirstSearch', $token, ParameterType::STRING)
                ->bind(':personNickSearch', $token, ParameterType::STRING)
                ->bind(':personInfoSearch', $token, ParameterType::STRING);
        }

        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order($db->quoteName('p.lastname') . ' ' . $direction);

        return $query;
    }

    public function getSeasonTeams($seasonId = 0): array
    {
        $db = $this->getDatabase();
        $seasonId = (int) $seasonId;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('st.season_id') . ' = :seasonTeamsSeasonId')
            ->bind(':seasonTeamsSeasonId', $seasonId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getSeasonName($seasonId = 0): string
    {
        $db = $this->getDatabase();
        $seasonId = (int) $seasonId;
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->where($db->quoteName('id') . ' = :seasonNameId')
            ->bind(':seasonNameId', $seasonId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (string) ($db->loadResult() ?? '');
    }

    public function getSeasons(bool $selectOptions = false): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                $selectOptions
                    ? [$db->quoteName('id', 'value'), $db->quoteName('name', 'text')]
                    : [$db->quoteName('id'), $db->quoteName('name')]
            )
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->order($db->quoteName('name') . ' DESC');

        $db->setQuery($query);
        $seasons = $db->loadObjectList() ?: [];

        foreach ($seasons as $season) {
            if ($selectOptions) {
                $season->text = Text::_($season->text);
            } else {
                $season->name = Text::_($season->name);
            }
        }

        return $seasons;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (string) $this->getState('layout');
        $id .= ':' . (int) $this->getState('season.id');
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.search_nation');
        $id .= ':' . (string) $this->getState('filter.state');

        return parent::getStoreId($id);
    }
}
