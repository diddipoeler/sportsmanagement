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
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

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

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement AJAX Top Navigation requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();
        $language->load('mod_sportsmanagement_ajax_top_navigation_menu', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $rawLayout = $data['params']->get('layout', 'default');
        $requestedLayout = is_string($rawLayout) ? trim($rawLayout) : 'default';
        if ($requestedLayout === '' || $requestedLayout === '_:native' || $requestedLayout === 'native'
            || !preg_match('/^(?:[A-Za-z0-9_-]+|[A-Za-z0-9_-]+:[A-Za-z0-9_-]+)$/D', $requestedLayout)
        ) {
            $requestedLayout = 'default';
        }

        $payload = $this->getHelperFactory()
            ->getHelper('AjaxTopNavigationHelper')
            ->getData($data['params'], $data['module'], $app);

        // Only merge data supplied as an array, without replacing Joomla context.
        $payload = is_array($payload) ? $payload : [];
        unset($payload['module'], $payload['params'], $payload['layout']);

        foreach ($payload as $key => $value) {
            $data[$key] = $value;
        }

        $data['legacyLayout'] = $requestedLayout;
        $data['params']->set('layout', 'native');

        $document = $app->getDocument();
        // Joomla's web assets and script options are only available in HTML documents.
        if ($document instanceof HtmlDocument) {
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
    
            // Joomla's script options expect an array; do not forward malformed helper data.
            $clientConfig = $data['clientConfig'] ?? [];
            $document->addScriptOptions(
                'mod_sportsmanagement_ajax_top_navigation_menu.' . max(0, (int) ($data['module']->id ?? 0)),
                is_array($clientConfig) ? $clientConfig : []
            );
        }

        return $data;
    }
}
