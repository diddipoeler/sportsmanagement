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

use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form controller for project referees.
 */
final class ProjectrefereeController extends SportsManagementFormController
{
    /**
     * Delete selected project-referee relations and refresh Finder entries.
     *
     * Validates the Joomla form token and component deletion permission,
     * resolves the native model and identifies related people before
     * deleting the selected rows. Notifies the Finder index only when the
     * delete operation succeeds, then returns to the referee overview.
     *
     * @return void
     * @throws \RuntimeException For denied access or an unavailable model.
     */
    public function remove(): void
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.delete', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $pks = $this->app->getInput()->post->get('cid', [], 'array');
        $model = $this->getModel();
        if (!is_object($model)) {
            throw new \RuntimeException('Projectreferee model is unavailable.', 500);
        }

        $notifier = new FinderRelationNotifier($model->getDatabase());
        $personIds = $notifier->projectRefereePeopleForRows((array) $pks);
        $success = $model->delete($pks);

        if ($success) {
            $notifier->notifyPeople($personIds);
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=projectreferees',
            Text::_($success ? 'JLIB_APPLICATION_DELETE_SUCCESS' : 'JLIB_APPLICATION_ERROR_DELETE_FAILED'),
            $success ? 'message' : 'error'
        );
    }
}
