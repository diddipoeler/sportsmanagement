<?php
/**
 * Joomla 5/6 administrator controller for project-team lists.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class ProjectteamsController extends SportsManagementAdminController
{
    /**
     * Save inline project-team fields and refresh affected Finder records.
     * @return void
     */
    public function saveshort(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ids = $this->normaliseIds($this->app->getInput()->post->get('cid', [], 'array'));
        $notifier = $this->finderNotifier();
        $before = $notifier->projectTeamEntitiesForRows($ids);
        $ok = $model->saveshort();

        if ($ok) {
            $notifier->notify($before, $notifier->projectTeamEntitiesForRows($ids));
        }

        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Assign a selected season-team to a project and notify Finder.
     * @return void
     */
    public function addteam(): void
    {
        $this->assertPostAndPermission('core.edit');
        $input = $this->app->getInput();
        $model = $this->model();
        $teamId = $input->post->getInt('team_id');
        $projectId = $input->post->getInt('pid', $input->getInt('pid'));
        $ok = $model->addNewProjectTeam($teamId, $projectId);

        if ($ok) {
            $notifier = $this->finderNotifier();
            $notifier->notify($notifier->projectTeamEntitiesForProject($projectId));
        }

        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Save match playground selections for project teams.
     * @return void
     */
    public function set_playground_match(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->set_playground_match($this->app->getInput()->post->getArray());
        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Save standard playground assignments for project teams.
     * @return void
     */
    public function set_playground(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->set_playground($this->app->getInput()->post->getArray());
        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Persist selected teams in a project and refresh Finder relations.
     * @return void
     */
    public function assign(): void
    {
        $this->assertPostAndPermission('core.edit');
        $input = $this->app->getInput();
        $post = $input->post->getArray();
        $model = $this->model();
        $projectId = $this->positiveSubmittedId($post['project_id'] ?? $post['pid'] ?? 0);
        $selected = $this->normaliseIds($post['project_teamslist'] ?? []);
        $notifier = $this->finderNotifier();
        $before = $notifier->projectTeamEntitiesForProject($projectId);
        $ok = $model->store([
            'id' => $projectId,
            'project_teamslist' => $selected,
        ]);

        if ($ok) {
            $notifier->notify($before, $notifier->projectTeamEntitiesForProject($projectId));
        } else {
            $this->app->enqueueMessage(
                $model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'warning'
            );
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Update selected project-team match group associations.
     * @return void
     */
    public function matchgroups(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $this->redirectProjectTeams($model->matchgroups(), $model->getError());
    }

    /**
     * Update project-team season references and notify Finder.
     * @return void
     */
    public function setseasonid(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ids = $this->normaliseIds($this->app->getInput()->post->get('cid', [], 'array'));
        $notifier = $this->finderNotifier();
        $before = $notifier->projectTeamEntitiesForRows($ids);
        $ok = $model->setseasonid();

        if ($ok) {
            $notifier->notify($before, $notifier->projectTeamEntitiesForRows($ids));
        }

        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Delete selected project-team rows and their Finder associations.
     * @return void
     */
    public function delete(): void
    {
        $this->assertPostAndPermission('core.delete');
        $ids = $this->normaliseIds($this->app->getInput()->post->get('cid', [], 'array'));
        $model = $this->projectteamModel();
        $notifier = $this->finderNotifier();
        $before = $notifier->projectTeamEntitiesForRows($ids);
        $ok = $ids ? $model->delete($ids) : false;

        if ($ok) {
            $notifier->notify($before);
        }

        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Release administrator checkouts for selected project teams.
     * @return void
     */
    public function checkin(): void
    {
        $this->assertPostAndPermission('core.edit');
        $ids = $this->normaliseIds($this->app->getInput()->post->get('cid', [], 'array'));
        $model = $this->projectteamModel();
        $ok = $ids ? $model->checkin($ids) : false;
        $this->redirectProjectTeams($ok, $model->getError());
    }

    /**
     * Prepare the project-team copy dialog and available destinations.
     *
     * Requires create permission, validates selected row IDs and loads
     * eligible destination projects with a bound source-project ID.
     * A failed query produces a generic warning instead of exposing SQL
     * or database credentials through an exception message.
     *
     * @return void
     */
    public function copy(): void
    {
        $this->assertPostAndPermission('core.create');
        $input = $this->app->getInput();
        $ids = $this->normaliseIds($input->post->get('cid', [], 'array'));
        $projectId = $input->post->getInt('pid', $input->getInt('pid'));

        if (!$ids) {
            $this->redirectProjectTeams(false, Text::_('JGLOBAL_NO_MATCHING_RESULTS'));
            return;
        }

        $model = $this->model();
        $db = $this->database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' <> :sourceProjectId')
            ->order($db->quoteName('name') . ' ASC')
            ->bind(':sourceProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            $projects = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->redirectProjectTeams(false, Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
            return;
        }

        $this->app->setUserState('com_sportsmanagement.projectteams.copy.ids', $ids);
        $this->app->setUserState('com_sportsmanagement.projectteams.copy.projects', $projects);
        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=projectteams&layout=copy&tmpl=component&pid=' . $projectId,
                false
            )
        );
    }

    /**
     * Copy selected project-team rows to the requested destination.
     * @return void
     */
    public function storecopy(): void
    {
        $this->assertPostAndPermission('core.create');
        $input = $this->app->getInput();
        $destination = $input->post->getInt('dest');
        $ids = $this->normaliseIds($input->post->get('ptids', [], 'array'));
        $model = $this->model();
        $notifier = $this->finderNotifier();
        $before = $notifier->projectTeamEntitiesForProject($destination);
        $ok = $model->copy($destination, $ids);

        if ($ok) {
            $notifier->notify($before, $notifier->projectTeamEntitiesForProject($destination));
        } else {
            $this->app->enqueueMessage(
                $model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'warning'
            );
        }

        $this->app->setUserState('com_sportsmanagement.projectteams.copy.ids', null);
        $this->app->setUserState('com_sportsmanagement.projectteams.copy.projects', null);
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Publish selected project-team relations.
     * @return void
     */
    public function publish(): void { $this->state(1); }
    /**
     * Unpublish selected project-team relations.
     * @return void
     */
    public function unpublish(): void { $this->state(0); }
    /**
     * Archive selected project-team relations.
     * @return void
     */
    public function archive(): void { $this->state(2); }
    /**
     * Move selected project-team relations to the trash.
     * @return void
     */
    public function trash(): void { $this->state(-2); }
    /**
     * Enable ranking score calculation for selected project teams.
     * @return void
     */
    public function use_table_yes(): void { $this->flag('score', 1); }
    /**
     * Disable ranking score calculation for selected project teams.
     * @return void
     */
    public function use_table_no(): void { $this->flag('score', 0); }
    /**
     * Enable final-points calculations for project-team relations.
     * @return void
     */
    public function use_table_points_yes(): void { $this->flag('finally', 1); }
    /**
     * Disable final-points calculations for project-team relations.
     * @return void
     */
    public function use_table_points_no(): void { $this->flag('finally', 0); }

    /**
     * Resolve the administrator project-teams list model via Joomla MVC.
     * Preserves additional caller options with request-dependent list state.
     * @param string $name Requested model name.
     * @param string $prefix Administrator MVC namespace.
     * @param array $config Caller model factory options.
     * @return mixed Resolved model or parent failure result.
     */
    public function getModel($name = 'Projectteams', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }

    /**
     * Change publication state of selected project-team relations.
     * @param int $value Requested Joomla publication state.
     * @return void
     */
    private function state(int $value): void
    {
        $this->assertPostAndPermission('core.edit.state');
        $ids = $this->normaliseIds($this->app->getInput()->post->get('cid', [], 'array'));
        $model = $this->model();
        $notifier = $this->finderNotifier();
        $before = $notifier->projectTeamEntitiesForRows($ids);
        $ok = $model->setProjectTeamState($value);

        if ($ok) {
            $notifier->notify($before, $notifier->projectTeamEntitiesForRows($ids));
        }

        $this->messageAndRedirect($model, $ok);
    }

    /**
     * Set a ranking score or final-points flag on selected team records.
     * @param string $flag Trusted score or final-points flag.
     * @param int $value Flag value, zero or one.
     * @return void
     */
    private function flag(string $flag, int $value): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $ok = $flag === 'score' ? $model->setScoreFlag($value) : $model->setFinallyFlag($value);
        $this->messageAndRedirect($model, $ok);
    }

    /**
     * Require a native ProjectteamsModel for list operations.
     * @return ProjectteamsModel Valid model instance.
     * @throws \RuntimeException If the list model cannot be resolved.
     */
    private function model(): ProjectteamsModel
    {
        $model = $this->getModel();
        if (!$model instanceof ProjectteamsModel) {
            throw new \RuntimeException('ProjectteamsModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Require the native ProjectteamModel for individual row updates.
     * @return ProjectteamModel Valid editor model.
     * @throws \RuntimeException If the editor model cannot be resolved.
     */
    private function projectteamModel(): ProjectteamModel
    {
        $model = parent::getModel('Projectteam', 'Administrator', ['ignore_request' => true]);
        if (!$model instanceof ProjectteamModel) {
            throw new \RuntimeException('ProjectteamModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Create the Finder relation notifier using the selected database.
     * @return FinderRelationNotifier Finder update service.
     */
    private function finderNotifier(): FinderRelationNotifier
    {
        return new FinderRelationNotifier($this->database());
    }

    /**
     * Resolve the active SportsManagement administrator database.
     * @return DatabaseInterface Component database connection.
     */
    private function database(): DatabaseInterface
    {
        $input = $this->app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        return (new SportsManagementDatabaseResolver())->resolve(
            $selector,
            self::joomlaDatabase()
        );
    }

    /**
     * Obtain Joomla's database service from the dependency container.
     * @return DatabaseInterface Primary Joomla database.
     * @throws \RuntimeException If the service does not implement DatabaseInterface.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement project-teams database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Require a valid POST token and component ACL permission.
     * @param string $permission Required Joomla ACL action.
     * @return void
     * @throws \RuntimeException If the token or permission is invalid.
     */
    private function assertPostAndPermission(string $permission): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise($permission, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Report a project-teams model result and redirect to the project list.
     * @param ProjectteamsModel $model Native project-teams model.
     * @param bool $ok Whether the model operation succeeded.
     * @return void
     */
    private function messageAndRedirect(ProjectteamsModel $model, bool $ok): void
    {
        $this->app->enqueueMessage(
            $ok ? Text::_('JSAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $pid = (int) ($model->getContextParams()['pid'] ?? 0);
        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid, false));
    }

    /**
     * Redirect to the current project/team list with operation feedback.
     * @param bool $ok Whether the operation succeeded.
     * @param string $error Optional user-visible failure message.
     * @return void
     */
    private function redirectProjectTeams(bool $ok, string $error = ''): void
    {
        $input = $this->app->getInput();
        $pid = $input->post->getInt('pid', $input->getInt('pid'));
        $division = $input->post->getInt('division', $input->getInt('division'));

        if (!$ok && $error !== '') {
            $this->app->enqueueMessage($error, 'warning');
        }

        $url = 'index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid;
        if ($division > 0) {
            $url .= '&division=' . $division;
        }
        $this->setRedirect(Route::_($url, false));
    }

    /**
     * Validate a single submitted positive project identifier.
     *
     * Rejects array input, malformed decimal strings and integer overflow
     * before passing the project context to database update operations.
     *
     * @param mixed $value Submitted project identifier.
     * @return int Valid positive ID, or zero for invalid input.
     */
    private function positiveSubmittedId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Validate a bounded set of submitted project-team row identifiers.
     * Rejects malformed, zero and overflowing IDs before write operations.
     * @param mixed $ids Raw selected project-team IDs.
     * @return array<int> Up to 500 unique positive IDs.
     */
    private function normaliseIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        $valid = [];

        foreach (array_slice($ids, 0, 500) as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            $raw = trim((string) $value);

            if (strlen($raw) > 20 || preg_match('/^[0-9]+$/D', $raw) !== 1) {
                continue;
            }

            $digits = ltrim($raw, '0');

            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id !== false) {
                $valid[$id] = $id;
            }
        }

        return array_values($valid);
    }
}
