<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 list controller for statistic definitions. */
final class StatisticsController extends SportsManagementAdminController
{
    /**
     * Resolve the native administrator statistic definition model for list operations.
     * Retains caller-supplied MVC factory options while forcing request-independent
     * model loading, preventing list filters from altering record operations.
     * @param string $name Requested model name.
     * @param string $prefix Joomla administrator MVC namespace prefix.
     * @param array $config Additional model creation options.
     * @return mixed Model instance or parent factory failure result.
     */
    public function getModel($name = 'Statistic', $prefix = 'Administrator', $config = [])
    {
        $config = is_array($config) ? $config : [];
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
