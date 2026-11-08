<?php
/**
 * Joomla 5/6 administrator application resolver.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;

final class SportsManagementAdministratorApplicationResolver
{
    /**
     * Obtain and validate the Joomla administrator application service.
     *
     * Resolves the native administrator application through Joomla's service
     * container instead of depending on legacy global application lookups.
     * Rejects frontend applications and wraps container lookup failures while
     * preserving the original exception for troubleshooting.
     *
     * @return CMSApplicationInterface The active administrator application.
     * @throws \RuntimeException If the application service is unavailable or not an administrator client.
     */
    public static function resolve(): CMSApplicationInterface
    {
        try {
            $app = Factory::getContainer()->get(AdministratorApplication::class);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('SportsManagement administrator application service could not be resolved.', 500, $exception);
        }

        if (!$app instanceof CMSApplicationInterface || !$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement administrator application is unavailable.', 500);
        }

        return $app;
    }
}
