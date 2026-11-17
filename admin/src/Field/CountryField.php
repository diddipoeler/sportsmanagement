<?php
/**
 * Joomla 5/6 native country field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

final class CountryField extends SportsManagementListField
{
    protected $type = 'Country';

    /**
     * List known countries and retain country codes used by federations.
     *
     * The country catalogue can be incomplete on upgraded installations.
     * Include existing federation country codes so a saved value remains
     * selectable in the edit form. Failures in either optional lookup do not
     * hide the inherited placeholder options or other valid country rows.
     * Invalid raw codes and labels are never passed into the select control.
     *
     * @return array<int,object> Inherited and validated ISO-3 country options.
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $seen = [];

        try {
            $db = $this->getSportsManagementDatabase();
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': country database unavailable (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return $options;
        }

        try {
            $query = $db->createQuery()
                ->select([$db->quoteName('alpha3', 'value'), $db->quoteName('name', 'text')])
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->order($db->quoteName('name'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $item) {
                if (!is_object($item) || !is_string($item->value ?? null)
                    || preg_match('/[\\x00-\\x1F\\x7F]/', $item->value) === 1) {
                    continue;
                }

                $code = strtoupper(trim($item->value));

                if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                    continue;
                }

                $rawName = $item->text ?? $code;
                $label = is_string($rawName) && strlen($rawName) <= 255
                    && preg_match('//u', $rawName) === 1
                    && preg_match('/[\\x00-\\x1F\\x7F]/', $rawName) !== 1
                    && trim($rawName) !== '' ? $rawName : $code;
                $seen[$code] = true;
                $options[] = (object) [
                    'value' => $code,
                    'text' => Text::_($label),
                ];
            }
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': country lookup failed (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );
        }

        // Stored federation codes must remain editable even if the optional
        // country catalogue has no matching record yet.
        try {
            $query = $db->createQuery()
                ->select('DISTINCT ' . $db->quoteName('country'))
                ->from($db->quoteName('#__sportsmanagement_federations'))
                ->where($db->quoteName('country') . ' IS NOT NULL')
                ->order($db->quoteName('country') . ' ASC');
            $db->setQuery($query);

            foreach ($db->loadColumn() ?: [] as $rawCode) {
                if (!is_string($rawCode)
                    || preg_match('/[\\x00-\\x1F\\x7F]/', $rawCode) === 1) {
                    continue;
                }

                $code = strtoupper(trim($rawCode));

                if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                    continue;
                }

                $seen[$code] = true;
                $options[] = (object) ['value' => $code, 'text' => $code];
            }
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': federation country fallback failed ('
                    . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );
        }

        return $options;
    }
}
