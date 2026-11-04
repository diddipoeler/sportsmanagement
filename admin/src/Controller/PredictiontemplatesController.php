<?php    /**
     * Create a local prediction-template override from a master template.
     *
     * Validates the Joomla form token, reads integer-filtered prediction
     * and template IDs, then delegates creation to PredictiontemplatesModel.
     * Afterwards redirects back to the selected prediction templates view
     * with the success or failure message.
     *
     * @return void
     * @throws \RuntimeException If Joomla form token validation fails.
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
