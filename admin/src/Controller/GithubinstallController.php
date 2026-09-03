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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for the GitHub update download flow. */
final class GithubinstallController extends BaseController
{
    public function CopyGithubLink(): void
    {
        $this->checkToken();

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

    public function store(): void
    {
        $this->checkToken();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    public function getModel($name = 'Githubinstall', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
