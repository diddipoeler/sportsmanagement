<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator list model for project referees.
 */
final class ProjectrefereesModel extends SportsManagementListModel
{
    public int $project_id = 0;
    public int $season_id = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'p.firstname', 'firstname',
            'p.lastname', 'lastname',
            'p.nickname', 'nickname',
            'p.phone', 'phone',
            'p.email', 'email',
            'p.mobile', 'mobile',
            'p.id', 'person_id',
            'pref.project_position_id', 'project_position_id',
            'pref.published', 'published', 'state',
            'pref.ordering', 'ordering',
            'pref.picture', 'picture',
            'pref.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    public function storeAssigned($cid, $project_id): int
    {
        $seasonPersonIds = $this->normaliseIds((array) $cid);
        $projectId = (int) $project_id;

        if (!$seasonPersonIds || $projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('person_id'))
            ->from($db->quoteName('#__sportsmanagement_project_referee'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);
        $current = array_flip(array_map('intval', $db->loadColumn() ?: []));
        $added = 0;
        $modified = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        foreach ($seasonPersonIds as $seasonPersonId) {
            if (isset($current[$seasonPersonId])) {
                continue;
            }

            try {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('p.picture'))
                    ->from($db->quoteName('#__sportsmanagement_season_person_id', 'sp'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_person', 'p')
                        . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('sp.person_id')
                    )
                    ->where($db->quoteName('sp.id') . ' = ' . $seasonPersonId)
                    ->where($db->quoteName('p.published') . ' = 1');
                $db->setQuery($query, 0, 1);
                $picture = $db->loadResult();

                if ($picture === null) {
                    continue;
                }

                $record = (object) [
                    'person_id' => $seasonPersonId,
                    'project_id' => $projectId,
                    'picture' => (string) $picture,
                    'modified' => $modified,
                    'modified_by' => $userId,
                ];
                $db->insertObject('#__sportsmanagement_project_referee', $record, 'id');
                $current[$seasonPersonId] = true;
                $added++;
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $added;
    }

    public function unassign($cid): int
    {
        $ids = $this->normaliseIds((array) $cid);

        if (!$ids) {
            return 0;
        }

        $db = $this->getDatabase();

        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_match_referee'))
                ->where($db->quoteName('project_referee_id') . ' IN (' . implode(',', $ids) . ')');
            $db->setQuery($query)->execute();

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_project_referee'))
                ->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
            $db->setQuery($query)->execute();

            return (int) $db->getAffectedRows();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return 0;
        }
    }

    public function getProjectRefereesCount($project_id): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project_referee'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    public function getItems2()
    {
        $db = $this->getDatabase();

        try {
            $db->setQuery($this->getListQuery());

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    public function getProject($project_id)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('st.name', 'sport_type_name'),
                $db->quoteName('st.eventtime', 'useeventtime'),
                $db->quoteName('l.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: false;
    }

    public function getProjectPositions($project_id, $persontype = 3): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('ppos.position_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . (int) $project_id)
            ->where($db->quoteName('pos.persontype') . ' = ' . (int) $persontype)
            ->order($db->quoteName('pos.ordering') . ' ASC');
        $db->setQuery($query);
        $positions = $db->loadObjectList() ?: [];

        foreach ($positions as $position) {
            $position->text = Text::_($position->text);
        }

        return $positions;
    }

    protected function populateState($ordering = 'p.lastname', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $projectId = $input->getInt('pid');

        if ($projectId <= 0) {
            $projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        }

        $this->project_id = max(0, $projectId);
        $this->season_id = (int) $app->getUserState('com_sportsmanagement.season_id', 0);
        $app->setUserState('com_sportsmanagement.pid', $this->project_id);
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
            $app->getUserStateFromRequest(
                $this->context . '.filter.search_nation',
                'filter_search_nation',
                '',
                'string'
            )
        );
        $this->setState(
            'filter.project_position_id',
            $app->getUserStateFromRequest(
                $this->context . '.filter.project_position_id',
                'filter_project_position_id',
                '',
                'string'
            )
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('tp.person_id', 'person_id'),
                $db->quoteName('tp.persontype'),
                $db->quoteName('tp.season_id'),
                $db->quoteName('tp.id', 'season_person_id'),
                $db->quoteName('pref.project_position_id'),
                $db->quoteName('pref.id', 'id'),
                $db->quoteName('pref.project_id'),
                $db->quoteName('pref.picture', 'picture'),
                $db->quoteName('pref.published', 'published'),
                $db->quoteName('pref.ordering', 'ordering'),
                $db->quoteName('pref.checked_out'),
                $db->quoteName('pref.checked_out_time'),
                $db->quoteName('u.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('p.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_referee', 'pref')
                . ' ON ' . $db->quoteName('pref.person_id') . ' = ' . $db->quoteName('tp.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pref.checked_out')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pref.project_id') . ' = ' . (int) $this->project_id);

        if ($this->season_id > 0) {
            $query->where($db->quoteName('tp.season_id') . ' = ' . $this->season_id);
        }

        $positionId = (int) $this->getState('filter.project_position_id');

        if ($positionId > 0) {
            $query->where($db->quoteName('pref.project_position_id') . ' = ' . $positionId);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where(
                '(LOWER(' . $db->quoteName('p.lastname') . ') LIKE LOWER(' . $token . ')'
                . ' OR LOWER(' . $db->quoteName('p.firstname') . ') LIKE LOWER(' . $token . ')'
                . ' OR LOWER(' . $db->quoteName('p.nickname') . ') LIKE LOWER(' . $token . '))'
            );
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('pref.published') . ' = ' . (int) $state);
        }

        $orderMap = [
            'p.firstname' => $db->quoteName('p.firstname'),
            'firstname' => $db->quoteName('p.firstname'),
            'p.lastname' => $db->quoteName('p.lastname'),
            'lastname' => $db->quoteName('p.lastname'),
            'p.nickname' => $db->quoteName('p.nickname'),
            'nickname' => $db->quoteName('p.nickname'),
            'p.phone' => $db->quoteName('p.phone'),
            'phone' => $db->quoteName('p.phone'),
            'p.email' => $db->quoteName('p.email'),
            'email' => $db->quoteName('p.email'),
            'p.mobile' => $db->quoteName('p.mobile'),
            'mobile' => $db->quoteName('p.mobile'),
            'p.id' => $db->quoteName('p.id'),
            'person_id' => $db->quoteName('p.id'),
            'pref.project_position_id' => $db->quoteName('pref.project_position_id'),
            'project_position_id' => $db->quoteName('pref.project_position_id'),
            'pref.published' => $db->quoteName('pref.published'),
            'published' => $db->quoteName('pref.published'),
            'state' => $db->quoteName('pref.published'),
            'pref.ordering' => $db->quoteName('pref.ordering'),
            'ordering' => $db->quoteName('pref.ordering'),
            'pref.picture' => $db->quoteName('pref.picture'),
            'picture' => $db->quoteName('pref.picture'),
            'pref.id' => $db->quoteName('pref.id'),
            'id' => $db->quoteName('pref.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'p.lastname');
        $listDirection = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['p.lastname']) . ' ' . $listDirection);

        return $query;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->project_id;
        $id .= ':' . $this->season_id;
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');
        $id .= ':' . (string) $this->getState('filter.project_position_id');

        return parent::getStoreId($id);
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map('intval', $ids),
                    static fn (int $id): bool => $id > 0
                )
            )
        );
    }
}
