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

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 list controller for prediction rounds.
 */
final class PredictionroundsController extends SportsManagementAdminController
{
    /**
     * Resolve a prediction-round model through Joomla administrator MVC.
     *
     * Preserves caller model options while using request-independent
     * loading for editing and importing prediction rounds.
     *
     * @param string $name Requested prediction round model.
     * @param string $prefix Joomla administrator MVC prefix.
     * @param array $config Additional Joomla model factory options.
     * @return mixed Parent controller model or failure value.
     */
    public function getModel($name = 'Predictionround', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Save inline changes to the selected prediction-game rounds.
     *
     * Checks the Joomla form token and edit permission, validates
     * POSTed round identifiers, delegates storage to the native model
     * and redirects with translated success or failure feedback.
     *
     * @return void
     * @throws \RuntimeException If the prediction round model is missing.
     */
    public function saveshort(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit');

        $input = $this->app->getInput();
        $pks = self::selectedRoundIds($input->post->get('cid', [], 'array'));
        $messageType = 'message';

        if (!$pks) {
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE_NO_SELECT');
            $messageType = 'error';
        } else {
            $model = $this->getModel();

            if (!is_object($model)) {
                throw new \RuntimeException('Predictionround model is unavailable.', 500);
            }

            $message = $model->saveshort($pks, $input->post->getArray());

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
     * Add missing project rounds to the active prediction game.
     *
     * Requires a valid Joomla token and create permission; compares
     * available project rounds with existing prediction rounds and
     * forwards only missing IDs to the native write model.
     *
     * @return void
     * @throws \RuntimeException If a required Joomla model is unavailable.
     */
    public function populateFromProjectRounds(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.create');

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

            if (!is_object($roundsModel)) {
                throw new \RuntimeException('Predictionrounds list model is unavailable.', 500);
            }

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
                    $model = $this->getModel();

                    if (!is_object($model)) {
                        throw new \RuntimeException('Predictionround model is unavailable.', 500);
                    }

                    $message = $model->addPredRoundIds($roundIdsToAdd, $predictionId, $projectId);

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

    /**
     * Validate a bounded collection of selected prediction round IDs.
     *
     * Ignores invalid, zero and overflowing values and deduplicates
     * positive decimal identifiers before an administrator write.
     *
     * @param mixed $selected Raw selected round identifiers.
     * @return array<int> Up to 500 unique positive round IDs.
     */
    private static function selectedRoundIds(mixed $selected): array
    {
        if (!is_array($selected)) {
            return [];
        }

        $ids = [];
        foreach (array_slice($selected, 0, 500) as $raw) {
            if (!is_int($raw) && !is_string($raw)) {
                continue;
            }

            if (is_string($raw) && !preg_match('/^[0-9]+$/D', $raw)) {
                continue;
            }

            $digits = ltrim((string) $raw, '0');
            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}
