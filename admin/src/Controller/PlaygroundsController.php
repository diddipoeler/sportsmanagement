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
    public function getModel($name = 'Playground', $prefix = 'Administrator', $config = [])
    {
        // Keep options provided by Joomla's MVC factory and legacy callers.
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
