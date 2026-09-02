<?php
/**
 * Native Joomla 5/6 state model for the database-tools list screen.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Native Joomla 5/6 state model for the database-tools list screen.
 *
 * The large Databasetool action model is intentionally migrated separately.
 */
final class DatabasetoolsModel extends SportsManagementListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $this->setState(
            'list.start',
            max(0, Factory::getApplication()->getInput()->getUInt('limitstart', 0))
        );
    }
}
