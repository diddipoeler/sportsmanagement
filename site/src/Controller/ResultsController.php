<?php
/**
 * Joomla 5/6 frontend controller for SportsManagement results actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel;
use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/** Joomla 5/6 frontend controller for results actions. */
final class ResultsController extends BaseController
{
    public function saveReferees(): void
    {
        $this->assertPostToken();

        $input = $this->input;
        $post = $input->post->getArray();
        $layout = is_string($post['layout'] ?? null)
            ? $post['layout'] : $input->getCmd('layout', 'form');
        $redirect = $this->buildResultsRedirect($post, $layout);
        $matchId = self::validatedId($post['id'] ?? null);
        $projectId = self::validatedId($post['project_id'] ?? $post['p'] ?? null);
        $databaseSelector = self::validatedId(
            $post['cfg_which_database'] ?? $input->getInt('cfg_which_database', 0),
            true
        );

        if ($matchId === null || $projectId === null || $databaseSelector === null) {
            $this->setRedirect($redirect, Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return;
        }

        $accessModel = $this->resultsAccessModel($databaseSelector, $projectId);
        if (!$accessModel->canEditMatches([$matchId])) {
            $this->setRedirect($redirect, Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            return;
        }

        // Reuse the namespaced Joomla 5/6 match editor: it validates referee
        // positions and persists assignments through the shared write service.
        $model = $this->getModel('Editmatch');
        if (!$model instanceof EditmatchModel) {
            throw new \RuntimeException('EditmatchModel is unavailable.', 500);
        }

        $model->setDatabaseSelector($databaseSelector);
        $post['id'] = $matchId;
        $post['project_id'] = $projectId;
        $saved = $model->updateReferees($post);

        $this->setRedirect(
            $redirect,
            Text::_($saved
                ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES'
                : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR'),
            $saved ? 'message' : 'error'
        );
    }

    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }

    public function saveshort(): void
    {
        $this->assertPostToken();

        $app = $this->app;
        $input = $this->input;
        $post = $input->post->getArray();
        $layout = is_string($post['layout'] ?? null)
            ? $post['layout'] : $input->getCmd('layout', 'form');
        $selection = $input->post->get('cid', [], 'array');
        $matchIds = is_array($selection) ? self::validMatchIds($selection) : [];
        $databaseSelector = self::validatedId(
            $post['cfg_which_database'] ?? $input->getInt('cfg_which_database', 0),
            true
        );
        $projectId = self::validatedId($post['p'] ?? $input->getInt('p', 0));

        if ($matchIds === [] || $projectId === null || $databaseSelector === null) {
            $app->enqueueMessage(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'notice');
            $this->setRedirect($this->buildResultsRedirect($post, $layout));
            return;
        }

        $accessModel = $this->resultsAccessModel($databaseSelector, $projectId);

        if (!$accessModel->canEditMatches($matchIds)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->setRedirect($this->buildResultsRedirect($post, $layout));
            return;
        }

        if (!class_exists(ResultsEditModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsEditModel.php';
        }

        $editModel = new ResultsEditModel();
        $editModel->setDatabaseSelector($databaseSelector);

        if (!$editModel->saveShort($post, $matchIds)) {
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        $this->setRedirect($this->buildResultsRedirect($post, $layout));
    }

    /**
     * Treat an invalid selection as no selection: never modify a different
     * match when a posted checkbox value includes unexpected content.
     *
     * @return array<int>
     */
    /**
     * Reject malformed numeric IDs instead of converting strings such as
     * "12junk" or arrays to an unrelated positive ID during a write request.
     */
    private static function validatedId(mixed $value, bool $allowZero = false): ?int
    {
        $minimum = $allowZero ? 0 : 1;

        if (is_int($value)) {
            return $value >= $minimum ? $value : null;
        }

        if (!is_string($value) || !preg_match('/^(?:0|[1-9][0-9]*)$/D', $value)) {
            return null;
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $minimum],
        ]);

        return $validated === false ? null : $validated;
    }

    private static function validMatchIds(array $selection): array
    {
        // Reject oversized selections rather than building unbounded IN
        // clauses or silently writing only a subset of selected matches.
        if (count($selection) > 500) {
            return [];
        }

        $ids = [];

        foreach ($selection as $value) {
            if (!is_int($value) && (!is_string($value) || !preg_match('/^[1-9][0-9]*$/D', $value))) {
                return [];
            }

            $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id === false) {
                return [];
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    private function resultsAccessModel(int $databaseSelector, int $projectId): ResultsAccessModel
    {
        if (!class_exists(ResultsAccessModel::class)) {
            require_once dirname(__DIR__) . '/Model/SportsManagementModel.php';
            require_once dirname(__DIR__) . '/Model/SportsManagementProjectModel.php';
            require_once dirname(__DIR__) . '/Model/ResultsAccessModel.php';
        }

        $accessModel = new ResultsAccessModel();
        $accessModel->setDatabaseSelector($databaseSelector);
        $accessModel->setProjectId($projectId);

        return $accessModel;
    }

    private function assertPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function buildResultsRedirect(array $post, string $layout): string
    {
        // Only forward scalar route values, never raw POST arrays/objects.
        foreach (['cfg_which_database', 's', 'p', 'r', 'division', 'mode', 'order'] as $key) {
            if (!is_string($post[$key] ?? null) && !is_int($post[$key] ?? null)) {
                $post[$key] = '';
            }
        }

        return SiteRouteHelper::view('results', [
            'cfg_which_database' => (string) ($post['cfg_which_database'] ?? ''),
            's' => (string) ($post['s'] ?? ''),
            'p' => (string) ($post['p'] ?? ''),
            'r' => (string) ($post['r'] ?? ''),
            'division' => (string) ($post['division'] ?? ''),
            'mode' => (string) ($post['mode'] ?? ''),
            'order' => (string) ($post['order'] ?? ''),
            'layout' => $layout,
        ]);
    }
}
