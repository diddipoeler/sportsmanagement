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
    /**
     * Load a native season model while preserving MVC factory configuration.
     * Forces ignore_request so writes are not populated from unrelated
     * list filters while keeping caller-provided options intact.
     * @param string $name Requested model name.
     * @param string $prefix Joomla administrator MVC prefix.
     * @param array $config Additional MVC factory options.
     * @return mixed Native season model result.
     */
    public function getModel($name = 'Season', $prefix = 'Administrator', $config = [])
    {
        // Retain caller options while keeping the model request-independent.
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Apply selected person assignments to the current season.
     * Checks edit permission, verifies the required Joomla model exists,
     * normalises request selection values and delegates assignment writes
     * to the season form model.
     * @return void
     * @throws \RuntimeException When the native model cannot be loaded.
     */
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

    /**
     * Apply selected team assignments to the current season.
     * Checks administrative edit rights and model availability before
     * delegating the POSTed season-team assignments to the native model.
     * @return void
     * @throws \RuntimeException When the native model is unavailable.
     */
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

    /**
     * Extract a scalar selection value from a legacy request field.
     * Accepts a direct nonarray input or searches an array for its first
     * scalar candidate; returns zero for arrays with no usable values.
     * @param mixed $value Request value or legacy list of values.
     * @return mixed First accepted scalar value, or zero.
     */
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
