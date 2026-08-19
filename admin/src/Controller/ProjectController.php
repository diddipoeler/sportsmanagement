<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 form controller for projects. */
final class ProjectController extends SportsManagementFormController
{
    public function getModel($name = 'Project', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }
}
