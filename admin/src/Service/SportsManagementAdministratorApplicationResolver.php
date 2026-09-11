<?php
/**
 * Joomla 5/6 administrator application resolver for SportsManagement runtime code.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;

final class SportsManagementAdministratorApplicationResolver
{
    public static function resolve(): AdministratorApplication
    {
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement administrator application is unavailable.', 500);
        }

        return $app;
    }
}
