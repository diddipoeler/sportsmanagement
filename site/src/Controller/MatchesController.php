<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 controller for the edit-match AJAX actions. */
final class MatchesController extends BaseController
{
    private ?MatchMutationService $matchMutationService = null;

    public function saveevent(): void
    {
        $input = $this->getApplication()->getInput();
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
        $input = $this->getApplication()->getInput();
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
        $substitutionId = $this->getApplication()->getInput()->getInt('substid', 0);
        $result = $this->mutationService()->removeSubstitution($substitutionId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substitutionId;

        $this->sendLegacyJson($response);
    }

    public function savecomment(): void
    {
        $input = $this->getApplication()->getInput();
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
        $eventId = $this->getApplication()->getInput()->getInt('event_id');
        $result = $this->mutationService()->deleteEvent($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    public function removeCommentary(): void
    {
        $eventId = $this->getApplication()->getInput()->getInt('event_id');
        $result = $this->mutationService()->deleteCommentary($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    private function mutationService(): MatchMutationService
    {
        if ($this->matchMutationService instanceof MatchMutationService) {
            return $this->matchMutationService;
        }

        $app = $this->getApplication();
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $sportsDatabase = SportsManagementDatabaseResolver::resolve($joomlaDatabase, 0);
        $identity = method_exists($app, 'getIdentity') ? $app->getIdentity() : null;
        $userId = (int) ($identity->id ?? 0);

        $this->matchMutationService = new MatchMutationService(
            $joomlaDatabase,
            $sportsDatabase,
            $userId,
            Factory::getDate()->toSql()
        );

        return $this->matchMutationService;
    }

    private function sendLegacyJson(string $response): void
    {
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->getApplication()->close();
    }
}
