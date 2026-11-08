<?php
/**
 * Joomla 5/6 service provider for the SportsManagement Google Calendar module.
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

return new class () implements ServiceProviderInterface {
    /**
     * Register Joomla 5/6 services for the SportsManagement google calendar module.
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
            new ModuleDispatcherFactory('Diddipoeler\\Module\\SportsManagementGoogleCalendar')
        );
        $container->registerServiceProvider(
            new HelperFactory('Diddipoeler\\Module\\SportsManagementGoogleCalendar\\Site\\Helper')
        );
        $container->registerServiceProvider(new Module());
    }
};
