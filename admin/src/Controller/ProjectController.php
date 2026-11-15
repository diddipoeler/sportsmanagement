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

/** Native Joomla 5/6 form controller for projects. */
final class ProjectController extends SportsManagementFormController
{
    /**
     * Create the native Joomla 5/6 project form model with caller options.
     *
     * Project editors normally inherit the selected request context, so
     * ignore_request defaults to false. Explicit caller configuration takes
     * precedence, including ignore_request for isolated model writes.
     *
     * @param string $name Requested administrator model.
     * @param string $prefix Joomla MVC model namespace.
     * @param array $config Additional MVC factory model options.
     * @return mixed Parent Joomla form controller model or failure result.
     */
    public function getModel($name = 'Project', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }
}
