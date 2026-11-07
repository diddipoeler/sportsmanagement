<?php
/**
 * Joomla 5/6 administrator tournament-tree match form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetomatchTable;

final class TreetomatchModel extends SportsManagementAdminModel
{
    /**
     * Return the native tournament-tree match table or a parent fallback.
     *
     * @param mixed $type type value.
     * @param mixed $prefix prefix value.
     * @param mixed $config config value.
     * @return mixed
     */
    public function getTable($type = 'treetomatch', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'treetomatch') === 0) {
            return new TreetomatchTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
