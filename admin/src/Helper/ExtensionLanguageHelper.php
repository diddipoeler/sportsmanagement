<?php
/**
 * Joomla 5/6 helper for optional SportsManagement extension language paths.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

/**
 * Resolve the optional SportsManagement extension language directory for the
 * active administrator view without bootstrapping the legacy global helper.
 */
final class ExtensionLanguageHelper
{
    /** @return list<string> */
    public static function forView(string $view): array
    {
        $view = preg_replace('/[^A-Z0-9_-]/i', '', $view) ?? '';

        if ($view === '') {
            return [];
        }

        $path = JPATH_SITE . '/components/com_sportsmanagement/extensions/' . $view;

        return is_dir($path) ? [$view] : [];
    }
}
