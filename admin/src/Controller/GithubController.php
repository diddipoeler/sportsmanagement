<?php
/**
 * Native Joomla 5/6 administrator controller for the GitHub helper view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native administrator controller for the GitHub helper view. */
final class GithubController extends BaseController
{
    public function addissue(): void
    {
        $this->checkToken();
        $identity = $this->app->getIdentity();

        if (!$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Github', 'Administrator', ['ignore_request' => true]);
        $success = $model !== false && $model->addissue();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=github&tmpl=component&layout=github_result',
            $success ? 'GitHub-Issue wurde erstellt.' : 'GitHub-Issue konnte nicht erstellt werden.',
            $success ? 'message' : 'error'
        );
    }

    public function cancel(): void
    {
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=close&tmpl=component',
            Text::_('JLIB_HTML_BEHAVIOR_CLOSE')
        );
    }

    public function getModel($name = 'Github', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config + ['ignore_request' => true]);
    }
}
