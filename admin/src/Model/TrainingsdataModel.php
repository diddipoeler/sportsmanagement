<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Native Joomla 5/6 administrator form model for training data assignments.
 */
final class TrainingsdataModel extends SportsManagementAdminModel
{
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return Factory::getApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }
}
