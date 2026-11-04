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

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
use Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 controller for the edit-match AJAX actions. */
final class MatchesController extends BaseController
{
    private ?MatchMutationService $matchMutationService = null;

    /**
     * Save a match event through the Joomla-native mutation service.
     * Checks the POST token, edit privileges and parent-match scope before
     * persisting event fields and returning the legacy JSON response.
     * @return void
     */
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

        $this->assertMatchScope((int) $data['match_id']);

        $result = $this->mutationService()->saveEvent($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');

        $this->sendLegacyJson($response);
    }

    /**
     * Save a match-player substitution from an authenticated AJAX request.
     * Checks the supplied parent match and project against edit permissions
     * before passing substitution details to the shared mutation service.
     * @return void
     */
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

        $this->assertMatchScope((int) $data['matchid']);

        $result = $this->mutationService()->saveSubstitution($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');

        $this->sendLegacyJson($response);
    }

    /**
     * Delete a recorded substitution from its persisted parent match.
     * Looks up the substitution's match rather than trusting a client-supplied
     * parent ID, checks permission and emits the legacy result format.
     * @return void
     */
    public function removeSubst(): void
    {
        $this->assertWritePermission();

        $substitutionId = $this->input->post->getInt('substid', 0);
        $this->assertMatchScope($this->parentMatchId('#__sportsmanagement_match_player', $substitutionId, true));

        $result = $this->mutationService()->removeSubstitution($substitutionId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substitutionId;

        $this->sendLegacyJson($response);
    }

    /**
     * Save match commentary through the shared mutation service.
     * Validates CSRF and access to the requested match, then serialises the
     * translated success or error message for legacy AJAX consumers.
     * @return void
     */
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

        $this->assertMatchScope((int) $data['match_id']);

        $result = $this->mutationService()->saveComment($data);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': '
            : $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');

        $this->sendLegacyJson($response);
    }

    /**
     * Delete a match event after verifying its persisted match scope.
     * Authorises the actual parent match and returns an ID-bearing JSON
     * response compatible with existing frontend event controls.
     * @return void
     */
    public function removeEvent(): void
    {
        $this->assertWritePermission();

        $eventId = $this->input->post->getInt('event_id');
        $this->assertMatchScope($this->parentMatchId('#__sportsmanagement_match_event', $eventId, false));

        $result = $this->mutationService()->deleteEvent($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    /**
     * Delete a match commentary entry only for an editable match.
     * Uses the database parent relationship to prevent the request from
     * deleting an entry that belongs to another match or project.
     * @return void
     */
    public function removeCommentary(): void
    {
        $this->assertWritePermission();

        $eventId = $this->input->post->getInt('event_id');
        $this->assertMatchScope($this->parentMatchId('#__sportsmanagement_match_commentary', $eventId, false));

        $result = $this->mutationService()->deleteCommentary($eventId);
        $response = !$result
            ? '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': '
            : '1&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $eventId;

        $this->sendLegacyJson($response);
    }

    /**
     * Resolve Joomla's DatabaseInterface from its service container.
     * Provides the default database connection used for ACL and selected
     * SportsManagement database resolution.
     * @return DatabaseInterface Joomla-managed database service.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    /**
     * Create or reuse the match-write service for this POST request.
     * Uses a single validated database selector for both permission checks
     * and mutations, and includes the current user and UTC modification time.
     * @return MatchMutationService Service for event, substitution and comment writes.
     */
    private function mutationService(): MatchMutationService
    {
        if ($this->matchMutationService instanceof MatchMutationService) {
            return $this->matchMutationService;
        }

        $app = $this->app;
        // The ACL check and the mutation must select the same database.
        // Never let URL parameters or a stored user preference override the
        // database explicitly submitted with this CSRF-protected POST request.
        $selector = $this->postDatabaseSelector();
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

    /**
     * Require a valid POST token and component edit permission.
     * Rejects unauthorised requests before any match mutation is attempted.
     * @return void
     * @throws \RuntimeException On CSRF failure or insufficient permissions.
     */
    private function assertWritePermission(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Authorize the actual match, not just a component-wide editing action.
     * A supplied AJAX match ID must match the persisted parent for deletions.
     */
    private function assertMatchScope(int $matchId): void
    {
        $post = $this->input->post;
        $projectId = $post->getInt('p', 0);
        $requestMatchId = $post->getInt('matchid', 0);

        if ($matchId <= 0 || $projectId <= 0 || $requestMatchId !== $matchId) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $access = new ResultsAccessModel();
        $access->setDatabaseSelector($this->postDatabaseSelector());
        $access->setProjectId($projectId);

        if (!$access->canEditMatches([$matchId])) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /** Read one non-negative POST database selector for ACL, lookup and write. */
    private function postDatabaseSelector(): int
    {
        $raw = $this->input->post->get('cfg_which_database', 0, 'raw');

        // Do not silently turn malformed selectors into a different database.
        if (!is_int($raw) && (!is_string($raw) || !preg_match('/^[0-9]+$/D', $raw))) {
            throw new \InvalidArgumentException('Invalid SportsManagement database selector.', 400);
        }

        $selector = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($selector === false) {
            throw new \InvalidArgumentException('Invalid SportsManagement database selector.', 400);
        }

        return $selector;
    }

    /**
     * Resolve the real parent of a child row before checking its access.
     * The legacy substitution table may live in the selected sports database.
     */
    private function parentMatchId(string $table, int $childId, bool $sportsDatabase): int
    {
        if ($childId <= 0) {
            return 0;
        }

        $fallbackDatabase = self::joomlaDatabase();
        $db = $sportsDatabase
            ? SportsManagementDatabaseResolver::resolve(
                $fallbackDatabase,
                $this->postDatabaseSelector()
            )
            : $fallbackDatabase;

        try {
            $query = $db->createQuery()
                ->select($db->quoteName('match_id'))
                ->from($db->quoteName($table))
                ->where($db->quoteName('id') . ' = :childId')
                ->bind(':childId', $childId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);

            return (int) $db->loadResult();
        } catch (\Throwable) {
            // A failed child lookup must never authorize a destructive action.
            return 0;
        }
    }

    /**
     * Send a JSON-encoded string in the historical match AJAX format.
     * Preserves compatibility with existing match-editor scripts while setting
     * the HTTP content type and ending the request after output.
     * @param string $response Legacy status-and-message response string.
     * @return void
     */
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
