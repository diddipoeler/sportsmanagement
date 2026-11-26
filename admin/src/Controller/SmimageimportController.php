<?php
/**
 * Native Joomla 5/6 form controller for an image-package record.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 form controller for an image-package record. */
final class SmimageimportController extends SportsManagementFormController
{
    /**
     * Retrieve the image-package model through Joomla's native MVC factory.
     * Ignores request-derived model state to keep the image editor independent
     * of unrelated query parameters while retaining the legacy method signature.
     *
     * @param string $name Model name.
     * @param string $prefix MVC client prefix.
     * @param array $config Legacy model configuration (not forwarded).
     * @return mixed Model returned by the parent MVC controller.
     */
    public function getModel($name = 'Smimageimport', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
