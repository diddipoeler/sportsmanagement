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
     *
     * Checks the administrator's write permission, requires a valid MVC
     * form token and a model with the inline-save operation, then redirects
     * with a status message instead of dereferencing a missing legacy model.
     *
     * @return void
     * @throws \\RuntimeException When the required inline-save model is absent.
     */
    public function saveshort(): void
    {
        $this->checkToken();
        $this->assertWritePermission();

        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'saveshort')) {
            throw new \RuntimeException('SportsManagement age-group model could not be loaded.', 500);
        }

        $message = $model->saveshort();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=agegroups',
            $message === false ? 'Age group changes could not be saved.' : $message,
            $message === false ? 'warning' : 'message'
        );
    }

    /**
     * Import an age-group file through the existing model implementation.
     *
     * Requires a valid Joomla form token, creation permission and a native import
     * model before delegating the import and redirecting to the list view.
     *
     * @return void
     * @throws \\RuntimeException When the import model is unavailable.
     */
    public function import(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.create');

        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'importAgeGroupFile')) {
            throw new \RuntimeException('SportsManagement age-group import model could not be loaded.', 500);
        }

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
