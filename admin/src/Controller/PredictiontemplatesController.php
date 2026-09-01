<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplatesModel;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 list controller for prediction templates.
 */
final class PredictiontemplatesController extends SportsManagementAdminController
{
    public function getModel($name = 'Predictiontemplate', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    /**
     * Create one local prediction-template override from the configured master game.
     */
    public function createOverride(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $predictionId = $input->post->getInt(
            'prediction_id',
            (int) $this->app->getUserState('com_sportsmanagement.filter.prediction_id', 0)
        );
        $sourceTemplateId = $input->post->getInt('templateid', 0);
        $model = parent::getModel('Predictiontemplates', 'Administrator', ['ignore_request' => true]);
        $messageType = 'message';
        $message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');

        if (!$model instanceof PredictiontemplatesModel) {
            $message = Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE');
            $messageType = 'error';
        } else {
            $overrideId = $model->createMasterOverride($sourceTemplateId, $predictionId);

            if ($overrideId === false || (int) $overrideId <= 0) {
                $message = $model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
                $messageType = 'error';
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=predictiontemplates&filter_prediction_id=' . $predictionId,
            $message,
            $messageType
        );
    }
}
