<?php
/**
 * Joomla 5/6 administrator prediction-games list controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list controller for prediction games.
 */
final class PredictiongamesController extends SportsManagementAdminController
{
    /**
     * Resolve the native administrator prediction game model for list actions.
     * Preserves supplied factory options while forcing request-independent
     * model loading so list filters do not leak into record operations.
     * @param string $name Requested model name.
     * @param string $prefix Joomla MVC namespace prefix.
     * @param array $config Additional model factory options.
     * @return mixed Native model or parent factory failure result.
     */
    public function getModel($name = 'Predictiongame', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
