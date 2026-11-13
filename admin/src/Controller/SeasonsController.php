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
     * to the season form model. Invalid structured identifiers and view
     * selectors are replaced with safe redirect defaults.
     * @return void
     * @throws \RuntimeException When the native model cannot be loaded.
     */
    public function applypersons(): void
    {
        $this->checkToken();
        $this->assertWritePermission();

        $post = $this->input->post->getArray();
        $model = $this->getModel();

        if (!is_object($model)) {
            throw new \RuntimeException('SportsManagement season model could not be loaded.', 500);
        }

        $message = (string) $model->saveshortpersons();

        $url = 'index.php?option=com_sportsmanagement'
            . '&tmpl=component'
            . '&view=players'
            . '&layout=assignpersons'
            . '&season_id=' . $this->positiveInputId($post['season_id'] ?? 0)
            . '&team_id=' . $this->positiveInputId($this->scalarInput($post['team_id'] ?? 0))
            . '&persontype=' . $this->positiveInputId($post['persontype'] ?? 0)
            . '&whichview=' . rawurlencode(is_string($post['whichview'] ?? null) ? $post['whichview'] : '');

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
        $this->checkToken();
        $this->assertWritePermission();

        $post = $this->input->post->getArray();
        $model = $this->getModel();

        if (!is_object($model)) {
            throw new \RuntimeException('SportsManagement season model could not be loaded.', 500);
        }

        $model->saveshortteams();

        $url = 'index.php?option=com_sportsmanagement'
            . '&tmpl=component'
            . '&view=teams'
            . '&layout=assignteams'
            . '&season_id=' . $this->positiveInputId($post['season_id'] ?? 0);

        $this->setRedirect(Route::_($url, false));
    }

    /**
     * Return a positive numeric Joomla form identifier or zero when invalid.
     *
     * Rejects arrays, signed values, partial numerics and integer overflows
     * rather than silently coercing malformed POST data into a redirect ID.
     *
     * @param mixed $value Raw form identifier.
     * @return int Positive record identifier, or zero if invalid.
     */
    private function positiveInputId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
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
