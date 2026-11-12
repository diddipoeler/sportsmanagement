<?php
/**
 * Joomla 5/6 SportsManagement administrator helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SportsManagementDatabaseTrait;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\ParameterType;

final class ExtraSelectOptionsHelper
{
    use SportsManagementDatabaseTrait;

    /**
     * Build the configured value/text options for a SportsManagement extra field.
     *
     * This replaces the historic sportsmanagementHelper::getExtraSelectOptions()
     * branch used by administrator forms and lists. Uses bound parameters
     * for exact field names and returns an empty list for missing or invalid
     * definitions, without exposing database diagnostics in the form.
     * The legacy comma-delimited option collection is capped to avoid
     * excessive rendering work, and blank values are omitted. Excessively
     * long label strings are rejected before parsing to bound memory use.
     * Invalid field identifiers and label control bytes are rejected before
     * creating Joomla HTML option objects.
     *
     * @param string $view Backend or frontend template identifier.
     * @param string $field Extra-field name whose choices are requested.
     * @param bool $template True for backend fields, false for frontend fields.
     * @param int $fieldType Stored extra-field type used to select the definition.
     * @return array<int, object> Prepared value/label option objects.
     */
    public function getOptions(
        string $view,
        string $field,
        bool $template = true,
        int $fieldType = 0
    ): array {
        if ($view === '' || $field === '' || trim($view) !== $view || trim($field) !== $field
            || strlen($view) > 100 || strlen($field) > 100
            || preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $view) !== 1
            || preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $field) !== 1
            || $fieldType < 0) {
            return [];
        }

        try {
            $db = $this->getSportsManagementDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('select_columns'),
                    $db->quoteName('select_values'),
                ])
                ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
                // LIKE treats % and _ as wildcards. Form field names and
                // template identifiers need exact matches, not prefix matches.
                ->where(
                    $db->quoteName($template ? 'template_backend' : 'template_frontend')
                    . ' = :templateName'
                )
                ->where($db->quoteName('name') . ' = :fieldName')
                ->where($db->quoteName('fieldtyp') . ' = :fieldType')
                ->bind(':templateName', $view, ParameterType::STRING)
                ->bind(':fieldName', $field, ParameterType::STRING)
                ->bind(':fieldType', $fieldType, ParameterType::INTEGER);

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();
        } catch (\Throwable $e) {
            // Optional extra fields must not break an administrator form or
            // expose SQL statements and credentials in public error output.
            \Joomla\CMS\Log\Log::add(
                __METHOD__ . ': extra-field options query failed (' . get_class($e) . ').',
                \Joomla\CMS\Log\Log::ERROR,
                'com_sportsmanagement'
            );

            return [];
        }

        if (!is_object($result)
            || !isset($result->select_columns)
            || !is_string($result->select_columns)
            || trim($result->select_columns) === '') {
            return [];
        }

        // Bound the legacy comma-delimited option definition before allocating
        // a potentially excessive collection of Joomla HTML option objects.
        if (strlen($result->select_columns) > 16384) {
            return [];
        }

        $values = explode(',', $result->select_columns);

        if (count($values) > 256) {
            return [];
        }
        $rawLabels = $result->select_values ?? '';

        if (is_string($rawLabels) && strlen($rawLabels) > 16384) {
            return [];
        }

        $labels = is_string($rawLabels) && trim($rawLabels) !== ''
            ? explode(',', $rawLabels)
            : $values;
        if (count($labels) > 256) {
            return [];
        }

        $options = [];

        foreach ($values as $key => $value) {
            $value = trim($value);
            $label = trim($labels[$key] ?? $value);

            if ($value === '' || strlen($value) > 255 || strlen($label) > 255
                || preg_match('/[\x00-\x1F\x7F]/', $value . $label) === 1) {
                continue;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                $value,
                $label !== '' ? $label : $value
            );
        }

        return $options;
    }
}
