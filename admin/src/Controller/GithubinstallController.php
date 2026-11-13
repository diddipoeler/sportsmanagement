<?php
/**
 * Native Joomla 5/6 controller for the GitHub update download flow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for the GitHub update download flow. */
final class GithubinstallController extends BaseController
{
    /**
     * Download and install an update package using the administrator model.
     * Requires a valid Joomla form token, a logged-in administrator with
     * component core.admin rights and a bounded string update URL. Delegates
     * the validated setting to the native model and displays its messages
     * before continuing into the established Joomla installer workflow.
     * Missing identities, guests and malformed URL configuration never
     * trigger an archive download or package installation. The Joomla MVC
     * model must expose both installation actions and return a list of
     * scalar messages before the installation is attempted.
     * @return void
     * @throws \RuntimeException If the current identity lacks admin rights.
     */
    public function CopyGithubLink(): void
    {
        $this->checkToken();

        // Downloading and installing a package is an administrator-only task.
        // A valid form token alone does not grant extension installation rights.
        $identity = $this->app->getIdentity();

        if ($identity === null || (int) ($identity->id ?? 0) < 1
            || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Githubinstall', 'Administrator', ['ignore_request' => true]);
        $configuredUrl = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_update_server_file', '');
        $url = is_string($configuredUrl) && strlen($configuredUrl) <= 2048
            ? trim($configuredUrl)
            : '';
        if (!is_object($model)
            || !is_callable([$model, 'CopyGithubLink'])
            || !is_callable([$model, 'installfolder'])) {
            throw new \RuntimeException('SportsManagement GitHub installation model is unavailable.', 500);
        }

        $messages = $model->CopyGithubLink($url);

        if (!is_array($messages)) {
            $this->setRedirect(
                Route::_('index.php?option=com_sportsmanagement&view=githubinstall', false),
                '',
                'error'
            );

            return;
        }

        foreach ($messages as $message) {
            if (is_scalar($message) && (string) $message !== '') {
                $this->app->enqueueMessage((string) $message, 'notice');
            }
        }

        $model->installfolder();
    }

    /**
     * Complete the GitHub installation helper and close its popup.
     * Validates the Joomla form token and redirects to the component's
     * close view without downloading or installing a package.
     * @return void
     */
    public function store(): void
    {
        $this->checkToken();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Resolve the GitHub installation model through Joomla MVC.
     * Uses the administrator namespace and forwards the caller's
     * configuration while always enabling request-independent model loading.
     * @param string $name Requested model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Model factory options.
     * @return mixed Native model or the parent factory's failure result.
     */
    public function getModel($name = 'Githubinstall', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
