<?php
/**
 * Joomla 5/6 administrator prediction-project form controller.
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
 * Native Joomla 5/6 form controller for prediction projects.
 */
final class PredictionprojectController extends SportsManagementFormController
{
    /**
     * Save a prediction project from the administrator modal editor.
     *
     * Validate Joomla's POST form token and record-level create/edit
     * authorisation before accessing the model. Resolve the native model
     * through the administrator MVC factory and reject unavailable save
     * services. Redirect back to the component modal with translated
     * success or error feedback after the authorised save.
     *
     * @return void The modal close redirect is registered.
     * @throws \RuntimeException On denied access or a missing save model.
     */
    public function store(): void
    {
        $data = $this->app->getInput()->post->get('jform', [], 'array');
        $this->assertFormSaveAccess($data);

        $model = $this->getModel('Predictionproject', 'Administrator', ['ignore_request' => true]);

        if (!is_object($model) || !is_callable([$model, 'save'])) {
            throw new \RuntimeException('SportsManagement prediction project model is unavailable.', 500);
        }

        $success = (bool) $model->save($data);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=close&tmpl=component',
            Text::_($success ? 'JLIB_APPLICATION_SAVE_SUCCESS' : 'JLIB_APPLICATION_ERROR_SAVE_FAILED'),
            $success ? 'message' : 'error'
        );
    }
}
