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
     * Resolve the native agegroup table on the model's active database connection.
     * Preserve Joomla's factory for custom table names, prefixes and options.
     * @param string $name Requested table name, or empty for the default.
     * @param string $prefix Optional table prefix.
     * @param array $options Optional factory configuration.
     * @return mixed Native table or parent factory result.
     */
    public function getTable($name = '', $prefix = '', $options = [])
    {
        if (is_string($name) && ($name === '' || strcasecmp($name, 'Agegroup') === 0)
            && in_array($prefix, ['', 'Administrator', 'sportsmanagementTable'], true)
            && $options === []) {
            $table = new \Diddipoeler\Component\SportsManagement\Administrator\Table\AgegroupTable($this->getDatabase(), true);
            if (method_exists($this, 'shouldUseExceptions') && $this->shouldUseExceptions()) {
                $table->setUseExceptions(true);
            }
            return $table;
        }

        return parent::getTable($name, $prefix, $options);
    }

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

        if ($user === null || (int) $user->id <= 0) {
            $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

            return false;
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

            // Keep transaction and SQL diagnostics in the administrator log.
            \Joomla\CMS\Log\Log::add(
                'SportsManagement age-group save failed: ' . $e->getMessage(),
                \Joomla\CMS\Log\Log::ERROR,
                'com_sportsmanagement'
            );
            $message = Text::_('JERROR_AN_ERROR_HAS_OCCURRED');
            $this->setError($message);
            $app->enqueueMessage($message, 'error');

            return false;
        }
    }
}
