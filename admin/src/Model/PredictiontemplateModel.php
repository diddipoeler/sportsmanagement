<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiontemplateTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for prediction templates.
 */
final class PredictiontemplateModel extends SportsManagementAdminModel
{
    public function getTable($type = 'predictiontemplate', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictiontemplate') === 0) {
            return new PredictiontemplateTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.predictiontemplate',
            'predictiontemplate',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $post = Factory::getApplication()->getInput()->post->getArray();

        if (array_key_exists('params', $post) && is_array($post['params'])) {
            $encoded = json_encode($post['params'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($encoded !== false) {
                $data['params'] = $encoded;
            }
        }

        return parent::prepareSportsManagementData($data);
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            Factory::getApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }

        parent::afterSportsManagementSave($data, $id, $isNew);
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
