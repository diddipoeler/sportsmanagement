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

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 list controller for project-position assignments.
 */
final class ProjectpositionsController extends SportsManagementAdminController
{
    public function store(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit');

        $post = $this->app->getInput()->post->getArray();
        $model = $this->getModel();

        if (!is_object($model)) {
            throw new \RuntimeException('Projectposition model is unavailable.', 500);
        }

        $success = $model->store($post);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=close&tmpl=component',
            Text::_($success ? 'JLIB_APPLICATION_SAVE_SUCCESS' : 'JLIB_APPLICATION_ERROR_SAVE_FAILED'),
            $success ? 'message' : 'error'
        );
    }

    public function getModel($name = 'Projectposition', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
