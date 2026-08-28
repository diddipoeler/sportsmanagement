<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** List controller for SportsManagement sample records. */
final class SportsmanagementsController extends SportsManagementAdminController
{
    public function getModel($name = 'Sportsmanagement', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
