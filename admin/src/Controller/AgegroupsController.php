<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * List controller for age groups.
 */
class AgegroupsController extends \JSMControllerAdmin
{
    /**
     * Save the short values of the selected age groups.
     */
    public function saveshort(): void
    {
        $model = $this->getModel();
        $message = $model->saveshort();

        $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $message);
    }

    /**
     * Import an age-group file through the existing model implementation.
     */
    public function import(): void
    {
        $model = $this->getModel();
        $message = $model->importAgeGroupFile();

        $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $message);
    }

    /**
     * Proxy for the legacy Agegroup model while models are migrated separately.
     */
    public function getModel($name = 'agegroup', $prefix = 'sportsmanagementModel', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
