<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list controller for prediction templates.
 */
final class PredictiontemplatesController extends SportsManagementAdminController
{
    public function getModel($name = 'Predictiontemplate', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
