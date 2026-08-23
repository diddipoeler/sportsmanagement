<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller adapter for the JoomLeague import model. */
final class JoomleagueimportController extends BaseController
{
    public function getModel($name = 'Joomleagueimport', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
