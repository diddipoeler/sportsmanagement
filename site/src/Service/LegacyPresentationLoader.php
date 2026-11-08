<?php
/**
 * Joomla 5/6 compatibility loader for remaining global SportsManagement presentation helpers.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

/**
 * Lazy compatibility autoloader for the remaining global presentation helpers.
 *
 * This keeps legacy templates working without registering new classes through
 * Joomla's deprecated JLoader API or eagerly executing the large helper files.
 */
final class LegacyPresentationLoader
{
    private static bool $registered = false;

    /**
     * Register a lazy autoloader for legacy global presentation helper classes.
     * Maps a bounded set of historical helper names to their Joomla 5/6
     * component paths and registers the SPL loader only once. The registered
     * flag is set after a successful SPL call so failures remain retryable.
     *
     * @return void
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        $classes = [
            'sportsmanagementHelper' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php',
            'sportsmanagementHelperHtml' => JPATH_SITE . '/components/com_sportsmanagement/helpers/html.php',
            'sportsmanagementHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php',
            'JSMPredictionHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/predictionroute.php',
            'JSMCountries' => JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php',
            'JSMRanking' => JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php',
        ];

        // PHP class names are case-insensitive during SPL autoloading.
        $classes = array_change_key_case($classes, CASE_LOWER);

        spl_autoload_register(
            static function (string $class) use ($classes): void {
                $path = $classes[strtolower($class)] ?? null;

                if ($path === null) {
                    return;
                }

                if (is_file($path)) {
                    require_once $path;
                }
            }
        );

        // Only mark the compatibility loader as registered after SPL accepted
        // the callback; failed registrations can then be retried safely.
        self::$registered = true;
    }
}
