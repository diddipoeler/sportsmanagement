<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Native Joomla 5/6 controller for the edit-match AJAX actions.
 *
 * The HTTP/task dispatch is native. The six underlying match mutations are
 * still delegated to the historical administrator match model until that data
 * service is migrated separately.
 */
final class MatchesController extends BaseController
{
    public function saveevent(): void
    {
        $input = Factory::getApplication()->getInput();
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

        $model = $this->legacyMatchModel();
        $result = $model::saveevent($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');

        $this->sendLegacyJson($response);
    }

    public function savesubst(): void
    {
        $input = Factory::getApplication()->getInput();
        $data = [
            'in' => $input->getInt('in'),
            'out' => $input->getInt('out'),
            'matchid' => $input->getInt('matchid'),
            'in_out_time' => $input->get('in_out_time', '', 'raw'),
            'project_position_id' => $input->getInt('project_position_id'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
        ];

        $model = $this->legacyMatchModel();
        $result = $model::savesubstitution($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');

        $this->sendLegacyJson($response);
    }

    public function removeSubst(): void
    {
        $substitutionId = Factory::getApplication()->getInput()->getInt('substid', 0);
        $model = $this->legacyMatchModel();
        $result = $model::removeSubstitution($substitutionId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substitutionId;

        $this->sendLegacyJson($response);
    }

    public function savecomment(): void
    {
        $input = Factory::getApplication()->getInput();
        $data = [
            'event_time' => $input->get('event_time', '', 'raw'),
            'match_id' => $input->getInt('matchid'),
            'type' => $input->get('type', '', 'raw'),
            'notes' => $input->get('notes', '', 'raw'),
            'projecttime' => $input->get('projecttime', '', 'raw'),
        ];

        $model = $this->legacyMatchModel();
        $result = $model::savecomment($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');

        $this->sendLegacyJson($response);
    }

    public function removeEvent(): void
    {
        $eventId = Factory::getApplication()->getInput()->getInt('event_id');
        $model = $this->legacyMatchModel();
        $result = $model::deleteevent($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    public function removeCommentary(): void
    {
        $eventId = Factory::getApplication()->getInput()->getInt('event_id');
        $model = $this->legacyMatchModel();
        $result = $model::deletecommentary($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    private function legacyMatchModel(): string
    {
        LegacyBootstrap::bootForView('editmatch');

        if (!class_exists('sportsmanagementModelMatch')) {
            throw new \RuntimeException('SportsManagement legacy match mutation model not found.', 500);
        }

        return 'sportsmanagementModelMatch';
    }

    private function sendLegacyJson(string $response): void
    {
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Factory::getApplication()->close();
    }
}
