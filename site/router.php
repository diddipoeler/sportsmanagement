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
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Backward-compatible class name used by older SportsManagement integrations.
 */
class SportsmanagementRouter extends SportsManagementRouterService
{
    public function __construct()
    {
        $app = Factory::getApplication();

        parent::__construct(
            $app,
            $app->getMenu(),
            null,
            Factory::getContainer()->get(DatabaseInterface::class)
        );
    }
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
    $router = new SportsmanagementRouter();
    $query = $router->preprocess($query);

    return $router->build($query);
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
    $router = new SportsmanagementRouter();

    return $router->parse($segments);
}
