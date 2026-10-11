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
    public function applyshortsinglematch(): void
    {
        $this->saveIndividualShort();
    }

    public function saveshortsinglematch(): void
    {
        $this->saveIndividualShort();
    }

    /** Preserve the historical no-op task until individual-sport deletion gets an explicit product decision. */
    public function deletesinglematch(): void
    {
        $this->setRedirect($this->returnUrl(), Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED'));
    }

    public function savestats(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, (int) ($post['match_id'] ?? $post['id'] ?? 0));
        $saved = $this->editMatchModel()->savestats($post);
        $message = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');
        return true;
    }

    public function saveReferees(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, (int) ($post['id'] ?? 0));
        $saved = $this->editMatchModel()->updateReferees($post);
        $message = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    public function saverosterbillard(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, (int) ($post['id'] ?? 0));
        $saved = $this->editMatchModel()->updateRosterBillard($post);
        $message = $saved
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED')
            : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    public function saveroster(): void
    {
        $this->checkToken();

        $post = $this->input->post->getArray();
        $this->assertMatchWriteAccess($post, (int) ($post['id'] ?? 0));
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

    public function saveshort(): void
    {
        $this->checkToken();

        $app = $this->app;
        $post = $this->input->post->getArray();
        $matchId = (int) ($post['matchid'] ?? 0);

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

    private function editMatchModel(): EditmatchModel
    {
        $model = $this->getModel('Editmatch');
        if (!$model instanceof EditmatchModel) {
            throw new \RuntimeException('EditmatchModel is unavailable.', 500);
        }

        return $model;
    }

    private function saveIndividualShort(): void
    {
        $this->checkToken();

        $app = $this->app;
        $input = $this->input;
        $post = $input->post->getArray();
        $ids = (array) $input->post->get('cid', [], 'array');
        $this->assertMatchWriteAccess($post, (int) ($post['match_id'] ?? 0));

        try {
            $saved = $this->individualMatchWriteService()->saveShort(
                $post,
                $ids,
                (int) $app->getIdentity()->id,
                $this->nowSql()
            );
        } catch (\Throwable $e) {
            $saved = false;
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
        }

        $this->setRedirect(
            $this->returnUrl(),
            $saved ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED') : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            $saved ? 'message' : 'error'
        );
    }

    /**
     * The POST token alone is not authorization. Validate each direct site
     * editing task against the same project-scoped match ACL used by results.
     */
    private function assertMatchWriteAccess(array $post, int $matchId): void
    {
        $projectId = (int) ($post['project_id'] ?? $post['p'] ?? $this->input->getInt('p', 0));

        if ($matchId <= 0 || $projectId <= 0) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 400);
        }

        $app = $this->app;
        $selector = (int) ($post['cfg_which_database']
            ?? $this->input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            ));

        $accessModel = new ResultsAccessModel();
        $accessModel->setDatabaseSelector($selector);
        $accessModel->setProjectId($projectId);

        if (!$accessModel->canEditMatches([$matchId])) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function individualMatchWriteService(): IndividualMatchWriteService
    {
        return new IndividualMatchWriteService($this->database());
    }

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

    private function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    private function nowSql(): string
    {
        return (new Date('now', new \DateTimeZone('UTC')))->toSql();
    }

    private function nullIfEmpty(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

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
