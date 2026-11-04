<?php    /**
     * Cancel editing a prediction member and return to its editor route.
     *
     * Keeps the historic task name, verifies the submitted Joomla POST token
     * and resolves the selected member without changing stored information.
     *
     * @param string|null $key Unused legacy record identifier.
     * @return void
     * @throws \RuntimeException On invalid token or unavailable model.
     */
    public function cancel($key = null): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionMemberModel();
        $this->setRedirect($this->buildEditorRoute($model));
    }

    /**
     * Require a valid Joomla POST CSRF token for member operations.
     *
     * Stops state-changing or selection tasks before resolving models when
     * the request lacks the token expected by Joomla's session service.
     *
     * @return void
     * @throws \RuntimeException If CSRF token verification fails.
     */
    private function checkPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    /**
     * Resolve the Joomla frontend model for prediction-user selection.
     *
     * Checks the MVC factory result so that member navigation never reads
     * data from a missing or incompatible component model.
     *
     * @return PredictionusersModel Validated prediction users model.
     * @throws \RuntimeException If the expected model is unavailable.
     */
    private function getPredictionUsersModel(): PredictionusersModel
    {
        $model = $this->getModel('Predictionusers');
        if (!$model instanceof PredictionusersModel) {
            throw new \RuntimeException('PredictionusersModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Resolve the Joomla model for editing prediction members.
     *
     * Requires the native PredictionmemberModel so permission checks and
     * member writes use the same component-level data access implementation.
     *
     * @return PredictionmemberModel Validated member model.
     * @throws \RuntimeException If the model is unavailable.
     */
    private function getPredictionMemberModel(): PredictionmemberModel
    {
        $model = $this->getModel('Predictionmember');
        if (!$model instanceof PredictionmemberModel) {
            throw new \RuntimeException('PredictionmemberModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Construct the prediction member navigation URL.
     *
     * Reads game, project, group and round context from the validated model;
     * takes the selected member ID from POST as a filtered integer and
     * preserves the current SportsManagement database selector.
     *
     * @param PredictionusersModel $model Model exposing prediction selection.
     * @return string Canonical prediction member URL.
     */
    private function buildMemberRoute(PredictionusersModel $model): string
    {
        $input = $this->input;

        return PredictionRouteHelper::member(
            $model->getPredictionGameId(),
            $input->post->getInt('uid', $model->getSelectedMemberNumericId()),
            null,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0)
        );
    }

    /**
     * Construct the return URL for the prediction-member editor.
     *
     * Reads the selected member, game, project, group and round from the
     * editor model and preserves the active SportsManagement database ID.
     *
     * @param PredictionmemberModel $model Native editable-member model.
     * @return string Canonical URL for the member editor.
     */
    private function buildEditorRoute(PredictionmemberModel $model): string
    {
        $input = $this->input;

        return PredictionRouteHelper::member(
            $model->getPredictionGameId(),
            $model->getSelectedMemberNumericId(),
            null,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0)
        );
    }
}
