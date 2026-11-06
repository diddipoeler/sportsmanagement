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
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator model for an age group.
 */
class AgegroupModel extends SportsManagementAdminModel
{
    /**
     * Import configured age groups for the selected sports and countries.
     *
     * Resolves the native database and control-panel models before
     * inserting the configured sport and age-group mappings.
     *
     * @return void
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
     * Save selected age group names and aliases from the compact list.
     *
     * Loads selected IDs from Joomla POST, regenerates URL-safe aliases,
     * updates audit columns and rolls back on failed table writes.
     *
     * @return string|false Translated success result or false on failure.
     */
    public function saveshort()
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $modified = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $user = $app->getIdentity();
        $pks = $input->post->get('cid', [], 'array');
        $post = $input->post->getArray();
        $selected = is_array($pks) ? $pks : [];
        $pks = [];

        foreach (array_slice($selected, 0, 500) as $raw) {
            if (!is_string($raw) && !is_int($raw)) {
                continue;
            }

            $value = trim((string) $raw);

            if (strlen($value) > 20 || preg_match('/^[0-9]+$/D', $value) !== 1) {
                continue;
            }

            $digits = ltrim($value, '0');
            $id = $digits !== ''
                ? filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;

            if ($id !== false) {
                $pks[$id] = $id;
            }
        }

        $pks = array_values($pks);

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
                $table->modified = $modified;
                $table->modified_by = (int) ($user?->id ?? 0);

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
