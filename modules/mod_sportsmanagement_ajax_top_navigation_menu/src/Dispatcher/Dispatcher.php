<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement AJAX top navigation module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $requestedLayout = (string) $data['params']->get('layout', 'default');
        if ($requestedLayout === '' || $requestedLayout === '_:native' || $requestedLayout === 'native') {
            $requestedLayout = 'default';
        }

        $payload = $this->getHelperFactory()
            ->getHelper('AjaxTopNavigationHelper')
            ->getData($data['params'], $data['module'], $app);

        foreach ($payload as $key => $value) {
            $data[$key] = $value;
        }

        $data['legacyLayout'] = $requestedLayout;
        $data['params']->set('layout', 'native');

        $document = $app->getDocument();
        $assets = $document->getWebAssetManager();
        $assets->useScript('bootstrap.tab');
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_ajax_top_navigation_menu',
            'modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_menu.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_ajax_top_navigation_menu.tabs',
            'modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'mod_sportsmanagement_ajax_top_navigation_menu.native',
            'modules/mod_sportsmanagement_ajax_top_navigation_menu/js/native.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['core']
        );

        $document->addScriptOptions(
            'mod_sportsmanagement_ajax_top_navigation_menu.' . (int) $data['module']->id,
            $data['clientConfig']
        );

        return $data;
    }
}
