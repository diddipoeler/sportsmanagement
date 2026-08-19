<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 administrator controller for federations. */
final class JlextfederationsController extends SportsManagementAdminController
{
    public function getModel($name = 'Jlextfederation', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
