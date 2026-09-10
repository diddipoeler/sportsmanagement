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
    public static function resolve(): CMSApplicationInterface
    {
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app instanceof CMSApplicationInterface || !$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement administrator application is unavailable.', 500);
        }

        return $app;
    }
}
