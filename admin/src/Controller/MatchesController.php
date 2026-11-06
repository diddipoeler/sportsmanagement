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

use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchRefereeNotificationService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchTimelineWriteService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchWriteService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 administrator controller for match list write actions. */
final class MatchesController extends SportsManagementAdminController
{
    /**
     * Return the shared legacy JoomlaTune AJAX response instance.
     * @return object Shared legacy AJAX response.
     */
    public static function getAjaxResponse()
    {
        static $instance = null;

        if (!is_object($instance)) {
            $instance = new \JoomlaTuneAjaxResponse('utf-8');
        }

        return $instance;
    }

    /**
     * Delete a match event via the native timeline write service.
     * Requires component delete permission and a valid POST token.
     * @return void
     */
    public function removeEvent(): void
    {
        $this->assertWritePermission('core.delete');

        $eventId = $this->app->getInput()->post->getInt('event_id');
        $result = $this->timelineAction(
            fn (): bool => $this->timelineWriteService()->deleteEvent($eventId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS'),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS'),
            $eventId
        );
        $this->jsonClose($result);
    }

    /**
     * Delete a match commentary via the native timeline service.
     * Requires component delete permission and a valid POST token.
     * @return void
     */
    public function removeCommentary(): void
    {
        $this->assertWritePermission('core.delete');

        $eventId = $this->app->getInput()->post->getInt('event_id');
        $result = $this->timelineAction(
            fn (): bool => $this->timelineWriteService()->deleteCommentary($eventId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY'),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY'),
            $eventId
        );
        $this->jsonClose($result);
    }

    /**
     * Remove a match substitution through the native timeline service.
     * Requires component delete permission and a valid POST token.
     * @return void
     */
    public function removeSubst(): void
    {
        $this->assertWritePermission('core.delete');

        $substitutionId = $this->app->getInput()->post->getInt('substid');
        $result = $this->timelineAction(
            fn (): bool => $this->timelineWriteService()->removeSubstitution($substitutionId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST'),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST'),
            $substitutionId
        );
        $this->jsonClose($result);
    }

    /**
     * Save a match event from POST using the native timeline service.
     * Maintains the existing JSON response used by the legacy editor.
     * @return void
     */
    public function saveevent(): void
    {
        $this->assertWritePermission('core.edit');

        $input = $this->app->getInput()->post;
        $data = [
            'teamplayer_id' => $input->getInt('teamplayer_id'),
            'projectteam_id' => $input->getInt('projectteam_id'),
            'event_type_id' => $input->getInt('event_type_id'),
            'event_time' => $input->getString('event_time', ''),
            'match_id' => $input->getInt('match_id'),
            'event_sum' => $input->get('event_sum', '', 'raw'),
            'notice' => $input->get('notice', '', 'raw'),
            'notes' => $input->get('notes', '', 'raw'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
            'useeventtime' => $input->getBool('useeventtime'),
            'doubleevents' => $input->getBool('doubleevents'),
        ];

        try {
            $eventId = $this->timelineWriteService()->saveEvent($data);
            $result = $eventId
                ? $eventId . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT')
                : '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT');
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT');
        }

        $this->jsonClose($result);
    }

    /**
     * Save a match commentary entry from validated POST parameters.
     * Returns the legacy string-valued JSON response.
     * @return void
     */
    public function savecomment(): void
    {
        $this->assertWritePermission('core.edit');

        $input = $this->app->getInput()->post;
        $data = [
            'event_time' => $input->getString('event_time', ''),
            'match_id' => $input->getInt('matchid'),
            'type' => $input->get('type', '', 'raw'),
            'notes' => $input->get('notes', '', 'raw'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
        ];

        try {
            $commentId = $this->timelineWriteService()->saveComment($data);
            $result = $commentId
                ? $commentId . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT')
                : '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT');
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT');
        }

        $this->jsonClose($result);
    }

