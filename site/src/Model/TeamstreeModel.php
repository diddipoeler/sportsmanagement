<?php
/**
 * Native Joomla 5/6 teams tree model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class TeamstreeModel extends SportsManagementProjectModel
{
    public function getTeamsForTree(): array
    {
        return $this->getProjectTeams($this->getDivisionId());
    }
}
