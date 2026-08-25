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
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

// Joomla 5 can legitimately reach this file through LegacyComponent before the
// component PSR-4 namespace has been activated (for example after an upgrade or
// when the service provider cannot be completed). Make the compatibility bridge
// self-contained instead of assuming that the native router already autoloads.
if (!class_exists(SportsManagementRouterService::class)) {
    $legacyPresentationLoader = __DIR__ . '/src/Service/LegacyPresentationLoader.php';
    $nativeRouter = __DIR__ . '/src/Service/Router.php';

    if (is_file($legacyPresentationLoader)) {
        require_once $legacyPresentationLoader;
    }

    if (is_file($nativeRouter)) {
        require_once $nativeRouter;
    }
}

if (!class_exists(SportsManagementRouterService::class)) {
    throw new \RuntimeException('SportsManagement native site router could not be loaded.', 500);
}

/**
 * Backward-compatible class name used by older SportsManagement integrations.
 */
class SportsmanagementRouter extends SportsManagementRouterService
{
    public function __construct(?CMSApplicationInterface $app = null, ?AbstractMenu $menu = null)
    {
        $app ??= Factory::getApplication();
        $menu ??= $app->getMenu();

        // Joomla 5 LegacyComponent::createRouter() passes its application and
        // menu explicitly. Honour that context instead of resolving a second
        // application/menu pair from Factory. The optional factory/database
        // arguments remain null because this router does not use them.
        parent::__construct(
            $app,
            $menu,
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
