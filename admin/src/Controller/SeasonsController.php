<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

final class SeasonsController extends SportsManagementAdminController
{
    public function getModel($name = 'Season', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    public function applypersons(): void
    {
        $post = $this->input->post->getArray();
        $model = $this->getModel();
        $message = (string) $model->saveshortpersons();

        $url = 'index.php?option=com_sportsmanagement'
            . '&tmpl=component'
            . '&view=players'
            . '&layout=assignpersons'
            . '&season_id=' . (int) ($post['season_id'] ?? 0)
            . '&team_id=' . (int) $this->scalarInput($post['team_id'] ?? 0)
            . '&persontype=' . (int) ($post['persontype'] ?? 0)
            . '&whichview=' . rawurlencode((string) ($post['whichview'] ?? ''));

        $this->setRedirect(Route::_($url, false), $message);
    }

    public function applyteams(): void
    {
        $post = $this->input->post->getArray();
        $model = $this->getModel();
        $model->saveshortteams();

        $url = 'index.php?option=com_sportsmanagement'
            . '&tmpl=component'
            . '&view=teams'
            . '&layout=assignteams'
            . '&season_id=' . (int) ($post['season_id'] ?? 0);

        $this->setRedirect(Route::_($url, false));
    }

    private function scalarInput($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $candidate) {
            if (is_scalar($candidate)) {
                return $candidate;
            }
        }

        return 0;
    }
}
