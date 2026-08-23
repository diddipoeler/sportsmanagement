<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Extension\SportsManagementComponent;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementMVCFactory;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory as ComponentDispatcherFactoryServiceProvider;
use Joomla\CMS\Extension\Service\Provider\RouterFactory as RouterFactoryServiceProvider;
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
    public function register(Container $container): void
    {
        $container->set(MVCFactoryInterface::class, static function (Container $container): MVCFactoryInterface {
            $app = $container->get(CMSApplicationInterface::class);
            $factory = $app->isClient('api')
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
