<?php
/**
 * Joomla 5/6 frontend controller for match editing actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchWriteService;
use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend controller for match editing actions. */
final class EditmatchController extends FormController
{
    /**
     * Save short-form individual match edits through the shared write handler.
     *
     * Delegates to saveIndividualShort(), which checks Joomla's POST token and project-scoped match editing permission.
     *
     * @return void
     */
    public function applyshortsinglematch(): void
    {
        $this->saveIndividualShort();
    }

    /**
     * Persist short-form individual match changes from a legacy toolbar task.
     *
     * Uses the same secured individual-match write path as the Apply task.
     *
     * @return void
     */
    public function saveshortsinglematch(): void
    {
        $this->saveIndividualShort();
    }

    /**
     * Preserve the historical individual-match delete task without deleting data.
     *
     * The compatibility action redirects to the previous editing page; it does not remove a match record.
     *
     * @return void
     */
    public function deletesinglematch(): void
    {
        $this->setRedirect($this->returnUrl(), Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED'));
    }

    /**
     * Save the submitted match statistics for an authorised fixture.
     *
     * Checks the POST token and match-specific permissions before calling the native edit-match model.
     *
     * @return void
     */
    public function savestats(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, self::positiveInputId($post['match_id'] ?? $post['id'] ?? 0));
        $saved = $this->editMatchModel()->savestats($post);
        $message = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    /**
     * Close the frontend match editor without storing any changes.
     *
     * Redirects the legacy modal to Joomla's close view.
     *
     * @param mixed $key Unused legacy record identifier.
     * @return bool True after configuring the close-view redirect.
     */
    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');
        return true;
    }

    /**
     * Save referee assignments on an authorised match.
     *
     * Checks the Joomla POST token and fixture permissions before calling the match model.
     *
     * @return void
     */
    public function saveReferees(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, self::positiveInputId($post['id'] ?? 0));
        $saved = $this->editMatchModel()->updateReferees($post);
        $message = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    /**
     * Persist billiards match roster assignments.
     *
     * Requires a valid POST token and project-scoped match edit permission; reports the model's result.
     *
     * @return void
     */
    public function saverosterbillard(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, self::positiveInputId($post['id'] ?? 0));
        $saved = $this->editMatchModel()->updateRosterBillard($post);
        $message = $saved
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED')
            : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    /**
     * Save player and staff rosters for the selected match.
     *
     * Checks the session token and permissions, invokes both roster operations and reports whether both succeeded.
     *
     * @return void
     */
    public function saveroster(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, self::positiveInputId($post['id'] ?? 0));
        $model = $this->editMatchModel();
        $playersSaved = $model->updateRoster($post);
        $staffSaved = $model->updateStaff($post);
        $saved = $playersSaved && $staffSaved;

