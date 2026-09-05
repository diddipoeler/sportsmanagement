<?php
/**
 * Joomla 5/6 service provider for the SportsManagement Top Tipper module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new ModuleDispatcherFactory('Diddipoeler\\Module\\SportsManagementTopTipper')
        );
        $container->registerServiceProvider(
            new HelperFactory('Diddipoeler\\Module\\SportsManagementTopTipper\\Site\\Helper')
        );
        $container->registerServiceProvider(new Module());
    }
};
