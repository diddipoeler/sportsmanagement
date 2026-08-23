<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetomatchTable;

final class TreetomatchModel extends SportsManagementAdminModel
{
    public function getTable($type = 'treetomatch', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'treetomatch') === 0) {
            return new TreetomatchTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
