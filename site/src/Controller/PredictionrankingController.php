<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

final class PredictionrankingController extends BaseController
{
    public function selectprojectround(): void
    {
        if (!Session::checkToken()) {
            throw new \RuntimeException('Invalid token.', 403);
        }

        $input = $this->input;
        $model = $this->getModel('Predictionranking');
        if (!$model instanceof PredictionrankingModel) {
            throw new \RuntimeException('Prediction ranking model not available.', 500);
        }

        $predictionId = $input->getInt('prediction_id', $model->getPredictionGameId());
        $projectId = $this->extractId((string) $input->get('pj', '', 'string')) ?: $model->getProjectId();
        $roundId = $this->extractId((string) $input->get('r', '', 'string')) ?: $model->getProjectCurrentRoundId($projectId);

        $query = Uri::buildQuery([
            'option' => 'com_sportsmanagement',
            'view' => 'predictionranking',
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            'prediction_id' => $predictionId,
            'pj' => $projectId,
            'r' => $roundId,
            'pggroup' => $input->getInt('pggroup', 0),
            'pggrouprank' => $input->getInt('pggrouprank', 0),
            'type' => $input->getInt('type', 0),
            'from' => $this->extractId((string) $input->get('from', '', 'string')),
            'to' => $this->extractId((string) $input->get('to', '', 'string')),
        ]);

        $this->setRedirect(Route::_('index.php?' . $query, false));
    }

    private function extractId(string $value): int
    {
        return $value === '' ? 0 : max(0, (int) strtok($value, ':'));
    }
}
