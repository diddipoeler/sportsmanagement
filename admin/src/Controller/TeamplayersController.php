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

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 list controller for team players and staff. */
final class TeamplayersController extends SportsManagementAdminController
{
    /**
     * Save abbreviated team-player fields from the administrator list.
     * Checks component edit permission and the POST token, reports the
     * model result and returns to the selected team-player list.
     * @return void
     */
    public function saveshort(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $ok = $model->saveShort();
        $this->app->enqueueMessage(
            $ok ? Text::_('JSAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $this->redirectToList($model);
    }

    /**
     * Assign countries to the selected team players or staff.
     * Loads relevant people for Finder notifications, performs the native
     * model update and refreshes those Finder records on success.
     * @return void
     */
    public function assignplayerscountry(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $params = $model->getContextParams();
        $project = $model->getProjectContext();
        $team = $model->getTeamContext();
        $notifier = $this->finderNotifier();
        $personIds = $notifier->peopleForTeamContext(
            (int) ($team->team_id ?? $params['team_id'] ?? 0),
            (int) ($project->season_id ?? 0),
            (int) ($params['persontype'] ?? 0)
        );
        $ok = $model->assignPlayersCountry();

        if ($ok) {
            $notifier->notifyPeople($personIds);
        }

        $this->app->enqueueMessage(
            $ok ? Text::_('JLIB_APPLICATION_SAVE_SUCCESS') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $this->redirectToList($model);
    }

    /**
     * Remove selected team-player relations and update Finder.
     * Requires core.delete and a valid POST token, collects affected
     * people before removing their relations, then refreshes Finder.
     * @return void
     */
    public function delete(): void
    {
        $this->assertPostAndPermission('core.delete');
        $model = $this->model();
        $relationIds = (array) $this->app->getInput()->post->get('cid', [], 'array');
        $notifier = $this->finderNotifier();
        $personIds = $notifier->peopleForTeamRelations($relationIds);
        $ok = $model->deleteRelations();

        if ($ok) {
            $notifier->notifyPeople($personIds);
        }

        $this->app->enqueueMessage(
            $ok ? Text::_('COM_SPORTSMANAGEMENT_N_ITEMS_DELETED') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $this->redirectToList($model);
    }

    /**
     * Publish selected team-player relationships.
     * @return void
     */
    public function publish(): void
    {
        $this->state(1);
    }

    /**
     * Unpublish selected team-player relationships.
     * @return void
     */
    public function unpublish(): void
    {
        $this->state(0);
    }

    /**
     * Archive selected team-player relationships.
     * @return void
     */
    public function archive(): void
    {
        $this->state(2);
    }

    /**
     * Move selected team-player relationships to the trash.
     * @return void
     */
    public function trash(): void
    {
        $this->state(-2);
    }

    /**
     * Redirect the historical club-assignment toolbar to its form.
     * Retains the team, project, season and person-type context.
     * @return void
     */
    public function assignpersonsclub(): void
    {
        $input = $this->app->getInput();
        $query = [
            'option' => 'com_sportsmanagement',
            'view' => 'players',
            'tmpl' => 'component',
            'layout' => 'assignpersonsclub',
            'type' => $input->getInt('persontype', 1) === 2 ? 1 : 0,
            'pid' => $input->getInt('pid'),
            'team_id' => $input->getInt('team_id'),
            'persontype' => $input->getInt('persontype', 1),
            'season_id' => $input->getInt('season_id'),
            'whichview' => 'teamplayers',
            'assignclub' => 1,
        ];
        $this->setRedirect(Route::_('index.php?' . http_build_query($query), false));
    }

    /**
     * Resolve the native team-players model through Joomla MVC.
     * Preserves caller-supplied model options, defaulting to list-state
     * request handling for the legacy administrator overview.
     * @param string $name Requested model name.
     * @param string $prefix Administrator MVC namespace.
     * @param array $config Caller-provided model options.
     * @return mixed Parent controller's model result.
     */
    public function getModel($name = 'Teamplayers', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }

    /**
     * Update publication state for selected team-player records.
     * Checks core.edit.state and the POST token, changes relation state
     * through the native model and refreshes affected Finder entries.
     * @param int $value Joomla publication state.
     * @return void
     */
    private function state(int $value): void
    {
        $this->assertPostAndPermission('core.edit.state');
        $model = $this->model();
        $relationIds = (array) $this->app->getInput()->post->get('cid', [], 'array');
        $notifier = $this->finderNotifier();
        $personIds = $notifier->peopleForTeamRelations($relationIds);
        $ok = $model->setRelationState($value);

        if ($ok) {
            $notifier->notifyPeople($personIds);
        }

        $this->app->enqueueMessage(
            $ok ? Text::_('JLIB_APPLICATION_SUCCESS_BATCH') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $this->redirectToList($model);
    }

    /**
     * Require the native TeamplayersModel for write operations.
     * @return TeamplayersModel Validated model instance.
     * @throws \RuntimeException If Joomla cannot resolve the model.
     */
    private function model(): TeamplayersModel
    {
        $model = $this->getModel();

        if (!$model instanceof TeamplayersModel) {
            throw new \RuntimeException('TeamplayersModel is unavailable.', 500);
        }

        return $model;
    }

    /**
     * Construct a Finder relation notification service for the database.
     * @return FinderRelationNotifier Joomla Finder update helper.
     */
    private function finderNotifier(): FinderRelationNotifier
    {
        return new FinderRelationNotifier($this->sportsManagementDatabase());
    }

    /**
     * Resolve the effective SportsManagement administrator database.
     * Reads the configured database selector from request or user state
     * and delegates selection to the Joomla 5/6 database resolver.
     * @return DatabaseInterface Selected database connection.
     */
    private function sportsManagementDatabase(): DatabaseInterface
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
     * Fetch Joomla's database service from the dependency container.
     * @return DatabaseInterface Native database connection.
     * @throws \RuntimeException If the service is not a database interface.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement team-players database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Verify the Joomla POST session token and required ACL action.
     * @param string $permission Component permission required for the task.
     * @return void
     * @throws \RuntimeException If the request or identity is unauthorised.
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
     * Redirect to the team-players list with the active selection context.
     *
     * Keeps only scalar, nonnegative, validated integer filter values from
     * the model context. Rejects malformed arrays and objects instead of
     * implicitly casting them under PHP 8 or encoding them into the URL.
     *
     * @param TeamplayersModel $model Model supplying the view filter values.
     * @return void
     */
    private function redirectToList(TeamplayersModel $model): void
    {
        $params = [];

        foreach ($model->getContextParams() as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-z][a-z0-9_]*$/D', $key)) {
                continue;
            }

            if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
                continue;
            }

            $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($number !== false) {
                $params[$key] = $number;
            }
        }
        $query = http_build_query(array_merge(
            ['option' => 'com_sportsmanagement', 'view' => 'teamplayers'],
            $params
        ));
        $this->setRedirect(Route::_('index.php?' . $query, false));
    }
}
