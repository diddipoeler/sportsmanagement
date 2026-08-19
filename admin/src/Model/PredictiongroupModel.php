<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for prediction groups.
 */
final class PredictiongroupModel extends SportsManagementAdminModel
{
    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            Factory::getApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }
    }
}
