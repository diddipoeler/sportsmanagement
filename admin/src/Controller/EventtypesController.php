<?php
/**
 * Joomla 5/6 administrator event-types list controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 list controller for event types. */
final class EventtypesController extends SportsManagementAdminController
{
    /**
     * Load the native event-type editor model through Joomla's MVC factory.
     * Retain options provided by callers while overriding ignore_request, so
     * the model starts from explicit editor state instead of request filters.
     * @param string $name Requested model name.
     * @param string $prefix Joomla administrator MVC namespace prefix.
     * @param array $config Additional configuration options from the caller.
     * @return mixed Model produced by the inherited Joomla factory.
     */
    public function getModel($name = 'Eventtype', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
