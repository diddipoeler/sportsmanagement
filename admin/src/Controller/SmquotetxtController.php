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
    /**
     * Cancel the quote text editor without persisting changes.
     *
     * Returns the administrator to the list of editable quote files,
     * using Joomla's routed component URL.
     *
     * @param string|null $key Optional legacy record identifier.
     * @return bool True after setting the redirect.
     */
    public function cancel($key = null)
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smquotestxt&layout=default', false)
        );

        return true;
    }

    /**
     * Save an administrator-edited random-quote module source file.
     *
     * Requires a valid Joomla form token and component core.admin
     * permission because the operation can write executable PHP.
     * Forwards posted form data to the model, encodes the filename in
     * redirects and distinguishes Apply, Save and failed operations.
     *
     * @param string|null $key Optional form record identifier.
     * @param string|null $urlVar Optional URL record identifier.
     * @return bool Whether the module file was saved successfully.
     * @throws \RuntimeException If administrator permission is missing.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        // The editor can modify module PHP files: a CSRF token alone is
        // insufficient to authorize writes to executable source files.
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $data = $this->app->getInput()->post->get('jform', [], 'array');
        $data = is_array($data) ? $data : [];
        $model = $this->getModel();
        // Joomla may return false or null when a model cannot be resolved.
        $success = is_object($model) && $model->save($data);
        $task = $this->getTask();
        $fileName = rawurlencode(is_string($data['filename'] ?? null) ? $data['filename'] : '');

        if (!$success) {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=smquotetxt&layout=default&file_name=' . $fileName,
                    false
                ),
                is_object($model)
                    ? ($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'))
                    : Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'),
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

    /**
     * Retrieve the native Joomla administrator quote editor model.
     *
     * Passes the requested model name, namespace and caller options to
     * Joomla's MVC factory without discarding model configuration.
     *
     * @param string $name Requested editor model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Additional factory settings.
     * @return mixed MVC model or the parent's failure result.
     */
    public function getModel($name = 'Smquotetxt', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
