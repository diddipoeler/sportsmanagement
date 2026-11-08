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
        $view = trim($view);
        $field = trim($field);

        if ($view === '' || $field === '' || strlen($view) > 255 || strlen($field) > 255
            || $fieldType < 0 || preg_match('/[\x00-\x1F\x7F]/', $view . $field)) {
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
                __METHOD__ . ': ' . $e->getMessage(),
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

        $values = explode(',', (string) $result->select_columns);
        $rawLabels = $result->select_values ?? '';
        $labels = is_string($rawLabels) && trim($rawLabels) !== ''
            ? explode(',', $rawLabels)
            : $values;
        $options = [];

        foreach ($values as $key => $value) {
            $options[] = HTMLHelper::_(
                'select.option',
                $value,
                $labels[$key] ?? $value
            );
        }

        return $options;
    }
}