        $this->setRedirect(
            $this->returnUrl(),
            $saved ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED') : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            $saved ? 'message' : 'error'
        );
    }

    /**
     * Save abbreviated match fields from the frontend form.
     *
     * Validates the match target and editor rights, prepares modification metadata and delegates persistence to EditmatchModel.
     *
     * @return void
     */
    public function saveshort(): void
    {
        $this->checkToken();

        $app = $this->app;
        $post = $this->input->post->getArray();
        $matchId = self::positiveInputId($post['matchid'] ?? 0);

        $this->assertMatchWriteAccess($post, $matchId);

        $data = [
            'id' => $matchId,
            'team1_bonus' => $this->nullIfEmpty($post['team1_bonus'] ?? null),
            'team2_bonus' => $this->nullIfEmpty($post['team2_bonus'] ?? null),
            'team1_legs' => $this->nullIfEmpty($post['team1_legs'] ?? null),
            'team2_legs' => $this->nullIfEmpty($post['team2_legs'] ?? null),
            'modified' => $this->nowSql(),
            'modified_by' => (int) $app->getIdentity()->id,
            'cancel' => $post['cancel'] ?? 0,
            'cancel_reason' => $post['cancel_reason'] ?? '',
            'playground_id' => $post['playground_id'] ?? null,
            'overtime' => ($post['overtime'] ?? '') === '' ? 0 : $post['overtime'],
            'count_result' => $post['count_result'] ?? 0,
            'alt_decision' => $post['alt_decision'] ?? 0,
            'team_won' => $post['team_won'] ?? 0,
            'preview' => $post['preview'] ?? '',
            'match_result_detail' => $post['match_result_detail'] ?? '',
            'show_report' => $post['show_report'] ?? 0,
            'summary' => $post['summary'] ?? '',
            'old_match_id' => $post['old_match_id'] ?? 0,
            'new_match_id' => $post['new_match_id'] ?? 0,
            'team1_result_decision' => $post['team1_result_decision'] ?? null,
            'team2_result_decision' => $post['team2_result_decision'] ?? null,
            'decision_info' => $post['decision_info'] ?? '',
        ];

        if (isset($post['extended']) && is_array($post['extended'])) {
            $registry = new Registry();
            $registry->loadArray($post['extended']);
            $data['extended'] = (string) $registry;
        }

        $saved = $this->editMatchModel()->updItem($data);
        $message = $saved
            ? sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'), $matchId)
            : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    /**
     * Resolve the expected Joomla frontend match-editing model.
     *
     * Checks the MVC factory result before any edit operation is invoked.
     *
     * @return EditmatchModel
     */
    private function editMatchModel(): EditmatchModel
    {
        $model = $this->getModel('Editmatch');
        if (!$model instanceof EditmatchModel) {
            throw new \RuntimeException('EditmatchModel is unavailable.', 500);
        }

        return $model;
    }

    /**
     * Write short individual-sport match data through the dedicated service.
     *
     * Checks the Joomla form token and fixture permissions, delegates the write and provides a generic failure response.
     *
     * @return void
     */
    private function saveIndividualShort(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $this->input;
        $post = $input->post->getArray();
        $ids = (array) $input->post->get('cid', [], 'array');
        $this->assertMatchWriteAccess($post, self::positiveInputId($post['match_id'] ?? 0));

        try {
            $saved = $this->individualMatchWriteService()->saveShort(
                $post,
                $ids,
                (int) $app->getIdentity()->id,
                $this->nowSql()
            );
        } catch (\Throwable $e) {
            $saved = false;
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
        }

        $this->setRedirect(
            $this->returnUrl(),
            $saved ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED') : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            $saved ? 'message' : 'error'
        );
    }

    /**
     * Check the project-scoped match edit permission for a submitted fixture.
     *
     * A valid POST token alone is insufficient: both match and project IDs
     * must be positive, and ResultsAccessModel must authorise the selected
     * database, project and match combination before any update is started.
     *
     * @param array<string,mixed> $post Submitted match-editing context.
     * @param int $matchId Positive identifier of the requested match.
     * @return void
     * @throws \RuntimeException If the target is invalid or access is denied.
     */
    private function assertMatchWriteAccess(array $post, int $matchId): void
    {
        $projectId = self::positiveInputId($post['project_id'] ?? $post['p'] ?? $this->input->getInt('p', 0));

        if ($matchId <= 0 || $projectId <= 0) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 400);
        }

        $app = $this->app;
        $selector = self::nonNegativeInputId($post['cfg_which_database']
            ?? $this->input->getInt(
                'cfg_which_database',
                0
            ));

        $accessModel = new ResultsAccessModel();
        $accessModel->setDatabaseSelector($selector);
        $accessModel->setProjectId($projectId);

        if (!$accessModel->canEditMatches([$matchId])) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Create the individual-sport match persistence service.
     *
     * Uses the selected SportsManagement database connection for authorised writes.
     *
     * @return IndividualMatchWriteService
     */
    private function individualMatchWriteService(): IndividualMatchWriteService
    {
        return new IndividualMatchWriteService($this->database());
    }

    /**
     * Resolve the SportsManagement database used by match editing.
     *
     * Reads the Joomla database selector and delegates connection selection to the native resolver.
     *
     * @return DatabaseInterface
     */
    private function database(): DatabaseInterface
    {
        $app = $this->app;
        $selector = $this->input->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        return SportsManagementDatabaseResolver::resolve(
            $this->joomlaDatabase(),
            $selector
        );
    }

    /**
     * Obtain Joomla's registered DatabaseInterface instance.
     *
     * Uses the Joomla service container instead of deprecated global database access.
     *
     * @return DatabaseInterface
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    /**
     * Create the current UTC modification timestamp for database updates.
     *
     * Uses Joomla's native date API with an explicit UTC timezone.
     *
     * @return string
     */
    private function nowSql(): string
    {
        return (new Date('now', new \DateTimeZone('UTC')))->toSql();
    }

    /**
     * Convert an explicitly empty form field to a null database value.
     *
     * Preserves other scalar or structured values for subsequent model validation.
     *
     * @param mixed $value Submitted form field value.
     * @return mixed
     */
    private function nullIfEmpty(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

    /**
     * Validate a positive decimal identifier from a submitted match form.
     *
     * Rejects arrays, booleans, negative values and integer overflows instead
     * of coercing malformed input to an unrelated match or project record.
     *
     * @param mixed $value Raw submitted identifier.
     * @return int Positive identifier, or zero when malformed.
     */
    private static function positiveInputId(mixed $value): int
    {
        $id = self::nonNegativeInputId($value);

        return $id > 0 ? $id : 0;
    }

    /**
     * Parse a nonnegative database selector without PHP 8 implicit casts.
     *
     * Supports nonnegative integer values and zero-padded decimal strings;
     * malformed or overflowing selectors return zero.
     *
     * @param mixed $value Raw form selector.
     * @return int Valid selector or zero when invalid.
     */
    private static function nonNegativeInputId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? 0 : $number;
    }

    /**
     * Build a safe return destination after a frontend match edit.
     *
     * Rejects protocol-relative, malformed or cross-origin referrers, falling back to the edit-match component view.
     *
     * @return string
     */
    private function returnUrl(): string
    {
        $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        $fallback = 'index.php?option=com_sportsmanagement&view=editmatch';

        // Redirect targets must not contain control characters, a browser-
        // normalized backslash or a protocol-relative external hostname.
        if ($referer === '' || preg_match('/[\x00-\x1F\x7F\\\\]/', $referer)
            || str_starts_with($referer, '//')) {
            return $fallback;
        }

        $parts = parse_url($referer);

        if ($parts === false) {
            return $fallback;
        }

        if (!isset($parts['scheme'])) {
            // Relative Joomla paths and queries are safe only without a host
            // or userinfo. A URL with a non-http scheme is never accepted.
            return isset($parts['host']) || isset($parts['user']) || isset($parts['pass'])
                ? $fallback
                : $referer;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return $fallback;
        }

        $current = Uri::getInstance();

        return strcasecmp((string) $parts['host'], $current->getHost()) === 0
            ? $referer
            : $fallback;
    }
}
