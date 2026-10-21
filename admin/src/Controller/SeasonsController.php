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

use Joomla\CMS\Router\Route;

final class SeasonsController extends SportsManagementAdminController
{
    public function getModel($name = 'Season', $prefix = 'Administrator', $config = [])
    {
        // Retain caller options while keeping the model request-independent.
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    public function applypersons(): void
    {
        $this->assertWritePermission();

        $post = $this->input->post->getArray();
        $model = $this->getModel();

        if ($model === false || $model === null) {
            throw new \RuntimeException('SportsManagement season model could not be loaded.', 500);
        }

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
        $this->assertWritePermission();

        $post = $this->input->post->getArray();
        $model = $this->getModel();

        if ($model === false || $model === null) {
            throw new \RuntimeException('SportsManagement season model could not be loaded.', 500);
        }

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
