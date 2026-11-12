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
    /**
     * Find an optional frontend extension language directory for a given view.
     *
     * Rejects path separators and unsupported characters before examining
     * the component's language extension folder. Only installed, safe view
     * names are returned, so callers never load arbitrary filesystem paths.
     * Canonical directory checks also reject symlinks pointing outside the
     * installed component's extension directory. Rejects non-directories
     * and invalid root folders before resolving individual extension names.
     *
     * @param string $view Joomla administrator view identifier.
     * @return list<string> Available extension view name, or an empty list.
     */
    public static function forView(string $view): array
    {
        // Do not silently strip path separators into another extension name.
        // Administrator view names must already consist of safe Joomla command characters.
        if ($view === '' || strlen($view) > 100
            || preg_match('/^[A-Z0-9_-]+$/iD', $view) !== 1) {
            return [];
        }

        $root = realpath(JPATH_SITE . '/components/com_sportsmanagement/extensions');

        if ($root === false || !is_dir($root)) {
            return [];
        }

        $path = realpath($root . DIRECTORY_SEPARATOR . $view);

        // An installed symlink must not escape the component extension root.
        if ($path === false || !is_dir($path)
            || $path === $root
            || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
            return [];
        }

        return [$view];
    }
}
