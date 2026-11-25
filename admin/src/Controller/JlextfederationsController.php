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

/** Native Joomla 5/6 administrator controller for federations. */
final class JlextfederationsController extends SportsManagementAdminController
{
    /**
     * Resolve the native administrator federation model through Joomla MVC.
     * Preserves the model name, namespace and caller configuration when
     * delegating to the shared administrator controller's MVC factory.
     * @param string $name Requested federation model name.
     * @param string $prefix Joomla administrator MVC namespace prefix.
     * @param array $config Additional model factory options.
     * @return mixed Resolved model or parent factory failure result.
     */
    public function getModel($name = 'Jlextfederation', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
