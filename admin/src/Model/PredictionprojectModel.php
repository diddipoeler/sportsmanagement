<?php
/**
 * Joomla 5/6 administrator form model for prediction projects.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionprojectTable;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator form model for prediction projects.
 */
final class PredictionprojectModel extends SportsManagementAdminModel
{
    /**
     * Prepare the prediction project form and conditional joker fields.
     * Enables joker and final-four scoring inputs when corresponding
     * prediction settings are active.
     * @param array $data Form values.
     * @param bool $loadData Whether to populate saved values.
     * @return mixed Joomla form or false.
     */
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

    /**
     * Save prediction project ordering in one database transaction.
     * @param array|null $pks Prediction project row IDs.
     * @param array|null $order New ordering values.
     * @return bool Whether ordering changes succeeded.
     */
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

    /**
     * Resolve the native prediction-project table.
     * @param string $type Joomla table name.
     * @param string $prefix Legacy table prefix.
     * @param array $config Additional table options.
     * @return mixed Native PredictionprojectTable or parent table.
     */
    public function getTable($type = 'predictionproject', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictionproject') === 0) {
            return new PredictionprojectTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Prepare prediction project settings for storage.
     * Serialises extended form values and final-four league IDs.
     * @param array $data Submitted prediction project settings.
     * @return array Prepared database values.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

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

    /**
     * Verify the current identity's permission to edit a prediction project.
     * Preserves standard Joomla ACL as a fallback for record-specific rights.
     * @param array $data Prediction project record.
     * @param string $key Record identifier property.
     * @return bool Whether edit access is granted.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        $identity = $this->administratorApplication()->getIdentity();

        return ($identity !== null && $identity->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        )) || parent::allowEdit($data, $key);
    }

    /**
     * Load stored prediction project values or pending form state.
     * Expands the comma-delimited final-four league IDs into a form array.
     * @return mixed Prediction project form data.
     */
    protected function loadFormData()
    {
        $data = $this->administratorApplication()->getUserState(
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

    /**
     * Look up a prediction project by game identifier.
     * Uses a bound database parameter and returns the first project row.
     * @param int $prediction_id Selected prediction game ID.
     * @return object|false Prediction project or false when unavailable.
     */
    public function getPredictionProject($prediction_id = 0)
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = :predictionProjectId')
            ->bind(':predictionProjectId', $predictionId, ParameterType::INTEGER)
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }
}
