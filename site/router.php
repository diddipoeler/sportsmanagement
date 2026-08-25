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
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

// Joomla 5 can legitimately reach this file through LegacyComponent before the
// component PSR-4 namespace has been activated (for example after an upgrade or
// when the service provider cannot be completed). Make the compatibility bridge
// self-contained instead of assuming that the native router already autoloads.
if (!class_exists(SportsManagementRouterService::class)) {
    $routeSchema = __DIR__ . '/src/Service/SiteRouteSchema.php';
    $nativeRouter = __DIR__ . '/src/Service/Router.php';

    if (is_file($routeSchema)) {
        require_once $routeSchema;
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
 * Return a native SportsManagement router for historical routing functions.
 *
 * Do not call bootComponent() from this compatibility bridge. Joomla 5 may be
 * executing this file from LegacyComponent::createRouter(); booting the same
 * component again from here can re-enter the legacy router bootstrap. The
 * bridge class above already wraps the same native Router implementation Joomla
 * 5/6 receives from RouterFactory, so a direct instance is both equivalent and
 * safe on the legacy path.
 */
function sportsmanagementGetRouter(): RouterInterface
{
    static $router = null;

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
    $router = sportsmanagementGetRouter();
    $query = $router->preprocess($query);

    return $router->build($query);
}

/**
 * Legacy parse proxy.
 *
 * Joomla 5 RouterLegacy passes its component segments by reference and expects
 * the component parser to remove every segment it consumes. Keep that reference
 * intact while delegating to the native router; otherwise Joomla 5 treats the
 * already parsed SportsManagement segments as an unconsumed trailing path.
 *
 * @param   array  $segments  URL path segments.
 *
 * @return  array
 */
function SportsmanagementParseRoute(&$segments)
{
    return sportsmanagementGetRouter()->parse($segments);
}
