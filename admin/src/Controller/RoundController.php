<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 form controller for project rounds.
 */
final class RoundController extends SportsManagementFormController
{
    public function save2($key = null, $urlVar = null): void
    {
        $this->app->enqueueMessage((string) $this->getTask(), 'notice');
    }

    public function startpopulate(): void
    {
        $this->checkToken();

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
