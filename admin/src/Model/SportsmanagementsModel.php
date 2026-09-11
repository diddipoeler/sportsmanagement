<?php
/**
 * Joomla 5/6 administrator legacy SportsManagement list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list model for the legacy SportsManagement sample records.
 */
final class SportsmanagementsModel extends SportsManagementListModel
{
    protected function getListQuery()
    {
        $db = $this->getDatabase();

        return $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('greeting'),
            ])
            ->from($db->quoteName('#__sportsmanagement'));
    }
}
