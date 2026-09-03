<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffstatisticTable;

final class MatchstaffstatisticModel extends SportsManagementAdminModel
{
    public function getTable($type = 'matchstaffstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchstaffstatistic') === 0) {
            return new MatchstaffstatisticTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
