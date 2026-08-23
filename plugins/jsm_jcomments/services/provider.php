<?php
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\Content\SportsmanagementComments\Extension\SportsmanagementComments;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): PluginInterface {
                $plugin = new SportsmanagementComments(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('content', 'sportsmanagement_comments')
                );
                $plugin->setApplication($container->get(CMSApplicationInterface::class));

                return $plugin;
            }
        );
    }
};
