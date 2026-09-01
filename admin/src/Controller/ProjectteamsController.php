<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

final class ProjectteamsController extends SportsManagementAdminController
{
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

    public function set_playground_match(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->set_playground_match($this->app->getInput()->post->getArray());
        $this->redirectProjectTeams($ok, $model->getError());
    }

    public function set_playground(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->set_playground($this->app->getInput()->post->getArray());
        $this->redirectProjectTeams($ok, $model->getError());
    }

    public function assign(): void
    {
        $this->assertPostAndPermission('core.edit');
        $input = $this->app->getInput();
        $post = $input->post->getArray();
        $model = $this->model();
        $projectId = (int) ($post['project_id'] ?? $post['pid'] ?? 0);
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

    public function matchgroups(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $this->redirectProjectTeams($model->matchgroups(), $model->getError());
    }

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

    public function checkin(): void
    {
        $this->assertPostAndPermission('core.edit');
        $ids = $this->normaliseIds($this->app->getInput()->post->get('cid', [], 'array'));
        $model = $this->projectteamModel();
        $ok = $ids ? $model->checkin($ids) : false;
        $this->redirectProjectTeams($ok, $model->getError());
    }

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
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' <> ' . $projectId)
            ->order($db->quoteName('name') . ' ASC');

        try {
            $db->setQuery($query);
            $projects = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->redirectProjectTeams(false, $e->getMessage());
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

    public function publish(): void { $this->state(1); }
    public function unpublish(): void { $this->state(0); }
    public function archive(): void { $this->state(2); }
    public function trash(): void { $this->state(-2); }
    public function use_table_yes(): void { $this->flag('score', 1); }
    public function use_table_no(): void { $this->flag('score', 0); }
    public function use_table_points_yes(): void { $this->flag('finally', 1); }
    public function use_table_points_no(): void { $this->flag('finally', 0); }

    public function getModel($name = 'Projectteams', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }

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

    private function flag(string $flag, int $value): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $ok = $flag === 'score' ? $model->setScoreFlag($value) : $model->setFinallyFlag($value);
        $this->messageAndRedirect($model, $ok);
    }

    private function model(): ProjectteamsModel
    {
        $model = $this->getModel();
        if (!$model instanceof ProjectteamsModel) {
            throw new \RuntimeException('ProjectteamsModel is unavailable.', 500);
        }
        return $model;
    }

    private function projectteamModel(): ProjectteamModel
    {
        $model = parent::getModel('Projectteam', 'Administrator', ['ignore_request' => true]);
        if (!$model instanceof ProjectteamModel) {
            throw new \RuntimeException('ProjectteamModel is unavailable.', 500);
        }
        return $model;
    }

    private function finderNotifier(): FinderRelationNotifier
    {
        return new FinderRelationNotifier($this->database());
    }

    private function database(): DatabaseInterface
    {
        $input = $this->app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        return (new SportsManagementDatabaseResolver())->resolve(
            $selector,
            \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class)
        );
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

    private function messageAndRedirect(ProjectteamsModel $model, bool $ok): void
    {
        $this->app->enqueueMessage(
            $ok ? Text::_('JSAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $pid = (int) $model->getContextParams()['pid'];
        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid, false));
    }

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

    private function normaliseIds($ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
