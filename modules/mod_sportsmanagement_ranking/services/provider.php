<?php
/**
 * Joomla 5/6 service provider for the SportsManagement Ranking module.
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
     * Configure the SportsManagement ranking module for Joomla 5/6.
     *
     * Registers the namespaced dispatcher and helper factories
     * with Joomla's dependency injection container, followed by the core
     * module service. The native dispatcher can then prepare its layout
     * without relying on a legacy module PHP entry point.
     *
     * @param Container $container Joomla module service container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('Diddipoeler\\Module\\SportsManagementRanking'));
        $container->registerServiceProvider(new HelperFactory('Diddipoeler\\Module\\SportsManagementRanking\\Site\\Helper'));
        $container->registerServiceProvider(new Module());
    }
};
