<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for tournament-tree match assignments. */
final class TreetomatchController extends SportsManagementFormController
{
    public function save_matcheslist(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $nodeId = $input->post->getInt('nid') ?: $input->getInt('nid');
        $treeId = $input->post->getInt('tid') ?: $input->getInt('tid');
        $projectId = $input->post->getInt('pid') ?: $input->getInt('pid');
        $data = $input->post->getArray();
        $data['id'] = $nodeId;
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
