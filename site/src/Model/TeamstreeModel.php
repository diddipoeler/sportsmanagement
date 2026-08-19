<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class TeamstreeModel extends SportsManagementProjectModel
{
    public function getTeamsForTree(): array
    {
        return $this->getProjectTeams($this->getDivisionId());
    }
}
