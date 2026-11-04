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
     * Requires a Joomla form token and component core.admin rights, reads
     * the configured update URL, delegates download to the native model
     * and displays returned status messages before starting installation.
     * @return void
     * @throws \RuntimeException If the current identity lacks admin rights.
     */
    public function CopyGithubLink(): void
    {
        $this->checkToken();

        // Downloading and installing a package is an administrator-only task.
        // A valid form token alone does not grant extension installation rights.
        $identity = $this->app->getIdentity();

        if (!$identity || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Githubinstall', 'Administrator', ['ignore_request' => true]);
        $url = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_update_server_file', ''));
        $messages = $model !== false ? $model->CopyGithubLink($url) : false;

        if ($messages === false) {
            $this->setRedirect(
                Route::_('index.php?option=com_sportsmanagement&view=githubinstall', false),
                '',
                'error'
            );

            return;
        }

        foreach ($messages as $message) {
            if ((string) $message !== '') {
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
     * configuration, including the request-independent loading default.
     * @param string $name Requested model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Model factory options.
     * @return mixed Native model or the parent factory's failure result.
     */
    public function getModel($name = 'Githubinstall', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
