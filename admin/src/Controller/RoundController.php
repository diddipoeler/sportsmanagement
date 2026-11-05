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
 * Native Joomla 5/6 form controller for project rounds.
 */
final class RoundController extends SportsManagementFormController
{
    /**
     * Report the legacy alternative round-save task in Joomla's message queue.
     *
     * This compatibility handler does not persist a round; regular round
     * form writes continue to use the native controller save workflow.
     *
     * @param string|null $key Optional record key for legacy calls.
     * @param string|null $urlVar Optional URL record identifier.
     * @return void
     */
    public function save2($key = null, $urlVar = null): void
    {
        $this->app->enqueueMessage((string) $this->getTask(), 'notice');
    }

    /**
     * Generate round fixtures from a submitted team ordering.
     *
     * Requires a valid Joomla form token and component creation permission,
     * validates that the team list is present, resolves the native Rounds
     * model and delegates scheduling to its populate() operation. The
     * caller returns to the project rounds overview with result feedback.
     *
     * @return void
     * @throws \RuntimeException On denied access or missing scheduling model.
     */
    public function startpopulate(): void
    {
        $this->checkToken();

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.create', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $input = $this->app->getInput();
        $post = $input->post->getArray();
        $projectId = (int) ($post['project_id'] ?? 0);
        $teamsOrder = isset($post['teamsorder']) && is_array($post['teamsorder'])
            ? $post['teamsorder']
            : [];
        $message = '';
        $messageType = 'message';

        if (!$teamsOrder) {
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB');
            $messageType = 'error';
        } else {
            $model = parent::getModel('Rounds', 'Administrator', ['ignore_request' => true]);
            if (!is_object($model)) {
                throw new \RuntimeException('SportsManagement rounds scheduling model is unavailable.', 500);
            }

            $success = $model->populate(
                $projectId,
                (int) ($post['scheduling'] ?? 0),
                (string) ($post['time'] ?? '20:00'),
                (int) ($post['interval'] ?? 7),
                (string) ($post['start'] ?? ''),
                (string) ($post['roundname'] ?? '%d'),
                $teamsOrder
            );

            if (!$success) {
                $message = $model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
                $messageType = 'error';
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId,
            $message,
            $messageType
        );
    }
}
