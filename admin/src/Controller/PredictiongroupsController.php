<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list controller for prediction groups.
 */
final class PredictiongroupsController extends SportsManagementAdminController
{
    public function getModel($name = 'Predictiongroup', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
