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
        $this->assertWritePermission();

        $model = $this->getModel();
        $message = $model->saveshort();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=agegroups',
            $message === false ? 'Age group changes could not be saved.' : $message,
            $message === false ? 'warning' : 'message'
        );
    }

    /**
     * Import an age-group file through the existing model implementation.
     */
    public function import(): void
    {
        $this->assertWritePermission('core.create');

        $model = $this->getModel();
        $message = $model->importAgeGroupFile();

        $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $message);
    }

    /**
     * Resolve the namespaced Agegroup model through Joomla's MVCFactory.
     *
     * Preserve caller-supplied configuration while forcing request-independent
     * model loading so existing extensions can still customise the model.
     *
     * @param string $name Model name to resolve.
     * @param string $prefix Joomla MVC client prefix.
     * @param array $config Additional configuration for the model.
     * @return mixed Model instance returned by the parent controller.
     */
    public function getModel($name = 'Agegroup', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
