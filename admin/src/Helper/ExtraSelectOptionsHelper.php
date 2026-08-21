<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SportsManagementDatabaseTrait;
use Joomla\CMS\HTML\HTMLHelper;

final class ExtraSelectOptionsHelper
{
    use SportsManagementDatabaseTrait;

    /**
     * Build the configured value/text options for a SportsManagement extra field.
     *
     * This replaces the historic sportsmanagementHelper::getExtraSelectOptions()
     * branch used by administrator forms and lists.
     *
     * @return array<int, object>
     */
    public function getOptions(
        string $view,
        string $field,
        bool $template = true,
        int $fieldType = 0
    ): array {
        $view = trim($view);
        $field = trim($field);

        if ($view === '' || $field === '') {
            return [];
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('select_columns'),
                $db->quoteName('select_values'),
            ])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->where($db->quoteName($template ? 'template_backend' : 'template_frontend') . ' = ' . $db->quote($view))
            ->where($db->quoteName('name') . ' = ' . $db->quote($field))
            ->where($db->quoteName('fieldtyp') . ' = ' . $fieldType);

        $db->setQuery($query);
        $result = $db->loadObject();

        if (!$result || trim((string) $result->select_columns) === '') {
            return [];
        }

        $values = explode(',', (string) $result->select_columns);
        $labels = trim((string) $result->select_values) !== ''
            ? explode(',', (string) $result->select_values)
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
