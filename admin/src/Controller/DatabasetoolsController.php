<?php
/**
 * Native Joomla 5/6 controller for the database-tools screen.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Native Joomla 5/6 controller for the database-tools screen.
 *
 * The list screen itself uses DatabasetoolsModel; explicit maintenance tasks
 * continue to resolve the large Databasetool action model until that model is
 * migrated separately.
 */
final class DatabasetoolsController extends BaseController
{
    /**
     * Preserve the historical default database maintenance action model.
     *
     * Sets ignore_request to avoid uncontrolled request data influencing the
     * administrator model instance supplied by Joomla's MVC factory.
     *
     * @param string $name Action model name, Databasetool by default.
     * @param string $prefix Administrator MVC namespace prefix.
     * @param array<string,mixed> $config Optional model creation flags.
     * @return object Joomla database-tools model created by the parent.
     */
    public function getModel($name = 'Databasetool', $prefix = 'Administrator', $config = [])
    {
        $config = is_array($config) ? $config : [];
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
