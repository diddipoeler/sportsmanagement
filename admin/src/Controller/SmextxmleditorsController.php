<?php
/**
 * Native Joomla 5/6 administrator controller for extended XML/PHP files.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 administrator controller for extended XML/PHP files.
 */
final class SmextxmleditorsController extends SportsManagementAdminController
{
    /**
     * Resolve the native XML/PHP editor model through Joomla's MVC factory.
     *
     * Uses the administrator model namespace and disables request-derived
     * state by default. Unlike the legacy bridge, caller-supplied factory
     * options are retained, allowing Joomla and extension integrations to
     * inject model dependencies without losing their configuration.
     *
     * @param string $name Administrator editor model name.
     * @param string $prefix Joomla model namespace prefix.
     * @param array<string,mixed> $config Caller-provided MVC model options.
     * @return mixed Model returned by the parent Joomla controller.
     */
    public function getModel($name = 'Smextxmleditor', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config + ['ignore_request' => true]);
    }
}
