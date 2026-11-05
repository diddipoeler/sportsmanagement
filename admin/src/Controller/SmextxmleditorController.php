<?php
/**
 * Native Joomla 5/6 controller for the SportsManagement extended XML/PHP editor.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 controller for the SportsManagement extended XML/PHP editor.
 */
final class SmextxmleditorController extends FormController
{
    /**
     * Cancel the extended source editor without saving changes.
     *
     * Redirects to the administrator's XML/PHP editor overview using
     * Joomla's route builder, preserving the historic form workflow.
     *
     * @param string|null $key Optional legacy record key.
     * @return bool True once the redirect has been configured.
     */
    public function cancel($key = null)
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', false)
        );

        return true;
    }

    /**
     * Save extended XML/PHP source edited by a component administrator.
     *
     * Requires Joomla's form token and component core.admin permission
     * because this task can write executable PHP files. Resolves the
     * native editor model, reports persistence failures and redirects
     * to the editor after Apply or the overview after a normal Save.
     *
     * @param string|null $key Optional form record identifier.
     * @param string|null $urlVar Optional URL identifier field.
     * @return bool Whether the native model saved the submitted content.
     * @throws \RuntimeException If access is denied or the model is missing.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $data = $this->app->getInput()->post->get('jform', [], 'array');
        $model = $this->getModel();

        if (!$model instanceof SmextxmleditorModel) {
            throw new \RuntimeException('Extended source editor model is unavailable.', 500);
        }

        $success = $model->save($data);
        $task = $this->getTask();
        $fileName = rawurlencode(is_string($data['filename'] ?? null) ? $data['filename'] : '');

        if (!$success) {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default&file_name=' . $fileName,
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
                    'index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default&file_name=' . $fileName,
                    false
                ),
                Text::_('JLIB_APPLICATION_SAVE_SUCCESS')
            );

            return true;
        }

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', false),
            Text::_('JLIB_APPLICATION_SAVE_SUCCESS')
        );

        return true;
    }

    /**
     * Retrieve the native Joomla 5/6 extended source editor model.
     *
     * Forwards the requested administrator model name and all supplied
     * factory options to the parent Joomla MVC controller.
     *
     * @param string $name Requested editor model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Optional MVC model factory configuration.
     * @return mixed Joomla model or the parent's failure result.
     */
    public function getModel($name = 'Smextxmleditor', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
