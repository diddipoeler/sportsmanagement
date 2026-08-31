<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator list model for prediction rounds.
 */
final class PredictionroundsModel extends SportsManagementListModel
{
    public int $prediction_id = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'roundname',
            'roundcode',
        ];

        parent::__construct($config, $factory);
    }

    public function getActivePredictionRoundsCount($prediction_id)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = ' . (int) $prediction_id)
            ->where($db->quoteName('published') . ' = 1');

        try {
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    public function getPredGamesPredictionRoundsIds($prediction_id)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('round_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = ' . (int) $prediction_id);

        try {
            $db->setQuery($query);

            return array_map('intval', $db->loadColumn() ?: []);
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Return prediction games for the administrator selector.
     */
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

    /**
     * Return one prediction game for the rounds header/settings display.
     */
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

    /**
     * Return SportsManagement project IDs assigned to a prediction game.
     */
    public function getPredictionProjectIds($prediction_id): array
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('project_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);

            return array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Return the round IDs belonging to a SportsManagement project.
     */
    public function getProjectRoundIds($project_id): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('roundcode') . ' ASC')
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);

            return array_map('intval', $db->loadColumn() ?: []);
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    protected function populateState($ordering = 'roundcode', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $option = 'com_sportsmanagement';
        $oldPredictionId = (int) $app->getUserState($option . '.filter.prediction_id', 0);
        $filterPredictionId = (int) $app->getUserStateFromRequest(
            $option . '.filter.prediction_id',
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
                $app->redirect('index.php?option=com_sportsmanagement&view=predictionrounds');
            }
        }

        $this->prediction_id = max(0, $predictionId);
        $this->setState('filter.prediction_id', $this->prediction_id);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s') . '.*',
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('u1.username'),
                $db->quoteName('r.name', 'roundname'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround', 's'))
            ->where($db->quoteName('s.prediction_id') . ' = ' . (int) $this->prediction_id)
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('s.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('s.modified_by')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('s.round_id')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('r.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('s.published') . ' = ' . (int) $state);
        }

        $orderMap = [
            'roundname' => $db->quoteName('r.name'),
            'roundcode' => $db->quoteName('r.roundcode'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'roundcode');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['roundcode']) . ' ' . $direction);

        return $query;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->prediction_id;
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');

        return parent::getStoreId($id);
    }
}
