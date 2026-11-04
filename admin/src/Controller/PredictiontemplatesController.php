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

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplatesModel;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 list controller for prediction templates.
 */
final class PredictiontemplatesController extends SportsManagementAdminController
{
    /**
     * Retrieve an administrator prediction-template model using Joomla MVC.
     *
     * Defaults to request-independent model loading while retaining other
     * configuration options supplied by the caller.
     *
     * @param string $name Requested model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional model factory options.
     * @return mixed Joomla model or parent failure result.
     */
    public function getModel($name = 'Predictiontemplate', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => true], $config));
    }

    /**
     * Create an override from the master prediction game's template.
     *
     * Verifies the submitted Joomla form token, obtains filtered prediction
     * and template identifiers, and delegates creation to the native model.
     * Redirects back to the selected prediction game with result feedback.
     *
     * @return void
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
