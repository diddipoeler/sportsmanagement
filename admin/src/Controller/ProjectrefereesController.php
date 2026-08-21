<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereeModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 administrator controller for project referees.
 */
final class ProjectrefereesController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $model = $this->getModel();
        $success = $model->saveshort(
            $input->post->get('cid', [], 'array'),
            $input->post->getArray()
        );

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=projectreferees', false),
            '',
            $success ? 'message' : 'error'
        );
    }

    public function publish(): void
    {
        $model = $this->projectrefereeModel();
        $ids = (array) $this->app->getInput()->post->get('cid', [], 'array');
        $notifier = new FinderRelationNotifier($model->getDatabase());
        $personIds = $notifier->projectRefereePeopleForRows($ids);

        parent::publish();

        $notifier->notifyPeople($personIds);
    }

    public function getModel($name = 'Projectreferee', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    private function projectrefereeModel(): ProjectrefereeModel
    {
        $model = $this->getModel();

        if (!$model instanceof ProjectrefereeModel) {
            throw new \RuntimeException('ProjectrefereeModel is unavailable.', 500);
        }

        return $model;
    }
}
