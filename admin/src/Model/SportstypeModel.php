<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\ParameterType;

final class SportstypeModel extends SportsManagementAdminModel
{
    /**
     * Prepare the native administrator sports-type form and its media field.
     *
     * Uses an explicitly validated Joomla form-field type from the component
     * settings and applies available database column sizes for edit controls.
     * Missing schemas or optional column hints do not prevent form rendering.
     *
     * @param array $data Existing or submitted sports-type form values.
     * @param bool $loadData Whether Joomla should load persisted model data.
     * @return mixed Joomla form instance, or false if it cannot be loaded.
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $configuredMediaTool = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_media_tool', 'media');
        $mediaTool = is_string($configuredMediaTool) ? trim($configuredMediaTool) : '';

        // An invalid legacy setting must not become an arbitrary Joomla
        // form field type or trigger an array-to-string PHP warning.
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', $mediaTool)) {
            $mediaTool = 'media';
        }

        $form->setFieldAttribute('icon', 'type', $mediaTool);

        try {
            $tableName = $this->getDatabase()->getPrefix() . 'sportsmanagement_sports_type';

            foreach ($this->getDatabase()->getTableColumns($tableName, true) as $fieldName => $type) {
                if (!$form->getField((string) $fieldName)) {
                    continue;
                }

                if (preg_match('/varchar\s*\(\s*(\d+)\s*\)/i', (string) $type, $match)) {
                    $form->setFieldAttribute((string) $fieldName, 'size', (string) (int) $match[1]);
                }
            }
        } catch (\Throwable) {
            // Column-length hints are a UI enhancement only.
        }

        return $form;
    }

    /**
     * Read one sports-type record for the native administrator editor.
     *
     * Ignores missing identifiers and binds the numeric record ID in a
     * one-row Joomla database query. Query failures are logged privately,
     * while the model exposes only Joomla's generic translated error.
     *
     * @param int $sportstypeId Selected sports-type identifier.
     * @return object|null Sports type row or null when missing/unavailable.
     */
    public function getSportstype(int $sportstypeId): ?object
    {
        if ($sportstypeId <= 0) {
            return null;
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select('*')
                ->from($db->quoteName('#__sportsmanagement_sports_type'))
                ->where($db->quoteName('id') . ' = :sportstypeId')
                ->bind(':sportstypeId', $sportstypeId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement sport type read failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

            return null;
        }
    }
}
