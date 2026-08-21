<?php
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\System\SportsmanagementRegistercomp\Extension\SportsmanagementRegistercomp;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (): PluginInterface {
                $plugin = new SportsmanagementRegistercomp(
                    (array) PluginHelper::getPlugin('system', 'jsm_registercomp')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
