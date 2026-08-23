<?php

defined('_JEXEC') or die;

use Diddipoeler\Plugin\Finder\Sportsmanagement\Extension\Sportsmanagement;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): Sportsmanagement {
                $plugin = new Sportsmanagement(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('finder', 'jsm_search')
                );
                $plugin->setApplication($container->get(CMSApplicationInterface::class));
                $plugin->setDatabase($container->get(DatabaseInterface::class));

                return $plugin;
            }
        );
    }
};
