<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionprojectTable;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator form model for prediction projects.
 */
final class PredictionprojectModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        if ($form->getValue('joker')) {
            foreach ([
                'joker_limit',
                'points_tipp_joker',
                'points_correct_result_joker',
                'points_correct_diff_joker',
                'points_correct_draw_joker',
                'points_correct_tendence_joker',
            ] as $fieldName) {
                $form->setFieldAttribute($fieldName, 'type', 'text');
            }
        }

        if ($form->getValue('champ')) {
            $form->setFieldAttribute('points_tipp_champ', 'type', 'text');
        }

        return $form;
    }

    public function saveorder($pks = null, $order = null)
    {
        $pks = array_values((array) $pks);
        $order = array_values((array) $order);
        $count = min(count($pks), count($order));

        if ($count === 0) {
            return true;
        }

        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            for ($i = 0; $i < $count; $i++) {
                $row = $this->getTable();

                if (!$row->load((int) $pks[$i])) {
                    throw new \RuntimeException((string) $row->getError());
                }

                if ((int) $row->ordering === (int) $order[$i]) {
                    continue;
                }

                $row->ordering = (int) $order[$i];

                if (!$row->store()) {
                    throw new \RuntimeException((string) $row->getError());
                }
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original ordering error.
                }
            }

            $this->setError($e->getMessage());

            return false;
        }
    }

    public function getTable($type = 'predictionproject', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictionproject') === 0) {
            return new PredictionprojectTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $post = Factory::getApplication()->getInput()->post->getArray();

        if (array_key_exists('extended', $post) && is_array($post['extended'])) {
            $registry = new Registry();
            $registry->loadArray($post['extended']);
            $data['extended'] = (string) $registry;
        }

        if (!isset($data['league_final4'])) {
            $data['league_final4'] = '0';
        } elseif (is_array($data['league_final4'])) {
            $ids = array_values(array_filter(array_map('intval', $data['league_final4']), static fn (int $id): bool => $id > 0));
            $data['league_final4'] = $ids ? implode(',', $ids) : '0';
        }

        return parent::prepareSportsManagementData($data);
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return Factory::getApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState(
            'com_sportsmanagement.edit.predictionproject.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();

            if ($data && isset($data->league_final4)) {
                $value = trim((string) $data->league_final4);
                $data->league_final4 = $value === '' || $value === '0'
                    ? []
                    : array_values(array_filter(array_map('intval', explode(',', $value))));
            }
        }

        return $data;
    }

    public function getPredictionProject($prediction_id = 0)
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }
}
