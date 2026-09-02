<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator model for an age group.
 */
class AgegroupModel extends SportsManagementAdminModel
{
    /**
     * Import the configured age groups for all configured sport types and countries.
     */
    public function importAgeGroupFile(): void
    {
        $factory = $this->getMVCFactory();
        $databaseTool = $factory->createModel(
            'Databasetool',
            'Administrator',
            ['ignore_request' => true]
        );
        $cpanelTool = $factory->createModel(
            'Cpanel',
            'Administrator',
            ['ignore_request' => true]
        );

        if (!$databaseTool instanceof DatabasetoolModel || !$cpanelTool instanceof CpanelModel) {
            $this->administratorApplication()->enqueueMessage(
                Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'),
                'error'
            );

            return;
        }

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
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $date = Factory::getDate();
        $user = $app->getIdentity();
        $pks = $input->post->get('cid', [], 'array');
        $post = $input->post->getArray();
        $pks = array_values(array_filter(array_map('intval', (array) $pks), static fn (int $id): bool => $id > 0));

        if (!$pks) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE_NO_SELECT');
        }

        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            foreach ($pks as $pk) {
                $table = $this->getTable();

                if (!$table->load($pk)) {
                    throw new \RuntimeException((string) $table->getError());
                }

                $table->name = trim((string) ($post['name' . $pk] ?? $table->name));
                $table->alias = OutputFilter::stringURLSafe($table->name);
                $table->modified = $date->toSql();
                $table->modified_by = (int) $user->id;

                if (!$table->store()) {
                    throw new \RuntimeException((string) $table->getError());
                }
            }

            $db->transactionCommit();

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE');
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original storage error.
                }
            }

            $this->setError($e->getMessage());
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }
}
