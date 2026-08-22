<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Persist SportsManagement user extra-field values without loading the legacy helper.
 */
final class ExtraFieldsSaveHelper
{
    public function save(array $post, int $itemId, ?DatabaseInterface $database = null): void
    {
        if ($itemId <= 0 || empty($post['extraf']) || !is_array($post['extraf'])) {
            return;
        }

        $app = Factory::getApplication();
        $task = $app->getInput()->getCmd('task');
        $db = (new SportsManagementDatabaseResolver())->resolve(null, $database);
        $extraIds = is_array($post['extra_id'] ?? null) ? $post['extra_id'] : [];

        foreach ($post['extraf'] as $index => $rawValue) {
            $fieldId = (int) ($extraIds[$index] ?? 0);

            if ($fieldId <= 0) {
                continue;
            }

            try {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
                    ->where([
                        $db->quoteName('field_id') . ' = ' . $fieldId,
                        $db->quoteName('jl_id') . ' = ' . $itemId,
                    ]);
                $db->setQuery($query)->execute();
            } catch (\Throwable $e) {
                $this->reportDatabaseError($e);
            }

            $value = (string) $rawValue;

            if ($task === 'save2copy' && $value !== '') {
                if ($fieldId === 34) {
                    $value = $this->incrementPathSegment($value, 5);
                } elseif ($fieldId === 69) {
                    $value = $this->incrementPathSegment($value, 3);
                }
            }

            try {
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
                    ->columns($db->quoteName(['field_id', 'jl_id', 'fieldvalue']))
                    ->values(implode(', ', [
                        $fieldId,
                        $itemId,
                        $db->quote($value),
                    ]));
                $db->setQuery($query)->execute();
            } catch (\Throwable $e) {
                $this->reportDatabaseError($e);
            }
        }
    }

    /**
     * Preserve the historic save2copy path mutation for extra fields 34 and 69.
     */
    private function incrementPathSegment(string $value, int $segment): string
    {
        $parts = explode('/', $value);
        $parts[$segment] = ((int) ($parts[$segment] ?? 0)) + 1;

        if (isset($parts[0])) {
            $parts[0] .= '/';
        }

        $parts = array_values(array_filter($parts));

        return implode('/', $parts);
    }

    private function reportDatabaseError(\Throwable $e): void
    {
        $app = Factory::getApplication();
        $app->enqueueMessage(
            Text::sprintf(
                'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                $e->getCode(),
                $e->getMessage()
            ),
            'error'
        );
        $app->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__),
            'error'
        );
    }
}
