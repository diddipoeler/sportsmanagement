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
    public function getModel($name = 'Predictionround', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    public function saveshort(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit');

        $input = $this->app->getInput();
        $pks = self::selectedRoundIds($input->get('cid', [], 'array'));
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
     * Add missing SportsManagement project rounds to the selected prediction game.
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

    /** Return at most 500 unique positive IDs without permissive integer casts. */
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
