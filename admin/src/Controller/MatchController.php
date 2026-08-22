<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\GoogleCalendarMatchSynchronizer;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
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
    public function insertgooglecalendar()
    {
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
