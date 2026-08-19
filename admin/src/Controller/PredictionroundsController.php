<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 list controller for prediction rounds.
 */
final class PredictionroundsController extends SportsManagementAdminController
{
    public function getModel($name = 'Predictionround', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    public function saveshort(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $pks = array_values(array_filter(array_map('intval', $input->get('cid', [], 'array'))));
        $messageType = 'message';

        if (!$pks) {
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE_NO_SELECT');
            $messageType = 'error';
        } else {
            $message = $this->getModel()->saveshort($pks, $input->post->getArray());

            if ($message === false) {
                $message = Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
                $messageType = 'error';
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=predictionrounds',
            $message,
            $messageType
        );
    }

    /**
     * Add missing SportsManagement project rounds to the selected prediction game.
     */
    public function populateFromProjectRounds(): void
    {
        $this->checkToken();

        $app = $this->app;
        $predictionId = (int) $app->getUserStateFromRequest(
            'com_sportsmanagement.filter.prediction_id',
            'filter_prediction_id',
            0,
            'int'
        );
        $messageType = 'message';
        $message = '';

        if ($predictionId <= 0) {
            $message = Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_ID',
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME')
            );
            $messageType = 'error';
        } else {
            $roundsModel = parent::getModel('Predictionrounds', 'Administrator', ['ignore_request' => true]);
            $projectIds = $roundsModel->getPredictionProjectIds($predictionId);

            if (!$projectIds) {
                $message = Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
                $messageType = 'error';
            } else {
                $projectId = (int) $projectIds[0];
                $existingPredictionRoundIds = $roundsModel->getPredGamesPredictionRoundsIds($predictionId);
                $projectRoundIds = $roundsModel->getProjectRoundIds($projectId);
                $roundIdsToAdd = array_values(array_diff(
                    $projectRoundIds,
                    is_array($existingPredictionRoundIds) ? $existingPredictionRoundIds : []
                ));

                if ($roundIdsToAdd) {
                    $message = $this->getModel()->addPredRoundIds($roundIdsToAdd, $predictionId, $projectId);

                    if ($message === false) {
                        $message = Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
                        $messageType = 'error';
                    }
                } else {
                    $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_ALL_AVAILABLE');
                    $messageType = 'warning';
                }
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=predictionrounds',
            $message,
            $messageType
        );
    }
}
