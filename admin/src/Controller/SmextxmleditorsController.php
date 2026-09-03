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
    public function getModel($name = 'Smextxmleditor', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
