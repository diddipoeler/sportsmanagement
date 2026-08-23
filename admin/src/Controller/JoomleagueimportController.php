<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller adapter for the JoomLeague import model. */
final class JoomleagueimportController extends BaseController
{
    public function getModel($name = 'Joomleagueimport', $prefix = 'Administrator', $config = [])
    {
        if (!class_exists('sportsmanagementModeljoomleagueimport', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/joomleagueimport.php';
        }

        $config['ignore_request'] = true;

        return new \sportsmanagementModeljoomleagueimport($config);
    }
}
