<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 administrator list model for prediction games.
 */
final class PredictiongamesModel extends SportsManagementListModel
{
    public int $prediction_id = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'pre.name', 'name',
            'pre.published', 'published', 'state',
            'pre.id', 'id',
            'pre.ordering', 'ordering',
            'pre.modified', 'modified',
            'pre.modified_by', 'modified_by',
        ];

        parent::__construct($config, $factory);

        $requestedPredictionId = Factory::getApplication()->getInput()->getInt('prediction_id');

        if ($requestedPredictionId > 0) {
            Factory::getApplication()->setUserState(
                $this->context . '.filter.prediction_id',
                $requestedPredictionId
            );
        }
    }

    public static function getAdmins($pred_id = 0, $list = false)
    {
        $predictionId = (int) $pred_id;

        if ($predictionId <= 0) {
            return false;
        }

        $db = self::getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select($list ? $db->quoteName('user_id', 'value') : $db->quoteName('user_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_admin'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('user_id') . ' ASC');
        $db->setQuery($query);

        return $list ? ($db->loadObjectList() ?: []) : array_map('intval', $db->loadColumn() ?: []);
    }

    public function getChilds($pred_id, $all = false)
    {
        if (is_array($pred_id)) {
            return [];
        }

        $predictionId = (int) $pred_id;

        if ($predictionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($all ? $db->quoteName('pro.project_id') : $db->quoteName('pro') . '.*')
            ->from($db->quoteName('#__sportsmanagement_prediction_project', 'pro'))
            ->where($db->quoteName('pro.prediction_id') . ' = ' . $predictionId)
            ->where($db->quoteName('pro.project_id') . ' != 0');

        if (!$all) {
            $query->select($db->quoteName('joo.name', 'project_name'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project', 'joo')
                    . ' ON ' . $db->quoteName('joo.id') . ' = ' . $db->quoteName('pro.project_id')
                );
        }

        try {
            $db->setQuery($query);

            if ($all) {
                return array_map('intval', $db->loadColumn() ?: []);
            }

            return $db->loadAssocList('id') ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    public function getPredictionGamesMatches($predictionGameID, $predictionProjectID, $userID): array
    {
        $predictionGameId = (int) $predictionGameID;
        $projectId = (int) $predictionProjectID;
        $userId = (int) $userID;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.round_id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.team1_result_decision'),
                $db->quoteName('m.team2_result_decision'),
                $db->quoteName('r.id', 'roundcode'),
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                $db->quoteName('pr.tipp'),
                $db->quoteName('pr.tipp_home'),
                $db->quoteName('pr.tipp_away'),
                $db->quoteName('pr.joker'),
                $db->quoteName('pr.id', 'prid'),
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('t1.name', 'home_name'),
                $db->quoteName('t2.name', 'away_name'),
                $db->quoteName('c1.logo_big', 'home_logo_big'),
                $db->quoteName('c1.country', 'home_country'),
                $db->quoteName('c2.logo_big', 'away_logo_big'),
                $db->quoteName('c2.country', 'away_country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.current_round') . ' = ' . $db->quoteName('r.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_prediction_result', 'pr')
                . ' ON ' . $db->quoteName('pr.match_id') . ' = ' . $db->quoteName('m.id')
                . ' AND ' . $db->quoteName('pr.prediction_id') . ' = ' . $predictionGameId
                . ' AND ' . $db->quoteName('pr.user_id') . ' = ' . $userId
                . ' AND ' . $db->quoteName('pr.project_id') . ' = ' . $projectId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c1')
                . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't2')
                . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c2')
                . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.match_date') . ' <> ' . $db->quote('0000-00-00 00:00:00'))
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->order($db->quoteName('m.match_date') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    public function getPredictionGames(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->order($db->quoteName('name') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    public function getPredictionGame($prediction_id)
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . $predictionId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    public function getActivePredictionRoundsCount($prediction_id): int
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->where($db->quoteName('published') . ' = 1');

        try {
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return 0;
        }
    }

    public function getProjectRoundsCount($project_id): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);

        try {
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return 0;
        }
    }

    protected function populateState($ordering = 'pre.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $oldPredictionId = (int) $app->getUserState($this->context . '.filter.prediction_id', 0);
        $filterPredictionId = (int) $app->getUserStateFromRequest(
            $this->context . '.filter.prediction_id',
            'filter_prediction_id',
            0,
            'int'
        );
        $requestedPredictionId = $input->getInt('prediction_id');
        $predictionId = $filterPredictionId;

        if ($requestedPredictionId > 0) {
            if ($filterPredictionId <= 0 || $oldPredictionId === $filterPredictionId) {
                $predictionId = $requestedPredictionId;
            } else {
                $app->redirect('index.php?option=com_sportsmanagement&view=predictiongames');
            }
        }

        $this->prediction_id = max(0, $predictionId);
        $this->setState('filter.prediction_id', $this->prediction_id);
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pre') . '.*',
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('u1.username'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_game', 'pre'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pre.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('pre.modified_by')
            );

        if ($this->prediction_id > 0) {
            $query->where($db->quoteName('pre.id') . ' = ' . $this->prediction_id);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('pre.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('pre.published') . ' = ' . (int) $state);
        }

        $orderMap = [
            'pre.name' => $db->quoteName('pre.name'),
            'name' => $db->quoteName('pre.name'),
            'pre.published' => $db->quoteName('pre.published'),
            'published' => $db->quoteName('pre.published'),
            'state' => $db->quoteName('pre.published'),
            'pre.id' => $db->quoteName('pre.id'),
            'id' => $db->quoteName('pre.id'),
            'pre.ordering' => $db->quoteName('pre.ordering'),
            'ordering' => $db->quoteName('pre.ordering'),
            'pre.modified' => $db->quoteName('pre.modified'),
            'modified' => $db->quoteName('pre.modified'),
            'pre.modified_by' => $db->quoteName('pre.modified_by'),
            'modified_by' => $db->quoteName('pre.modified_by'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'pre.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['pre.name']) . ' ' . $direction);

        return $query;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->prediction_id;
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    private static function getSportsManagementDatabase(): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve(
            0,
            Factory::getContainer()->get(DatabaseInterface::class)
        );
    }
}
