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
    /** @return array<string, mixed> */
    public static function toArray(string $stored): array
    {
        $stored = trim($stored);

        if ($stored === '') {
            return [];
        }

        try {
            $registry = new Registry();
            $registry->loadString($stored);

            return $registry->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
