<?php
/**
 * Joomla 5/6 dispatcher for mod_sportsmanagement_navigation_menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        $payload = $this->getHelperFactory()
            ->getHelper('NativeNavigationMenuHelper')
            ->getData($data['params'], $app, $joomlaDatabase);

        foreach ($payload as $key => $value) {
            $data[$key] = $value;
        }

        $assets = $app->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_navigation_menu',
            'modules/mod_sportsmanagement_navigation_menu/css/mod_sportsmanagement_navigation_menu.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'mod_sportsmanagement_navigation_menu',
            'modules/mod_sportsmanagement_navigation_menu/js/mod_sportsmanagement_navigation_menu.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        return $data;
    }
}
