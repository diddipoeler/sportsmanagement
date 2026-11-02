<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Google Calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Log\Log;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Build the Google Calendar module context using Joomla caching.
     *
     * Resolves the cache factory, requests event data from the helper and protects Joomla-owned layout values. Logs service failures without exposing API errors or interrupting public page rendering.
     *
     * @return array|false Template context, or false when rendering is skipped.
     * @throws \RuntimeException When invoked outside Joomla's site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Google Calendar requires the Joomla site application.', 500);
        }

        try {
            $cacheFactory = $this->cacheControllerFactory();
            $payload = $this->getHelperFactory()
                ->getHelper('GoogleCalendarHelper')
                ->getData($data['params'], $data['module'], $app, $cacheFactory);
            $payload = is_array($payload) ? $payload : [];
            unset($payload['module'], $payload['params'], $payload['layout']);
            $data = array_merge($data, $payload);
        } catch (\Throwable $exception) {
            // Avoid exposing OAuth/API error details to public site visitors.
            Log::add('JSM Google Calendar: rendering failed (' . get_class($exception) . ')', Log::ERROR, 'com_sportsmanagement');
            // The module is optional page content: avoid adding an unrelated
            // warning to every visitor's message queue during API outages.
            $data['events'] = [];
        }

        return $data;
    }

    /**
     * Retrieve Joomla's cache controller factory for calendar requests.
     *
     * Uses the dependency injection container rather than legacy cache
     * initialisation and verifies the service interface before using it.
     *
     * @return CacheControllerFactoryInterface Supported Joomla cache factory.
     * @throws \RuntimeException If the cache factory service is unavailable.
     */
    private function cacheControllerFactory(): CacheControllerFactoryInterface
    {
        $cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);

        if (!$cacheFactory instanceof CacheControllerFactoryInterface) {
            throw new \RuntimeException('SportsManagement Google Calendar cache factory is unavailable.', 500);
        }

        return $cacheFactory;
    }
}
