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

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;

final class SportsManagementSiteApplicationResolver
{
    public static function resolve(): CMSApplicationInterface
    {
        $app = Factory::getApplication();

        if (!$app instanceof CMSApplicationInterface || !$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement site application is unavailable.', 500);
        }

        return $app;
    }
}
