<?php
/**
 * Joomla 5/6 site application resolver for SportsManagement runtime code.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

final class SportsManagementSiteApplicationResolver
{
    /**
     * Return a validated Joomla site application from dependency injection.
     *
     * Prevents administrator or missing application services from being used
     * to handle SportsManagement frontend requests and removes reliance on
     * the legacy static Joomla application factory. Container failures are
     * converted into a predictable runtime exception, retaining the cause.
     *
     * @return SiteApplication Active Joomla frontend application.
     * @throws \RuntimeException When no matching site service is available.
     */
    public static function resolve(): SiteApplication
    {
        // Use Joomla's application service instead of the removed legacy Factory::getApplication() API.
        try {
            $app = Factory::getContainer()->get(SiteApplication::class);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('SportsManagement site application service could not be resolved.', 500, $exception);
        }

        if (!$app instanceof SiteApplication || !$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement site application is unavailable.', 500);
        }

        return $app;
    }
}
