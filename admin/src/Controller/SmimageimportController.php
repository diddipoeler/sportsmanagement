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
    public function getModel($name = 'Smimageimport', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
