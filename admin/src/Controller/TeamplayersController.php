<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 list controller for team players and staff. */
final class TeamplayersController extends SportsManagementAdminController
{
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

    public function publish(): void
    {
        $this->state(1);
    }

    public function unpublish(): void
    {
        $this->state(0);
    }

    public function archive(): void
    {
        $this->state(2);
    }

    public function trash(): void
    {
        $this->state(-2);
    }

    /** Compatibility redirect for the historical club-assignment toolbar action. */
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

    public function getModel($name = 'Teamplayers', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }

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

    private function model(): TeamplayersModel
    {
        $model = $this->getModel();

        if (!$model instanceof TeamplayersModel) {
            throw new \RuntimeException('TeamplayersModel is unavailable.', 500);
        }

        return $model;
    }

    private function finderNotifier(): FinderRelationNotifier
    {
        return new FinderRelationNotifier($this->sportsManagementDatabase());
    }

    private function sportsManagementDatabase(): DatabaseInterface
    {
        $input = $this->app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = $this->app->getContainer()->get(DatabaseInterface::class);

        return (new SportsManagementDatabaseResolver())->resolve($selector, $joomlaDatabase);
    }

    private function assertPostAndPermission(string $permission): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        if (!$this->app->getIdentity()->authorise($permission, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function redirectToList(TeamplayersModel $model): void
    {
        $params = array_filter(
            $model->getContextParams(),
            static fn ($value): bool => (int) $value !== 0
        );
        $query = http_build_query(array_merge(
            ['option' => 'com_sportsmanagement', 'view' => 'teamplayers'],
            $params
        ));
        $this->setRedirect(Route::_('index.php?' . $query, false));
    }
}
