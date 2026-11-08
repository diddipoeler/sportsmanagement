<?php
/**
 * Joomla 5/6 service provider for the SportsManagement Playground Plan module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface
{
    /**
     * Register Joomla 5/6 services for the SportsManagement playgroundplan module.
     *
     * Installs the namespaced dispatcher and helper factories in
     * Joomla's dependency injection container, then registers the core module
     * service. This activates the native module runtime in place of the
     * historical PHP entry-point bootstrap.
     *
     * @param Container $container Joomla's module service container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new ModuleDispatcherFactory('Diddipoeler\\Module\\SportsManagementPlaygroundPlan')
        );
        $container->registerServiceProvider(
            new HelperFactory('Diddipoeler\\Module\\SportsManagementPlaygroundPlan\\Site\\Helper')
        );
        $container->registerServiceProvider(new Module());
    }
};
