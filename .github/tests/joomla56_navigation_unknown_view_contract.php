<?php
/**
 * Regression check for Joomla 5/6 navigation menu view dispatch.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = file_get_contents(
    $root . '/modules/mod_sportsmanagement_navigation_menu/src/Helper/NativeNavigationMenuHelper.php'
);

if ($source === false) {
    fwrite(STDERR, "Cannot read native navigation helper.\n");
    exit(1);
}

// Ranking remains a supported, explicit destination.
if (!preg_match(
    "/case 'ranking':\\s*return SiteRouteHelper::view\\('ranking',/s",
    $source
)) {
    fwrite(STDERR, "Explicit ranking navigation is missing.\n");
    exit(1);
}

// Unknown view names (including typos from stored module settings) must not
// resolve to a valid but unrelated destination.
if (!preg_match(
    '/default:\\s*(?:\\/\\/[^\\n]*\\n\\s*)*return false;/',
    $source
)) {
    fwrite(STDERR, "Unknown navigation views must return false.\n");
    exit(1);
}

echo "Navigation menu unknown-view contract passed.\n";
