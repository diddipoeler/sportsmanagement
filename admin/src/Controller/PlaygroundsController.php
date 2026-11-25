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

final class PlaygroundsController extends SportsManagementAdminController
{
    /**
     * Resolve the native playground edit model for administrator list actions.
     * Retains caller MVC factory options and forces request-independent model
     * state so list filters do not influence record operations.
     * @param string $name Requested playground model name.
     * @param string $prefix Joomla administrator MVC namespace prefix.
     * @param array $config Additional model creation options.
     * @return mixed Model instance or parent factory failure result.
     */
    public function getModel($name = 'Playground', $prefix = 'Administrator', $config = [])
    {
        // Keep options provided by Joomla's MVC factory and legacy callers.
        $config = is_array($config) ? $config : [];
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
