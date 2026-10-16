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

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 controller for the installation helper wizard.
 */
final class InstallhelperController extends SportsManagementFormController
{
    public function savesportstype(): void
    {
        $this->checkToken();

        // CSRF protection does not grant permission to create installation
        // records. Keep the installer wizard restricted to component admins.
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $post = $this->app->getInput()->post->getArray();
        $model = $this->getModel('Installhelper', 'Administrator', ['ignore_request' => true]);

        if ($model === false) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=installhelper&step=1',
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );

            return;
        }

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
