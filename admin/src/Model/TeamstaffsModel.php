<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for team staff assignments. */
final class TeamstaffsModel extends SportsManagementListModel
{
    private bool $useNewTable = false;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'ppl.lastname', 'lastname',
            'ppl.firstname', 'firstname',
            'ts.ordering', 'ordering',
            'ts.project_position_id', 'project_position_id',
            'ts.published', 'published', 'state',
            'ts.id', 'id',
        ];
        $this->useNewTable = defined('COM_SPORTSMANAGEMENT_USE_NEW_TABLE')
            && (bool) COM_SPORTSMANAGEMENT_USE_NEW_TABLE;

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'ts.ordering', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $option = 'com_sportsmanagement';
        $projectId = $input->getInt('pid') ?: (int) $app->getUserState($option . '.pid', 0);
        $seasonId = $input->getInt('season_id') ?: (int) $app->getUserState($option . '.season_id', 0);
        $teamId = $input->getInt('team_id') ?: (int) $app->getUserState($option . '.team_id', 0);
        $projectTeamId = $input->getInt('project_team_id')
            ?: (int) $app->getUserState($option . '.project_team_id', 0);

        $this->setState('context.project_id', $projectId);
        $this->setState('context.season_id', $seasonId);
        $this->setState('context.team_id', $teamId);
        $this->setState('context.project_team_id', $projectTeamId);

        foreach ([
            'pid' => $projectId,
            'season_id' => $seasonId,
            'team_id' => $teamId,
            'project_team_id' => $projectTeamId,
        ] as $key => $value) {
            if ($value > 0) {
                $app->setUserState($option . '.' . $key, $value);
            }
        }

        $search = (string) $app->getUserStateFromRequest(
            $option . 'ts_search',
            'search',
            '',
            'string'
        );
        $searchMode = (string) $app->getUserStateFromRequest(
            $option . 'ts_search_mode',
            'search_mode',
            '',
            'string'
        );
        $state = (string) $app->getUserStateFromRequest(
            $option . 'ts_filter_state',
            'filter_state',
            '',
            'word'
        );
        $this->setState('filter.search', mb_strtolower(trim($search)));
        $this->setState('filter.search_mode', $searchMode);
        $this->setState('filter.state', $state);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        if ($this->useNewTable) {
            $query->select([
                $db->quoteName('ppl.firstname'),
                $db->quoteName('ppl.lastname'),
                $db->quoteName('ppl.nickname'),
                $db->quoteName('ppl.injury'),
                $db->quoteName('ppl.suspension'),
                $db->quoteName('ppl.away'),
                $db->quoteName('ts') . '.*',
                $db->quoteName('u.name', 'editor'),
            ])
                ->from($db->quoteName('#__sportsmanagement_person', 'ppl'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_person_id', 'ts')
                    . ' ON ' . $db->quoteName('ts.person_id') . ' = ' . $db->quoteName('ppl.id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__users', 'u')
                    . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('ts.checked_out')
                )
                ->where($db->quoteName('ppl.published') . ' = 1')
                ->where($db->quoteName('ts.team_id') . ' = ' . (int) $this->getState('context.team_id'))
                ->where($db->quoteName('ts.season_id') . ' = ' . (int) $this->getState('context.season_id'))
                ->where($db->quoteName('ts.persontype') . ' = 2');
        } else {
            $query->select([
                $db->quoteName('ppl.firstname'),
                $db->quoteName('ppl.lastname'),
                $db->quoteName('ppl.nickname'),
                $db->quoteName('ts') . '.*',
                $db->quoteName('u.name', 'editor'),
            ])
                ->from($db->quoteName('#__sportsmanagement_person', 'ppl'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_team_staff', 'ts')
                    . ' ON ' . $db->quoteName('ts.person_id') . ' = ' . $db->quoteName('ppl.id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__users', 'u')
                    . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('ts.checked_out')
                )
                ->where($db->quoteName('ts.projectteam_id') . ' = ' . (int) $this->getState('context.project_team_id'))
                ->where($db->quoteName('ppl.published') . ' = 1');
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $escapedSearch = $db->escape($search, true);
            $needle = (string) $this->getState('filter.search_mode') !== ''
                ? $escapedSearch . '%'
                : '%' . $escapedSearch . '%';
            $query->where(
                'LOWER(' . $db->quoteName('ppl.lastname') . ') LIKE '
                . $db->quote($needle, false)
            );
        }

        $state = strtoupper((string) $this->getState('filter.state'));

        if ($state === 'P') {
            $query->where($db->quoteName('ts.published') . ' = 1');
        } elseif ($state === 'U') {
            $query->where($db->quoteName('ts.published') . ' = 0');
        }

        $orderMap = [
            'ppl.lastname' => $db->quoteName('ppl.lastname'),
            'lastname' => $db->quoteName('ppl.lastname'),
            'ppl.firstname' => $db->quoteName('ppl.firstname'),
            'firstname' => $db->quoteName('ppl.firstname'),
            'ts.ordering' => $db->quoteName('ts.ordering'),
            'ordering' => $db->quoteName('ts.ordering'),
            'ts.project_position_id' => $db->quoteName('ts.project_position_id'),
            'project_position_id' => $db->quoteName('ts.project_position_id'),
            'ts.published' => $db->quoteName('ts.published'),
            'published' => $db->quoteName('ts.published'),
            'state' => $db->quoteName('ts.published'),
            'ts.id' => $db->quoteName('ts.id'),
            'id' => $db->quoteName('ts.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'ts.ordering');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $primary = $orderMap[$ordering] ?? $orderMap['ts.ordering'];
        $query->order($primary . ' ' . $direction);

        if ($primary !== $db->quoteName('ppl.lastname')) {
            $query->order($db->quoteName('ppl.lastname') . ' ASC');
        }

        return $query;
    }

    /** Remove legacy team-staff assignments and their dependent match rows. */
    public function remove($cids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $cids))));

        if (!$ids) {
            return 0;
        }

        $db = $this->getDatabase();
        $idList = implode(',', $ids);
        $db->transactionStart();

        try {
            foreach (['#__sportsmanagement_match_staff', '#__sportsmanagement_match_staff_statistic'] as $table) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName($table))
                    ->where($db->quoteName('team_staff_id') . ' IN (' . $idList . ')');
                $db->setQuery($query)->execute();
            }

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_team_staff'))
                ->where($db->quoteName('id') . ' IN (' . $idList . ')');
            $db->setQuery($query)->execute();
            $count = $db->getAffectedRows();
            $db->transactionCommit();

            return $count;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError(Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_STAFF',
                $e->getMessage()
            ));

            return 0;
        }
    }
}
