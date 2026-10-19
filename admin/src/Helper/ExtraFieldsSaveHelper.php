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

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Persist SportsManagement user extra-field values without loading the legacy helper.
 */
final class ExtraFieldsSaveHelper
{
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    public function save(array $post, int $itemId, ?DatabaseInterface $database = null): void
    {
        if ($itemId <= 0 || empty($post['extraf']) || !is_array($post['extraf'])) {
            return;
        }

        $app = self::administratorApplication();
        $task = $app->getInput()->getCmd('task');
        try {
            $db = (new SportsManagementDatabaseResolver())->resolve(null, $database);
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return;
        }

        $extraIds = is_array($post['extra_id'] ?? null) ? $post['extra_id'] : [];

        foreach ($post['extraf'] as $index => $rawValue) {
            $rawId = $extraIds[$index] ?? null;
            $fieldId = self::positiveFieldId($rawId);

            // Nested form values are not valid text: PHP 8 emits warnings when
            // arrays are cast, and storing the word "Array" corrupts the row.
            if ($fieldId <= 0 || (!is_scalar($rawValue) && $rawValue !== null)) {
                continue;
            }

            try {
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
                    ->where([
                        $db->quoteName('field_id') . ' = ' . $fieldId,
                        $db->quoteName('jl_id') . ' = ' . $itemId,
                    ]);
                $db->setQuery($query)->execute();
            } catch (\Throwable $e) {
                $this->reportDatabaseError($e);

                // Never insert a replacement when the old row could not be
                // removed; that would create duplicate extra-field values.
                continue;
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
                $query = $db->createQuery()
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

    /** Reject partial numeric, boolean, nested and overflowing field identifiers. */
    private static function positiveFieldId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    private function reportDatabaseError(\Throwable $e): void
    {
        // Keep connection strings, SQL and filesystem locations in the
        // Joomla server log, not in administrator messages.
        \Joomla\CMS\Log\Log::add(
            __METHOD__ . ': ' . $e->getMessage(),
            \Joomla\CMS\Log\Log::ERROR,
            'com_sportsmanagement'
        );

        self::administratorApplication()->enqueueMessage(
            Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            'error'
        );
    }
}
