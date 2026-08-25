<?php
/**
 * Legacy router compatibility bridge for SportsManagement.
 *
 * Joomla 5/6 obtains the component router through RouterFactory and
 * Diddipoeler\Component\SportsManagement\Site\Service\Router. These proxy
 * symbols are retained for third-party extensions which still call the
 * historical component routing functions directly.
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\Router as SportsManagementRouterService;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Factory;

/**
 * Backward-compatible class name used by older SportsManagement integrations.
 */
class SportsmanagementRouter extends SportsManagementRouterService
{
    public function __construct()
    {
        $app = Factory::getApplication();

        // The SportsManagement router currently needs only the application and
        // menu. Keeping the optional factory/database arguments null makes this
        // fallback independent from Joomla's child DI container, which is
        // important when Joomla 5 reaches router.php because component booting
        // itself failed or is incomplete.
        parent::__construct(
            $app,
            $app->getMenu(),
            null,
            null
        );
    }
}

/**
 * Return the same component router Joomla 5/6 uses whenever possible.
 *
 * The static fallback keeps the historic bridge usable for third-party code
 * which includes router.php outside Joomla's normal component-router lookup.
 */
function sportsmanagementGetRouter(): RouterInterface
{
    static $router = null;

    if ($router instanceof RouterInterface) {
        return $router;
    }

    $app = Factory::getApplication();

    if (method_exists($app, 'bootComponent')) {
        try {
            $component = $app->bootComponent('com_sportsmanagement');

            if ($component instanceof RouterServiceInterface) {
                $candidate = $component->createRouter($app, $app->getMenu());

                if ($candidate instanceof RouterInterface) {
                    $router = $candidate;
                }
            }
        } catch (\Throwable $exception) {
            // Joomla 5 can reach the legacy bridge while the component service
            // container is only partially available. Do not turn that bootstrap
            // problem into a routing fatal; the direct router below does not
            // depend on the component child container.
        }
    }

    if (!$router instanceof RouterInterface) {
        $router = new SportsmanagementRouter();
    }

    return $router;
}

/**
 * Legacy build proxy.
 *
 * @param   array  $query  URL query variables.
 *
 * @return  array
 */
function SportsmanagementBuildRoute(&$query)
{
    $query = sportsmanagementGetRouter()->preprocess($query);

    return sportsmanagementGetRouter()->build($query);
}

/**
 * Legacy parse proxy.
 *
 * @param   array  $segments  URL path segments.
 *
 * @return  array
 */
function SportsmanagementParseRoute($segments)
{
    return sportsmanagementGetRouter()->parse($segments);
}
