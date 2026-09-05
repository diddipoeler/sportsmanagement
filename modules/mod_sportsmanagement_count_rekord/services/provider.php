<?php
/**
 * Joomla 5/6 service provider for mod_sportsmanagement_count_rekord.
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
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('Diddipoeler\\Module\\SportsManagementCountRekord'));
        $container->registerServiceProvider(new HelperFactory('Diddipoeler\\Module\\SportsManagementCountRekord\\Site\\Helper'));
        $container->registerServiceProvider(new Module());
    }
};
