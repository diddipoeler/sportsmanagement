<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list controller for prediction games.
 */
final class PredictiongamesController extends SportsManagementAdminController
{
    public function getModel($name = 'Predictiongame', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
