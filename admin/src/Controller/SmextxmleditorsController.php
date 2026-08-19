<?php
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
