<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Native Joomla 5/6 administrator model for an age group.
 */
class AgegroupModel extends SportsManagementAdminModel
{
    /**
     * Import the configured age groups for all configured sport types and countries.
     *
     * The import helpers are still legacy models; keeping that dependency local here
     * allows the Agegroup form model itself to use Joomla's namespaced MVC flow.
     */
    public function importAgeGroupFile(): void
    {
        $databaseTool = BaseDatabaseModel::getInstance('databasetool', 'sportsmanagementModel');
        $cpanelTool = BaseDatabaseModel::getInstance('cpanel', 'sportsmanagementModel');
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $sportTypes = (array) $params->get('cfg_sport_types', []);
        $countries = (array) $params->get('cfg_country_associations', []);

        foreach ($sportTypes as $type) {
            $cpanelTool->checksporttype($type);
            $sportTypeId = $databaseTool->insertSportType($type);

            foreach ($countries as $country) {
                $databaseTool->insertAgegroup($country, $sportTypeId);
            }
        }
    }

    /**
     * Save the short values of selected age groups from the list view.
     *
     * @return string|false Status text on success, false on storage failure.
     */
    public function saveshort()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $date = Factory::getDate();
        $user = $app->getIdentity();
        $pks = $input->post->get('cid', [], 'array');
        $post = $input->post->getArray();
        $pks = array_values(array_filter(array_map('intval', (array) $pks), static fn (int $id): bool => $id > 0));

        if (!$pks) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE_NO_SELECT');
        }

        foreach ($pks as $pk) {
            $table = $this->getTable();

            if (!$table->load($pk)) {
                $app->enqueueMessage((string) $table->getError(), 'error');

                return false;
            }

            $table->name = trim((string) ($post['name' . $pk] ?? $table->name));
            $table->alias = OutputFilter::stringURLSafe($table->name);
            $table->modified = $date->toSql();
            $table->modified_by = (int) $user->id;

            if (!$table->store()) {
                $app->enqueueMessage((string) $table->getError(), 'error');

                return false;
            }
        }

        return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE');
    }
}
