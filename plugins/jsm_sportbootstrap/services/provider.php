<?php
/**
 * Joomla 5/6 dependency-injection provider for configured sport installation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Plugin\Extension\SportsmanagementSportbootstrap\Extension\SportsmanagementSportbootstrap;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface
{
    /**
     * Register the Joomla extension plugin and its event dispatcher.
     *
     * Joomla retrieves this service when it imports extension plugins while
     * saving component configuration through com_config.
     *
     * @param Container $container Joomla plugin dependency container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->set(PluginInterface::class, static function (Container $container): PluginInterface {
            $plugin = new SportsmanagementSportbootstrap(
                $container->get(DispatcherInterface::class),
                (array) PluginHelper::getPlugin('extension', 'jsm_sportbootstrap')
            );
            $plugin->setApplication(Factory::getApplication());

            return $plugin;
        });
    }
};
