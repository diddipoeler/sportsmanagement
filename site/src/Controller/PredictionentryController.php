<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionentryModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionmembershipModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictiontipModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

final class PredictionentryController extends BaseController
{
    public function select(): void
    {
        $this->assertPostToken();
        $model = $this->entryModel();
        $model->getEntryMember();
        $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId()));
    }

    public function selectprojectround(): void
    {
        $this->assertPostToken();
        $model = $this->entryModel();
        $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId()));
    }

    public function register(): void
    {
        $this->assertPostToken();
        $model = $this->membershipModel();
        $app = $this->getApplication();

        try {
            $memberId = $model->registerCurrentUser();
            if ($memberId <= 0) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5'));
            }
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_2'), 'message');
            $this->setRedirect($this->entryRoute($model, $memberId, ['s' => 1]));
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect($this->entryRoute($model, 0));
        }
    }

    public function addtipp(): void
    {
        $this->assertPostToken();
        $model = $this->tipModel();
        $app = $this->getApplication();
        $post = $app->getInput()->post->getArray();

        try {
            if (!$model->saveTips($post)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3'));
            }
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_1'), 'message');
            $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId(), ['eok' => 1]));
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId()));
        }
    }

    private function assertPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function entryModel(): PredictionentryModel
    {
        $model = $this->getModel('Predictionentry');
        if (!$model instanceof PredictionentryModel) {
            throw new \RuntimeException('PredictionentryModel is unavailable.', 500);
        }
        return $model;
    }

    private function tipModel(): PredictiontipModel
    {
        $model = $this->getModel('Predictiontip');
        if (!$model instanceof PredictiontipModel) {
            throw new \RuntimeException('PredictiontipModel is unavailable.', 500);
        }
        return $model;
    }

    private function membershipModel(): PredictionmembershipModel
    {
        $model = $this->getModel('Predictionmembership');
        if (!$model instanceof PredictionmembershipModel) {
            throw new \RuntimeException('PredictionmembershipModel is unavailable.', 500);
        }
        return $model;
    }

    private function entryRoute(PredictionentryModel $model, int $memberId, array $extra = []): string
    {
        $input = $this->getApplication()->getInput();
        $params = [
            'option' => 'com_sportsmanagement',
            'view' => 'predictionentry',
            'prediction_id' => $model->getPredictionGameId(),
            'uid' => max(0, $memberId),
            'pj' => $model->getProjectId(),
            'r' => $model->getRoundId(),
            'pggroup' => $model->getGroupId(),
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
        ] + $extra;

        return Route::_('index.php?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986), false);
    }
}
