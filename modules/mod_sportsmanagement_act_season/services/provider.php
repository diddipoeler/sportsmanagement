<?php
/**
 * Joomla 5/6 service provider for mod_sportsmanagement_act_season.
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

return new class implements ServiceProviderInterface
{
    /**
     * Register the Joomla 5/6 services for the SportsManagement act season module.
     *
     * Registers the native dispatcher factory and namespaced helper factory
     * with the dependency injection container. Finally attaches Joomla's core
     * module service so the runtime can dispatch the module independently of
     * its legacy PHP entry point.
     *
     * @param Container $container Joomla's module service container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('Diddipoeler\\Module\\SportsManagementActSeason'));
        $container->registerServiceProvider(new HelperFactory('Diddipoeler\\Module\\SportsManagementActSeason\\Site\\Helper'));
        $container->registerServiceProvider(new Module());
    }
};
