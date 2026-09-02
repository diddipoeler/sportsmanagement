<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * List controller for age groups.
 */
class AgegroupsController extends SportsManagementAdminController
{
    /**
     * Save the short values of the selected age groups.
     */
    public function saveshort(): void
    {
        $model = $this->getModel();
        $message = $model->saveshort();

        $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $message);
    }

    /**
     * Import an age-group file through the existing model implementation.
     */
    public function import(): void
    {
        $model = $this->getModel();
        $message = $model->importAgeGroupFile();

        $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $message);
    }

    /**
     * Resolve the namespaced Agegroup model through Joomla's MVCFactory.
     */
    public function getModel($name = 'Agegroup', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
