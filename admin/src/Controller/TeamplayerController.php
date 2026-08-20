<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 form controller for one team-person assignment. */
final class TeamplayerController extends SportsManagementFormController
{
    public function getModel($name = 'Teamplayer', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }
}
