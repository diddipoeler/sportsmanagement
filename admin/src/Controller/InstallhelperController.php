<?php
/**
 * Native Joomla 5/6 controller for the installation helper wizard.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 controller for the installation helper wizard.
 */
final class InstallhelperController extends SportsManagementFormController
{
    public function savesportstype(): void
    {
        $this->checkToken();

        $post = $this->app->getInput()->post->getArray();
        $model = $this->getModel('Installhelper', 'Administrator', ['ignore_request' => true]);
        $warnings = $model->saveSportstype($post);

        if ($warnings) {
            foreach ($warnings as $warning) {
                $this->app->enqueueMessage($warning, 'warning');
            }

            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=installhelper&step=1'
            );

            return;
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=installhelper&step=2'
        );
    }
}
