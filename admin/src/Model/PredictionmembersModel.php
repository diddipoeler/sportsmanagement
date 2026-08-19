<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for prediction-game members. */
final class PredictionmembersModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'u.username', 'username',
            'u.name', 'realname',
            'p.name', 'predictionname',
            'tmb.last_tipp', 'last_tipp',
            'tmb.reminder', 'reminder',
            'tmb.receipt', 'receipt',
            'tmb.show_profile', 'show_profile',
            'tmb.admintipp', 'admintipp',
            'tmb.approved', 'approved', 'state',
            'tmb.modified', 'modified',
            'tmb.modified_by', 'modified_by',
            'tmb.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    public function getFilterForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return parent::getFilterForm($data, $loadData);
    }

    protected function populateState($ordering = 'u.username', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $this->setState(
            'filter.prediction_id',
            $app->getUserStateFromRequest(
                $this->context . '.filter.prediction_id',
                'filter_prediction_id',
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
                $db->quoteName('tmb') . '.*',
                $db->quoteName('u.name', 'realname'),
                $db->quoteName('u.username'),
                $db->quoteName('p.name', 'predictionname'),
                $db->quoteName('u1.username', 'modusername'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'tmb'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_prediction_game', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tmb.prediction_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('tmb.user_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('tmb.modified_by')
            );

        $predictionId = $this->getState('filter.prediction_id');

        if ($predictionId !== '' && is_numeric($predictionId)) {
            $query->where($db->quoteName('tmb.prediction_id') . ' = ' . (int) $predictionId);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('tmb.approved') . ' = ' . (int) $state);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape(mb_strtolower($search), true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('u.username') . ') LIKE ' . $token);
        }

        $orderMap = [
            'u.username' => $db->quoteName('u.username'),
            'username' => $db->quoteName('u.username'),
            'u.name' => $db->quoteName('u.name'),
            'realname' => $db->quoteName('u.name'),
            'p.name' => $db->quoteName('p.name'),
            'predictionname' => $db->quoteName('p.name'),
            'tmb.last_tipp' => $db->quoteName('tmb.last_tipp'),
            'last_tipp' => $db->quoteName('tmb.last_tipp'),
            'tmb.reminder' => $db->quoteName('tmb.reminder'),
            'reminder' => $db->quoteName('tmb.reminder'),
            'tmb.receipt' => $db->quoteName('tmb.receipt'),
            'receipt' => $db->quoteName('tmb.receipt'),
            'tmb.show_profile' => $db->quoteName('tmb.show_profile'),
            'show_profile' => $db->quoteName('tmb.show_profile'),
            'tmb.admintipp' => $db->quoteName('tmb.admintipp'),
            'admintipp' => $db->quoteName('tmb.admintipp'),
            'tmb.approved' => $db->quoteName('tmb.approved'),
            'approved' => $db->quoteName('tmb.approved'),
            'state' => $db->quoteName('tmb.approved'),
            'tmb.modified' => $db->quoteName('tmb.modified'),
            'modified' => $db->quoteName('tmb.modified'),
            'tmb.modified_by' => $db->quoteName('tmb.modified_by'),
            'modified_by' => $db->quoteName('tmb.modified_by'),
            'tmb.id' => $db->quoteName('tmb.id'),
            'id' => $db->quoteName('tmb.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'u.username');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['u.username']) . ' ' . $direction);

        return $query;
    }

    public function getPredictionProjectName($predictionID)
    {
        $predictionId = (int) $predictionID;

        if ($predictionId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . $predictionId);
        $db->setQuery($query);

        return (string) $db->loadResult();
    }

    public function getPredictionMembers($prediction_id): array
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pm.user_id', 'value'),
                $db->quoteName('u.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id')
            )
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('u.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getJLUsers($prediction_id): array
    {
        $predictionId = (int) $prediction_id;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('pm.user_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $predictionId);
        $db->setQuery($query);
        $memberIds = array_values(array_filter(array_map('intval', $db->loadColumn() ?: [])));

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('block') . ' = 0')
            ->order($db->quoteName('name') . ' ASC');

        if ($memberIds) {
            $query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $memberIds) . ')');
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
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
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (string) $this->getState('filter.prediction_id');
        $id .= ':' . (string) $this->getState('filter.state');
        $id .= ':' . (string) $this->getState('filter.search');

        return parent::getStoreId($id);
    }
}
