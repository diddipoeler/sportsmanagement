<?php
/**
 * Native Joomla 5/6 controller for the random-quote module file editor.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 controller for the random-quote module file editor.
 */
final class SmquotetxtController extends FormController
{
    public function cancel($key = null)
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smquotestxt&layout=default', false)
        );

        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $data = $this->app->getInput()->post->get('jform', [], 'array');
        $model = $this->getModel();
        $success = $model->save($data);
        $task = $this->getTask();
        $fileName = rawurlencode((string) ($data['filename'] ?? ''));

        if (!$success) {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=smquotetxt&layout=default&file_name=' . $fileName,
                    false
                ),
                $model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'),
                'error'
            );

            return false;
        }

        if ($task === 'apply') {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=smquotetxt&layout=default&file_name=' . $fileName,
                    false
                ),
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS')
            );

            return true;
        }

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smquotestxt&layout=default', false),
            Text::_('JLIB_APPLICATION_SAVE_SUCCESS')
        );

        return true;
    }

    public function getModel($name = 'Smquotetxt', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
