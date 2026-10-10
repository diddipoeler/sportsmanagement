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

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;
        $classes = [
            'sportsmanagementHelper' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php',
            'sportsmanagementHelperHtml' => JPATH_SITE . '/components/com_sportsmanagement/helpers/html.php',
            'sportsmanagementHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php',
            'JSMPredictionHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/predictionroute.php',
            'JSMCountries' => JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php',
            'JSMRanking' => JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php',
        ];

        spl_autoload_register(
            static function (string $class) use ($classes): void {
                if (!isset($classes[$class])) {
                    return;
                }

                $path = $classes[$class];

                if (is_file($path)) {
                    require_once $path;
                }
            }
        );
    }
}
