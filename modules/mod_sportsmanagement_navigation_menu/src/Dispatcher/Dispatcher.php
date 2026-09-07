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

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();
        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_navigation_menu', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

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
