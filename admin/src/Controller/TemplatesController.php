<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 administrator list controller for project templates. */
final class TemplatesController extends SportsManagementAdminController
{
    /**
     * Redirect an administrator to the selected template editing form.
     *
     * Require a valid Joomla POST form token, read the chosen template
     * identifier and optional project identifier as integers, then build
     * the native administrator edit URL using Joomla's router. This task
     * changes navigation only and does not persist template data.
     *
     * @return void The edit-form redirect is registered.
     * @throws \RuntimeException If Joomla's POST token is invalid.
     */
    public function changetemplate(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $id = $input->post->getInt('new_id');
        $projectId = $input->post->getInt('pid');

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=template&layout=edit&id=' . $id
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            )
        );
    }

    /**
     * Resolve the administrator template model through the MVC factory.
     *
     * Retain the caller's model name, namespace and configuration, while
     * ensuring request-based list filters do not change model state by
     * default when the template edit form is opened.
     *
     * @param string $name Requested Joomla template model.
     * @param string $prefix Administrator model namespace.
     * @param array $config Optional model factory configuration.
     * @return mixed Model returned by the Joomla parent controller.
     */
    public function getModel($name = 'Template', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