    /**
     * Save a player substitution through the native timeline service.
     * Returns the legacy string-valued JSON response.
     * @return void
     */
    public function savesubst(): void
    {
        $this->assertWritePermission('core.edit');

        $input = $this->app->getInput()->post;
        $data = [
            'in' => $input->getInt('in'),
            'out' => $input->getInt('out'),
            'matchid' => $input->getInt('matchid'),
            'in_out_time' => $input->getString('in_out_time', ''),
            'project_position_id' => $input->getInt('project_position_id'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
        ];

        try {
            $saved = $this->timelineWriteService()->saveSubstitution($data);
            $result = $saved
                ? $this->database()->insertid() . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST')
                : '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST');
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST');
        }

        $this->jsonClose($result);
    }

    /**
     * Save match statistics and redirect to the statistics editor.
     * @return void
     */
    public function savestats(): void
    {
        $this->assertWritePermission();

        $app = $this->app;
        $input = $app->getInput();
        $post = $input->post->getArray();

        try {
            $saved = $this->matchWriteService()->saveStatistics($post);
        } catch (\Throwable $e) {
            $saved = false;
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        $msg = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');
        $this->setRedirect($this->editRedirect('editstats', $post), $msg);
    }

    /**
     * Save match player and staff rosters from submitted form data.
     * @return void
     */
    public function saveroster(): void
    {
        $this->assertWritePermission();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $projectId = (int) ($post['project_id'] ?? 0);
        $post['positions'] = $this->projectPositions($projectId, 1);
        $post['staffpositions'] = $this->projectPositions($projectId, 2);
        $msg = '';

        try {
            if ($this->matchWriteService()->updateRoster($post)) {
                $msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER') . '<br />';
                $msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER_TRIKOT') . '<br />';
            } else {
                $msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER') . '<br />';
                $msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER_TRIKOT') . '<br />';
            }

            $msg .= $this->matchWriteService()->updateStaff($post)
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_STAFF') . '<br />'
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_STAFF') . '<br />';
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $msg .= Text::_('JERROR_AN_ERROR_HAS_OCCURRED');
        }

        $this->setRedirect($this->editRedirect('editlineup', $post), $msg);
    }

    /**
     * Save match referee appointments and send assignment notifications.
     * @return void
     */
    public function saveReferees(): void
    {
        $this->assertWritePermission();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $post['positions'] = $this->projectPositions((int) ($post['project_id'] ?? 0), 3);

        try {
            $result = $this->matchWriteService()->updateReferees($post);
            if ($result['success']) {
                $this->refereeNotifier()->notifyNewAssignments((int) ($post['id'] ?? 0), $result['added']);
                $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES');
            } else {
                $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR');
            }
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR');
        }

        $this->setRedirect($this->editRedirect('editreferees', $post, false), $msg);
    }

    /**
     * Include selected matches in result counting.
     * @return void
     */
    public function count_result_yes(): void
    {
        $this->setCountResult(1);
        $this->redirectToList();
    }

    /**
     * Exclude selected matches from result counting.
     * @return void
     */
    public function count_result_no(): void
    {
        $this->setCountResult(0);
        $this->redirectToList();
    }

    /**
     * Run the historical short match editor including date-change side effects.
     *
     * Requires Joomla's component edit permission and POST session token
     * before calling the transitional legacy match model.
     *
     * @return void
     */
    public function saveshort(): void
    {
        $this->assertWritePermission();

        $this->legacyMatchModel()->saveshort();
        $this->redirectToList();
    }

    /**
     * Update result-count flags for validated selected match IDs.
     * Requires core.edit.state permission and a valid POST token.
     * @param int $value Flag value, zero or one.
     * @return void
     */
    private function setCountResult(int $value): void
    {
        $this->assertWritePermission('core.edit.state');

        $app = $this->app;
        $selected = $app->getInput()->post->get('cid', [], 'array');
        $ids = [];

        foreach (array_slice(is_array($selected) ? $selected : [], 0, 500) as $raw) {
            if (!is_int($raw) && !is_string($raw)) {
                continue;
            }

            $value = trim((string) $raw);

            if (strlen($value) > 20 || preg_match('/^[0-9]+$/D', $value) !== 1) {
                continue;
            }

            $digits = ltrim($value, '0');
            $id = $digits !== ''
                ? filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;

            if ($id !== false) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return;
        }

        $db = $this->database();
        foreach ($ids as $id) {
            try {
                $row = (object) ['id' => $id, 'count_result' => $value];
                $db->updateObject('#__sportsmanagement_match', $row, 'id');
                $app->enqueueMessage(sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'), $id), 'notice');
            } catch (\Throwable $e) {
                Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            }
        }
    }

    /** @return array<int,object> */
    private function projectPositions(int $projectId, int $personType): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('pos.id', 'posid'),
                $db->quoteName('pos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pos.persontype') . ' = ' . $personType)
            ->order($db->quoteName('pos.ordering') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList('value') ?: [];
    }

    /**
     * Build the match edit redirect or close the modal editor.
     * @param string $layout Destination edit layout.
     * @param array $post Submitted form values.
     * @param bool $withTeam Include current team context.
     * @return string Joomla administrator match editor URL.
     */
    private function editRedirect(string $layout, array $post, bool $withTeam = true): string
    {
        $input = $this->app->getInput();
        if ($input->getInt('close') === 1) {
            return 'index.php?option=com_sportsmanagement&view=close&tmpl=component';
        }

        $link = 'index.php?option=com_sportsmanagement&close=' . $input->getInt('close')
            . '&tmpl=component&view=match&layout=' . $layout
            . '&id=' . (int) ($post['id'] ?? 0);
        if ($withTeam) {
            $link .= '&team=' . (int) ($post['team'] ?? 0);
        }
        return $link;
    }

    /**
     * Perform a protected timeline change and format its result.
     * Logs errors without exposing internal details to the AJAX caller.
     * @param callable $action Timeline write callback.
     * @param string $error Translated failure message.
     * @param string $success Translated success message.
     * @param int $id Event or substitution identifier.
     * @return string Legacy AJAX result payload.
     */
    private function timelineAction(callable $action, string $error, string $success, int $id): string
    {
        try {
            return $action() ? '1&' . $success . '&' . $id : '0&' . $error;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');

            return '0&' . $error;
        }
    }

    /**
     * Return a legacy string-valued JSON response and close Joomla.
     * @param string $result Preformatted AJAX result.
     * @return void
     */
    private function jsonClose(string $result): void
    {
        $app = $this->app;
        $app->getDocument()->setMimeEncoding('application/json');
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);

        // Keep the legacy string-valued JSON contract used by match editors.
        echo json_encode(
            $result,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
        );
        $app->close();
    }

