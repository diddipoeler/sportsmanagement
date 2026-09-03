<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstatisticTable;

final class MatchstatisticModel extends SportsManagementAdminModel
{
    public function getTable($type = 'matchstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchstatistic') === 0) {
            return new MatchstatisticTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
