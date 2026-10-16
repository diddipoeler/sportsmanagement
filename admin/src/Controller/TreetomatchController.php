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
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for tournament-tree match assignments. */
final class TreetomatchController extends SportsManagementFormController
{
    public function editlist(): void
    {
        $input = $this->app->getInput();
        $this->setRedirect(Route::_(
            'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist'
            . '&nid=' . $input->getInt('nid')
            . '&tid=' . $input->getInt('tid')
            . '&pid=' . $input->getInt('pid'),
            false
        ));
    }

    public function save_matcheslist(): void
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $input = $this->app->getInput();
        // Save targets must come from the CSRF-protected POST, never from
        // conflicting GET parameters. The model validates the raw IDs.
        $data = $input->post->getArray();
        $data['id'] = $data['nid'] ?? null;
        $nodeId = max(0, $input->post->getInt('nid', 0));
        $treeId = max(0, $input->post->getInt('tid', 0));
        $projectId = max(0, $input->post->getInt('pid', 0));
        $model = $this->getModel('Treetomatchs', 'Administrator', ['ignore_request' => false]);
        $ok = $model !== false && $model->store($data);

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist'
                . '&nid=' . $nodeId . '&tid=' . $treeId . '&pid=' . $projectId,
                false
            ),
            Text::_($ok
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_SAVED'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_ERROR_SAVE'
            ) . (!$ok && $model !== false ? $model->getError() : ''),
            $ok ? 'message' : 'error'
        );
    }

    public function getModel($name = 'Treetomatchs', $prefix = 'Administrator', $config = ['ignore_request' => false])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
