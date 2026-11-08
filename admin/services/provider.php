<?php
/**
 * Joomla 5/6 dependency injection provider for com_sportsmanagement.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Extension\SportsManagementComponent;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementMVCFactory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory as ComponentDispatcherFactoryServiceProvider;
use Joomla\CMS\Extension\Service\Provider\RouterFactory as RouterFactoryServiceProvider;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Factory\ApiMVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface
{
    /**
     * Register the Joomla 5/6 SportsManagement component services.
     *
     * Installs the native-first MVC factory, supplies Joomla's form, event,
     * database, router, cache, user and mail dependencies, and registers the
     * component dispatcher/router factories. Finally constructs the component
     * extension used to boot site, administrator and API MVC requests while
     * preserving the controlled legacy model and controller fallbacks.
     *
     * @param Container $container Component dependency injection container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->set(MVCFactoryInterface::class, static function (Container $container): MVCFactoryInterface {
            $factory = Factory::getApplication()->isClient('api')
                ? new ApiMVCFactory('\\Diddipoeler\\Component\\SportsManagement')
                : new SportsManagementMVCFactory('\\Diddipoeler\\Component\\SportsManagement');
            $factory->setFormFactory($container->get(FormFactoryInterface::class));
            $factory->setDispatcher($container->get(DispatcherInterface::class));
            $factory->setDatabase($container->get(DatabaseInterface::class));
            $factory->setSiteRouter($container->get(SiteRouter::class));
            $factory->setCacheControllerFactory($container->get(CacheControllerFactoryInterface::class));
            $factory->setUserFactory($container->get(UserFactoryInterface::class));
            $factory->setMailerFactory($container->get(MailerFactoryInterface::class));
            return $factory;
        });

        $container->registerServiceProvider(new ComponentDispatcherFactoryServiceProvider('\\Diddipoeler\\Component\\SportsManagement'));
        $container->registerServiceProvider(new RouterFactoryServiceProvider('\\Diddipoeler\\Component\\SportsManagement'));

        $container->set(ComponentInterface::class, static function (Container $container): ComponentInterface {
            $component = new SportsManagementComponent($container->get(ComponentDispatcherFactoryInterface::class));
            $component->setRegistry($container->get(Registry::class));
            $component->setMVCFactory($container->get(MVCFactoryInterface::class));
            $component->setRouterFactory($container->get(RouterFactoryInterface::class));
            return $component;
        });
    }
};
