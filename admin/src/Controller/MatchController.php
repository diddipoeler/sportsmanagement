<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\GoogleCalendarMatchSynchronizer;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

if (!class_exists('sportsmanagementControllermatch', false)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/controllers/match.php';
}

/**
 * Joomla 5/6 match controller.
 *
 * Remaining legacy match tasks are inherited unchanged while individual tasks
 * are migrated into the native namespace incrementally.
 */
final class MatchController extends \sportsmanagementControllermatch
{
    public function cancelmodal($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    public function cancelmassadd()
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=matches&massadd=0');
    }

    public function massadd()
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=matches&layout=massadd&massadd=1');
    }

    public function remove()
    {
        $this->checkToken();

        $input = Factory::getApplication()->getInput();
        $pks = array_values(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        )));
        $model = $this->getModel('match');
        $success = $model !== false && $pks !== [] && $model->delete($pks);

        if (!$success) {
            $message = $model && method_exists($model, 'getError') && $model->getError()
                ? (string) $model->getError()
                : Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED');
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=matches',
                $message,
                'error'
            );

            return false;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=matches');

        return true;
    }

    public function picture()
    {
        $matchId = Factory::getApplication()->getInput()->getInt('id', 0);
        $destination = JPATH_ROOT . '/images/com_sportsmanagement/database/matchreport/' . $matchId;

        if (!Folder::exists($destination) && !Folder::create($destination)) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=matches',
                Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'),
                'error'
            );

            return false;
        }

        $folder = 'matchreport/' . $matchId;
        $this->setRedirect(
            'index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/' . $folder,
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE')
        );

        return true;
    }

    public function addmatch()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $data = $input->post->getArray();
        $data['project_id'] = (int) $app->getUserState($option . '.pid', 0);
        $data['round_id'] = (int) $app->getUserState($option . '.rid', 0);
        $data['count_result'] = 1;
        $data['published'] = 1;
        $data['summary'] = '-';
        $data['preview'] = '-';

        $model = $this->getModel('match');
        $success = $model !== false && $model->save($data);
        $message = $success
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH')
                . ($model && method_exists($model, 'getError') ? (string) $model->getError() : '');

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=matches',
            $message,
            $success ? 'message' : 'error'
        );

        return $success;
    }

    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $input = $app->getInput();
        $id = $input->getInt('id');
        $data = $input->post->get('jform', [], 'array');
        $model = $this->getModel('match');
        $success = $model !== false && $model->save($data);

        if (!$success) {
            $message = $model && method_exists($model, 'getError') && $model->getError()
                ? (string) $model->getError()
                : Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=match&layout=edit&tmpl=component&id=' . $id,
                $message,
                'error'
            );

            return false;
        }

        if ($this->getTask() === 'apply') {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=match&layout=edit&tmpl=component&id=' . $id,
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS'),
                'message'
            );
        } else {
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
        }

        return true;
    }

    public function insertgooglecalendar()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $input = $app->getInput();
        $matchIds = (array) $input->post->get('cid', [], 'array');
        $projectId = $input->post->getInt('project_id');
        $calendarId = $input->post->getInt('calendar_id');
        $redirect = 'index.php?option=com_sportsmanagement&view=matches';

        if ($calendarId <= 0) {
            $this->setRedirect(
                $redirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_NO_GOOGLECALENDAR_ID'),
                'warning'
            );

            return false;
        }

        try {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $databaseSelector = $input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            );
            $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);
            $synchronizer = new GoogleCalendarMatchSynchronizer($db);
            $synchronizer->synchronize($matchIds, $projectId, $calendarId);

            $this->setRedirect(
                $redirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_GOOGLE_EVENT'),
                'message'
            );

            return true;
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $this->setRedirect($redirect, $exception->getMessage(), 'error');

            return false;
        }
    }
}
