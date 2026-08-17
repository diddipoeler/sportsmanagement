<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 service provider scaffold.
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Extension\SportsManagementComponent;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory as ComponentDispatcherFactoryServiceProvider;
use Joomla\CMS\Extension\Service\Provider\MVCFactory as MVCFactoryServiceProvider;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new MVCFactoryServiceProvider('\\Diddipoeler\\Component\\SportsManagement')
        );

        $container->registerServiceProvider(
            new ComponentDispatcherFactoryServiceProvider('\\Diddipoeler\\Component\\SportsManagement')
        );

        $container->set(
            ComponentInterface::class,
            static function (Container $container): ComponentInterface {
                return new SportsManagementComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );
            }
        );
    }
};
