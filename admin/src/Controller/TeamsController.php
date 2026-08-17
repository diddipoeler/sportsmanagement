<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

final class TeamsController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $model = $this->getModel();
        $ok = $model->saveshort();
        $this->app->enqueueMessage($ok ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList();
    }

    public function copysave(): void
    {
        if (!$this->app->getIdentity()->authorise('core.create', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
        $model = $this->getModel();
        $ok = $model->copySelected();
        $this->app->enqueueMessage($ok ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList();
    }

    public function getModel($name = 'Team', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    private function redirectToList(): void
    {
        $clubId = $this->input->getInt('club_id');
        $url = 'index.php?option=com_sportsmanagement&view=teams' . ($clubId > 0 ? '&club_id=' . $clubId : '');
        $this->setRedirect(Route::_($url, false));
    }
}