    /**
     * Redirect the administrator to the configured matches list.
     * @return void
     */
    private function redirectToList(): void
    {
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Construct the native match data write service.
     * @return MatchWriteService Match write service for the active database.
     */
    private function matchWriteService(): MatchWriteService
    {
        return new MatchWriteService($this->database());
    }

    /**
     * Construct the native match timeline write service.
     * @return MatchTimelineWriteService Timeline persistence service.
     */
    private function timelineWriteService(): MatchTimelineWriteService
    {
        return new MatchTimelineWriteService($this->database());
    }

    /**
     * Build the referee notification service using Joomla mail support.
     * @return MatchRefereeNotificationService Referee notification service.
     */
    private function refereeNotifier(): MatchRefereeNotificationService
    {
        return new MatchRefereeNotificationService(
            $this->database(),
            \Joomla\CMS\Factory::getContainer()->get(MailerFactoryInterface::class),
            $this->app
        );
    }

    /**
     * Resolve the selected SportsManagement match database connection.
     * Tries the legacy component resolver and falls back to Joomla's
     * default connection when the selected connection is unavailable.
     * @return DatabaseInterface Effective component database.
     */
    private function database(): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        $app = $this->app;
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        try {
            $db = \sportsmanagementHelper::getDBConnection(true, $selector);
            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }

        return self::joomlaDatabase();
    }

    /**
     * Resolve Joomla's database service from the dependency container.
     * @return DatabaseInterface Joomla database interface.
     * @throws \RuntimeException When the database service is invalid.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement matches database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Instantiate the compatibility match model for inline saves.
     * @return object Loaded historical match editor model.
     */
    private function legacyMatchModel(): object
    {
        if (!class_exists('JSMModelAdmin', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/sportsmanagement/model.php';
        }
        if (!class_exists('sportsmanagementModelMatch', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/match.php';
        }
        return new \sportsmanagementModelMatch();
    }
}
