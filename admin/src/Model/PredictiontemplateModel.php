<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Native Joomla 5/6 administrator form model for prediction templates.
 */
final class PredictiontemplateModel extends SportsManagementAdminModel
{
    public function getTable($type = 'predictiontemplate', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    public function getScript(): string
    {
        return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $params = Factory::getApplication()->getInput()->post->get('params', [], 'array');

        if ($params) {
            $data['params'] = json_encode($params);
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            Factory::getApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }
    }

    public function getPredictionGame($id)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);

        try {
            $db->setQuery($query);

            return $db->loadObject();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return Factory::getApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }
}
