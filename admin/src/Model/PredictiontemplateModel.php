<?php
/**
 * Joomla 5/6 administrator form model for prediction templates.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiontemplateTable;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 administrator form model for prediction templates.
 */
final class PredictiontemplateModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native prediction template table for administrator edits.
     * @param string $type Requested table name.
     * @param string $prefix Legacy Joomla table namespace.
     * @param array $config Additional Joomla table settings.
     * @return mixed PredictiontemplateTable or parent Joomla table.
     */
    public function getTable($type = 'predictiontemplate', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictiontemplate') === 0) {
            return new PredictiontemplateTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Serialise submitted prediction template parameters into JSON.
     * Preserves the parent admin model's further data preparation logic.
     * @param array $data Submitted prediction template values.
     * @return array Prepared model persistence values.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

        if (array_key_exists('params', $post) && is_array($post['params'])) {
            $encoded = json_encode($post['params'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($encoded !== false) {
                $data['params'] = $encoded;
            }
        }

        return parent::prepareSportsManagementData($data);
    }

    /**
     * Notify administrators after a prediction template has been saved.
     * Uses the existing translated creation notice for new templates.
     * @param array $data Saved prediction template values.
     * @param int $id Saved record identifier.
     * @param bool $isNew Whether the record was newly inserted.
     * @return void
     */
    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            $this->administratorApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }

        parent::afterSportsManagementSave($data, $id, $isNew);
    }

    /**
     * Load a prediction game record using a bound numeric identifier.
     * @param mixed $id Requested prediction game identifier.
     * @return object|false|null Prediction game or missing/error result.
     */
    public function getPredictionGame($id)
    {
        $predictionId = (int) $id;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = :predictionGameId')
            ->bind(':predictionGameId', $predictionId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);

            return $db->loadObject();
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Check whether the current identity may edit a prediction template.
     * Delegates to normal Joomla admin permissions if the record-specific
     * action is not granted or the user has no identity.
     * @param array $data Selected prediction template row.
     * @param string $key Identifier field name.
     * @return bool Whether Joomla permits the edit.
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
}
