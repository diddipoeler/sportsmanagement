<?php
/**
 * Native Joomla 5/6 controller adapter for the JoomLeague import model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller adapter for the JoomLeague import model. */
final class JoomleagueimportController extends BaseController
{
    /**
     * Obtain the native Joomla JoomLeague import model.
     *
     * Retain caller-supplied model configuration and force ignore_request
     * so the import workflow does not inherit arbitrary list filter state.
     *
     * @param string $name Requested import model name.
     * @param string $prefix Joomla administrator namespace prefix.
     * @param array $config Optional model configuration.
     * @return mixed Model resolved by the parent Joomla MVC controller.
     */
    public function getModel($name = 'Joomleagueimport', $prefix = 'Administrator', $config = [])
    {
        $config = is_array($config) ? $config : [];
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
