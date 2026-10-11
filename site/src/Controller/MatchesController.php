<?php
/**
 * Native Joomla 5/6 controller for the edit-match AJAX actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 controller for the edit-match AJAX actions. */
final class MatchesController extends BaseController
{
    private ?MatchMutationService $matchMutationService = null;

    public function saveevent(): void
    {
        $this->assertWritePermission();

        $input = $this->input->post;
        $data = [
            'teamplayer_id' => $input->getInt('teamplayer_id'),
            'projectteam_id' => $input->getInt('projectteam_id'),
            'event_type_id' => $input->getInt('event_type_id'),
            'event_time' => $input->get('event_time', '', 'raw'),
            'match_id' => $input->getInt('match_id'),
            'event_sum' => $input->get('event_sum', '', 'raw'),
            'notice' => $input->get('notice', '', 'raw'),
            'notes' => $input->get('notes', '', 'raw'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
            'useeventtime' => $input->get('useeventtime', '', 'raw'),
            'doubleevents' => $input->get('doubleevents', '', 'raw'),
        ];

        $result = $this->mutationService()->saveEvent($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');

        $this->sendLegacyJson($response);
    }

    public function savesubst(): void
    {
        $this->assertWritePermission();

        $input = $this->input->post;
        $data = [
            'in' => $input->getInt('in'),
            'out' => $input->getInt('out'),
            'matchid' => $input->getInt('matchid'),
            'in_out_time' => $input->get('in_out_time', '', 'raw'),
            'project_position_id' => $input->getInt('project_position_id'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
        ];

        $result = $this->mutationService()->saveSubstitution($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');

        $this->sendLegacyJson($response);
    }

    public function removeSubst(): void
    {
        $this->assertWritePermission();

        $substitutionId = $this->input->post->getInt('substid', 0);
        $result = $this->mutationService()->removeSubstitution($substitutionId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substitutionId;

        $this->sendLegacyJson($response);
    }

    public function savecomment(): void
    {
        $this->assertWritePermission();

        $input = $this->input->post;
        $data = [
            'event_time' => $input->get('event_time', '', 'raw'),
            'match_id' => $input->getInt('matchid'),
            'type' => $input->get('type', '', 'raw'),
            'notes' => $input->get('notes', '', 'raw'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
        ];

        $result = $this->mutationService()->saveComment($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');

        $this->sendLegacyJson($response);
    }

    public function removeEvent(): void
    {
        $this->assertWritePermission();

        $eventId = $this->input->post->getInt('event_id');
        $result = $this->mutationService()->deleteEvent($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    public function removeCommentary(): void
    {
        $this->assertWritePermission();

        $eventId = $this->input->post->getInt('event_id');
        $result = $this->mutationService()->deleteCommentary($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    private function mutationService(): MatchMutationService
    {
        if ($this->matchMutationService instanceof MatchMutationService) {
            return $this->matchMutationService;
        }

        $app = $this->app;
        $selector = $this->input->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        $joomlaDatabase = self::joomlaDatabase();
        $sportsDatabase = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
        $identity = $app->getIdentity();
        $userId = (int) ($identity->id ?? 0);
        $modified = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $this->matchMutationService = new MatchMutationService(
            $joomlaDatabase,
            $sportsDatabase,
            $userId,
            $modified
        );

        return $this->matchMutationService;
    }

    private function assertWritePermission(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        if (!$this->app->getIdentity()->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function sendLegacyJson(string $response): void
    {
        $app = $this->app;
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);

        // The edit-match JavaScript expects the old ampersand-separated string.
        echo json_encode(
            $response,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
        );
        $app->close();
    }
}
