<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchRefereeNotificationService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchTimelineWriteService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchWriteService;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 administrator controller for match list write actions. */
final class MatchesController extends SportsManagementAdminController
{
    public static function getAjaxResponse()
    {
        static $instance = null;

        if (!is_object($instance)) {
            $instance = new \JoomlaTuneAjaxResponse('utf-8');
        }

        return $instance;
    }

    public function removeEvent(): void
    {
        $eventId = $this->app->getInput()->getInt('event_id');
        $result = $this->timelineAction(
            fn (): bool => $this->timelineWriteService()->deleteEvent($eventId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS'),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS'),
            $eventId
        );
        $this->jsonClose($result);
    }

    public function removeCommentary(): void
    {
        $eventId = $this->app->getInput()->getInt('event_id');
        $result = $this->timelineAction(
            fn (): bool => $this->timelineWriteService()->deleteCommentary($eventId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY'),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY'),
            $eventId
        );
        $this->jsonClose($result);
    }

    public function removeSubst(): void
    {
        $substitutionId = $this->app->getInput()->getInt('substid');
        $result = $this->timelineAction(
            fn (): bool => $this->timelineWriteService()->removeSubstitution($substitutionId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST'),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST'),
            $substitutionId
        );
        $this->jsonClose($result);
    }

    public function saveevent(): void
    {
        $input = $this->app->getInput();
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
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': ' . $e->getMessage();
        }

        $this->jsonClose($result);
    }

    public function savecomment(): void
    {
        $input = $this->app->getInput();
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
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': ' . $e->getMessage();
        }

        $this->jsonClose($result);
    }

    public function savesubst(): void
    {
        $input = $this->app->getInput();
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
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': ' . $e->getMessage();
        }

        $this->jsonClose($result);
    }

    public function savestats(): void
    {
        $app = $this->app;
        $input = $app->getInput();
        $post = $input->post->getArray();

        try {
            $saved = $this->matchWriteService()->saveStatistics($post);
        } catch (\Throwable $e) {
            $saved = false;
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $msg = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');
        $this->setRedirect($this->editRedirect('editstats', $post), $msg);
    }

    public function saveroster(): void
    {
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
            $msg .= $e->getMessage();
        }

        $this->setRedirect($this->editRedirect('editlineup', $post), $msg);
    }

    public function saveReferees(): void
    {
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
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR') . ': ' . $e->getMessage();
        }

        $this->setRedirect($this->editRedirect('editreferees', $post, false), $msg);
    }

    public function count_result_yes(): void
    {
        $this->setCountResult(1);
        $this->redirectToList();
    }

    public function count_result_no(): void
    {
        $this->setCountResult(0);
        $this->redirectToList();
    }

    /** Transitional adapter: saveshort still contains tournament/date-change side effects in the legacy model. */
    public function saveshort(): void
    {
        $this->legacyMatchModel()->saveshort();
        $this->redirectToList();
    }

    private function setCountResult(int $value): void
    {
        $app = $this->app;
        $ids = array_values(array_filter(array_map('intval', (array) $app->getInput()->post->get('cid', [], 'array'))));
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
                $app->enqueueMessage($e->getMessage(), 'error');
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

    private function timelineAction(callable $action, string $error, string $success, int $id): string
    {
        try {
            return $action() ? '1&' . $success . '&' . $id : '0&' . $error;
        } catch (\Throwable $e) {
            return '0&' . $error . ': ' . $e->getMessage();
        }
    }

    private function jsonClose(string $result): void
    {
        echo json_encode($result);
        $this->app->close();
    }

    private function redirectToList(): void
    {
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    private function matchWriteService(): MatchWriteService
    {
        return new MatchWriteService($this->database());
    }

    private function timelineWriteService(): MatchTimelineWriteService
    {
        return new MatchTimelineWriteService($this->database());
    }

    private function refereeNotifier(): MatchRefereeNotificationService
    {
        return new MatchRefereeNotificationService(
            $this->database(),
            \Joomla\CMS\Factory::getContainer()->get(MailerFactoryInterface::class),
            $this->app
        );
    }

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

        return \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
    }

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
