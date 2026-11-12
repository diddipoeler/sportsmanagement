<?php
/**
 * Joomla 5/6 helper for parsing stored SportsManagement extended values.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/** Parse stored SportsManagement extended values without loading the legacy helper. */
final class ExtendedDataHelper
{
    /**
     * Decode legacy extended-data fields into a PHP array.
     *
     * Uses Joomla Registry to interpret the stored serialised values and
     * returns an empty collection when the value is blank or cannot be
     * parsed. Rejects oversized stored settings before parsing to avoid
     * unbounded allocations in frontend rendering. No legacy global
     * SportsManagement helper is required.
     *
     * @param string $stored Raw serialised extended field data.
     * @return array<string, mixed> Parsed field values, or an empty array.
     */
    public static function toArray(string $stored): array
    {
        $stored = trim($stored);

        if ($stored === '' || strlen($stored) > 1048576) {
            return [];
        }

        try {
            $registry = new Registry();
            $registry->loadString($stored);

            $values = $registry->toArray();

            return is_array($values) ? $values : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
