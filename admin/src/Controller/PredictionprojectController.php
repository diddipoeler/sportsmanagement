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
    public function store(): void
    {
        $this->checkToken();

        $data = $this->app->getInput()->post->get('jform', [], 'array');
        $model = $this->getModel('Predictionproject', 'Administrator', ['ignore_request' => true]);
        $success = $model->save($data);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=close&tmpl=component',
            Text::_($success ? 'JLIB_APPLICATION_SAVE_SUCCESS' : 'JLIB_APPLICATION_ERROR_SAVE_FAILED'),
            $success ? 'message' : 'error'
        );
    }
}
