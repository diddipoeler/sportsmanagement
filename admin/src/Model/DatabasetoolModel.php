<?php
/**
 * Native Joomla 5/6 database maintenance and bootstrap-data model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 database maintenance and bootstrap-data model.
 *
 * Public method/property names intentionally preserve the historic API because
 * the control panel and the JoomLeague import code still call this model.
 */
final class DatabasetoolModel extends BaseDatabaseModel
{
    public static int $db_num_rows = 0;
    public static array $jsmtables = [];
    public static int $bar_value = 0;

    public array $_sport_types_events = [];
    public array $_sport_types_position = [];
    public array $_sport_types_position_parent = [];
    public array $_assoclist = [];
    public string $_success_text = '';
    public string $my_text = '';
    public string $storeFailedColor = 'red';
    public string $storeSuccessColor = 'green';
    public string $existingInDbColor = 'orange';

    /**
     * Normalise a database error code and message for legacy consumers.
     * Recognises MySQL duplicate-column and duplicate-key errors so maintenance actions can distinguish warnings from failures.
     *
     * @param mixed $getcode Parameter used by the native getExeptionMessage() operation.
     * @param mixed $getmessage Parameter used by the native getExeptionMessage() operation.
     * @return array Result of the getExeptionMessage() operation.
     */
    public static function getExeptionMessage($getcode = '', $getmessage = ''): array
    {
        $code = (int) $getcode;
        $message = trim((string) $getmessage);

        if (preg_match('/(?:SQLSTATE\[[^\]]+\].*?)(?:1060|1062)|(?:^|\D)(1060|1062)(?:\D|$)/', $message, $match)) {
            if (str_contains($message, '1060')) {
                $code = 1060;
            } elseif (str_contains($message, '1062')) {
                $code = 1062;
            }
        }

        return [
            'code' => $code,
            'message' => $message,
            'log' => $code === 1060 ? Log::NOTICE : Log::ERROR,
            'error' => $code === 1060 ? 0 : 1,
        ];
    }

    /**
     * Capture a high-resolution timestamp for database maintenance timing.
     * Returns PHP microtime as a float for elapsed-time calculations.
     *
     * @return float Result of the getRunTime() operation.
     */
    public static function getRunTime(): float
    {
        return microtime(true);
    }

    /** The historic form no longer has a matching XML definition. */
    public function getForm($data = [], $loadData = true)
    {
        return false;
    }

    /**
     * Create start, end and delta memory statistics for the admin interface.
     * Produces legacy display rows containing the measurement label and byte count.
     *
     * @param mixed $startmemory Parameter used by the native getMemory() operation.
     * @param mixed $endmemory Parameter used by the native getMemory() operation.
     * @return array Result of the getMemory() operation.
     */
    public function getMemory($startmemory, $endmemory): array
    {
        $rows = [];

        foreach ([
            ['start', (int) $startmemory],
            ['ende', (int) $endmemory],
            ['verbrauch', (int) $endmemory - (int) $startmemory],
        ] as [$name, $memory]) {
            $row = new \stdClass();
            $row->name = $name;
            $row->memory = $memory;
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Calculate elapsed query time from scalar or legacy microtime values.
     * Accepts numeric timestamps or space-separated microtime strings and rounds the duration to milliseconds.
     *
     * @param mixed $starttime Parameter used by the native getQueryTime() operation.
     * @param mixed $endtime Parameter used by the native getQueryTime() operation.
     * @return float Result of the getQueryTime() operation.
     */
    public function getQueryTime($starttime, $endtime): float
    {
        if (is_numeric($starttime) && is_numeric($endtime)) {
            return round((float) $endtime - (float) $starttime, 3);
        }

        $start = array_map('floatval', preg_split('/\s+/', trim((string) $starttime)) ?: []);
        $end = array_map('floatval', preg_split('/\s+/', trim((string) $endtime)) ?: []);

        return round(($end[0] ?? 0.0) - ($start[0] ?? 0.0) + ($end[1] ?? 0.0) - ($start[1] ?? 0.0), 3);
    }

    /**
     * Enumerate tables owned by the SportsManagement component.
     * Restricts discovery to the configured Joomla database prefix and component-specific table names.
     *
     * @return array Result of the getSportsManagementTables() operation.
     */
    public function getSportsManagementTables(): array
    {
        self::$jsmtables = $this->componentTables('sportsmanagement_');

        return self::$jsmtables;
    }

    /**
     * Enumerate legacy JoomLeague tables eligible for controlled cleanup.
     * Uses prefix-matched table discovery instead of accepting arbitrary database table names.
     *
     * @return array Result of the getJoomleagueTablesTruncate() operation.
     */
    public function getJoomleagueTablesTruncate(): array
    {
        self::$jsmtables = $this->componentTables('joomleague_');

        return self::$jsmtables;
    }

    /**
     * Validate the discovered JoomLeague tables against supported import mappings.
     * Checks source/destination tables and builds a status list for the administrator migration interface.
     *
     * @param mixed $tables Parameter used by the native checkImportTablesJlJsm() operation.
     * @return array Result of the checkImportTablesJlJsm() operation.
     */
    public function checkImportTablesJlJsm($tables): array
    {
        $db = self::sportsDatabase();
        $available = array_flip($db->getTableList());
        $prefix = (string) $db->getPrefix();
        $rows = [];
        $newStructure = ['project_team', 'team_player', 'team_staff'];
        $supported = [
            'match', 'club', 'league', 'person', 'playground', 'project', 'round', 'season', 'team',
            'match_commentary', 'match_player', 'match_statistic', 'prediction_groups', 'prediction_member',
            'template_config',
        ];

        foreach ((array) $tables as $value) {
            if (!is_object($value) || empty($value->name)) {
                continue;
            }

            $jlTable = (string) $value->name;
            $jsmTable = str_replace('joomleague', 'sportsmanagement', $jlTable);

            if (!isset($available[$jsmTable])) {
                continue;
            }

            $short = str_starts_with($jlTable, $prefix . 'joomleague_')
                ? substr($jlTable, strlen($prefix . 'joomleague_'))
                : preg_replace('/^.*?joomleague_/', '', $jlTable);

            $row = new \stdClass();
            $row->id = $value->id ?? 0;
            $row->jl = $jlTable;
            $row->jsm = $jsmTable;
            $row->import = $value->import ?? 0;
            $row->import_data = $value->import_data ?? 0;
            $row->checked_out = $value->checked_out ?? 0;

            if (in_array($short, $newStructure, true)) {
                $row->info = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NEW_STRUCTUR');
                $row->color = $this->existingInDbColor;
            } elseif (in_array($short, $supported, true)) {
                $row->info = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_OK');
                $row->color = $this->storeSuccessColor;
            } else {
                $row->info = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NOT_IMPORT');
                $row->color = $this->storeFailedColor;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Retrieve legacy import table mappings from the component's registry.
     * Uses the native query builder and shared error-tolerant object-list loader.
     *
     * @return array Result of the getJoomleagueImportTables() operation.
     */
    public function getJoomleagueImportTables(): array
    {
        $db = self::sportsDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_jl_tables'));

        return $this->loadObjectList($db, $query);
    }

    /**
     * Scan the Joomla database for legacy JoomLeague tables.
     * Sorts discovered tables and compares them with the recorded import mappings.
     *
     * @return array Result of the getJoomleagueTables() operation.
     */
    public function getJoomleagueTables(): array
    {
        $db = self::sportsDatabase();
        $prefix = (string) $db->getPrefix();
        $tables = array_values(array_filter(
            $db->getTableList(),
            static fn (string $table): bool => str_starts_with($table, $prefix . 'joomleague_')
        ));
        sort($tables, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($tables as $table) {
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_jl_tables'))
                ->where($db->quoteName('name') . ' = :joomleagueTableName')
                ->bind(':joomleagueTableName', $table, ParameterType::STRING);
            $db->setQuery($query, 0, 1);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            $record = (object) ['name' => $table, 'import' => 0, 'import_data' => 0];

            try {
                $db->insertObject('#__sportsmanagement_jl_tables', $record);
            } catch (\Throwable $e) {
                self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $tables;
    }

    /**
     * Convert imported legacy template parameters to Joomla-compatible JSON.
     * Examines migrated template configuration rows and normalises nonempty parameter data.
     *
     * @return void Result of the setParamstoJSON() operation.
     */
    public function setParamstoJSON(): void
    {
        $db = self::sportsDatabase();
        $emptyParams = '';
        $query = $db->createQuery()
            ->select([$db->quoteName('template'), $db->quoteName('params')])
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('params') . ' <> :emptyParams')
            ->where($db->quoteName('import_id') . ' <> 0')
            ->bind(':emptyParams', $emptyParams, ParameterType::STRING)
            ->group([$db->quoteName('template'), $db->quoteName('params')]);

        foreach ($this->loadObjectList($db, $query) as $row) {
            $template = basename((string) $row->template);

            if ($template === '' || $template !== (string) $row->template) {
                continue;
            }

            $parameter = new Registry();
            $legacy = explode("\n", (string) $row->params, 2)[0];
            $parameter->loadString($legacy);
            $values = $parameter->toArray();
            $xmlFile = JPATH_COMPONENT_SITE . '/settings/default/' . $template . '.xml';
            $xml = $this->loadXml($xmlFile);

            if ($xml !== null) {
                $values = [];

                foreach ($xml->fieldset as $fieldset) {
                    foreach ($fieldset->field as $field) {
                        $values[(string) $field['name']] = (string) $field['default'];
                    }
                }
            }

            $encodedValues = (string) json_encode($values);
            $update = $db->createQuery()
                ->update($db->quoteName('#__sportsmanagement_template_config'))
                ->set($db->quoteName('params') . ' = :templateParams')
                ->where($db->quoteName('template') . ' = :templateName')
                ->bind(':templateParams', $encodedValues, ParameterType::STRING)
                ->bind(':templateName', $template, ParameterType::STRING);
            $db->setQuery($update);
            self::runJoomlaQuery(self::class, $db);
        }
    }

    /**
     * Execute a previously prepared database statement and record affected rows.
     * Updates legacy static row-count properties and reports SQL failures via the Joomla administrator application.
     *
     * @param mixed $setModelVar Parameter used by the native runJoomlaQuery() operation.
     * @param mixed $db Parameter used by the native runJoomlaQuery() operation.
     * @return bool Result of the runJoomlaQuery() operation.
     */
    public static function runJoomlaQuery($setModelVar = '', $db = null): bool
    {
        $database = $db instanceof DatabaseInterface ? $db : self::sportsDatabase();

        try {
            $database->execute();
            $affected = (int) $database->getAffectedRows();
            self::$db_num_rows = $affected;

            if (is_string($setModelVar) && $setModelVar !== '' && class_exists($setModelVar)
                && property_exists($setModelVar, 'db_num_rows')) {
                $setModelVar::$db_num_rows = $affected;
            }

            return true;
        } catch (\Throwable $e) {
            self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Replace obsolete JoomLeague translation prefixes in migrated data.
     * Updates only predefined component tables and fields using the shared string replacement routine.
     *
     * @return void Result of the setNewComponentName() operation.
     */
    public function setNewComponentName(): void
    {
        foreach ([
            ['#__sportsmanagement_template_config', 'title', 'COM_JOOMLEAGUE', 'COM_SPORTSMANAGEMENT'],
            ['#__sportsmanagement_rosterposition', 'extended', 'COM_JOOMLEAGUE', 'COM_SPORTSMANAGEMENT'],
            ['#__sportsmanagement_rosterposition', 'extended', 'JL_EXT', 'COM_SPORTSMANAGEMENT_EXT'],
            ['#__sportsmanagement_eventtype', 'name', 'COM_JOOMLEAGUE', 'COM_SPORTSMANAGEMENT'],
        ] as [$table, $field, $search, $replace]) {
            $this->replaceFieldValue($table, $field, $search, $replace);
        }
    }

    /**
     * Rewrite historical media paths for supported component records.
     * Applies the known Joomla 5/6 image path substitutions to a fixed list of picture and icon columns.
     *
     * @return void Result of the setNewPicturePath() operation.
     */
    public function setNewPicturePath(): void
    {
        foreach ([
            ['#__sportsmanagement_person', 'picture'],
            ['#__sportsmanagement_playground', 'picture'],
            ['#__sportsmanagement_team', 'picture'],
            ['#__sportsmanagement_club', 'logo_big'],
            ['#__sportsmanagement_club', 'logo_middle'],
            ['#__sportsmanagement_club', 'logo_small'],
            ['#__sportsmanagement_associations', 'picture'],
            ['#__sportsmanagement_associations', 'assocflag'],
            ['#__sportsmanagement_eventtype', 'icon'],
            ['#__sportsmanagement_project_team', 'picture'],
            ['#__sportsmanagement_project_team', 'trikot_home'],
            ['#__sportsmanagement_project_team', 'trikot_away'],
            ['#__sportsmanagement_season_team_person_id', 'picture'],
            ['#__sportsmanagement_season_person_id', 'picture'],
        ] as [$table, $field]) {
            $this->replaceFieldValue($table, $field, 'com_joomleague', 'com_sportsmanagement');
        }
    }

    /**
     * Run a controlled maintenance operation on one component database table.
     * Accepts only REPAIR, OPTIMIZE or TRUNCATE and validates the table against the selected Joomla database prefix and actual tables.
     *
     * @param mixed $table Parameter used by the native setSportsManagementTableQuery() operation.
     * @param mixed $command Parameter used by the native setSportsManagementTableQuery() operation.
     * @return bool Result of the setSportsManagementTableQuery() operation.
     */
    public function setSportsManagementTableQuery($table, $command): bool
    {
        $db = self::sportsDatabase();
        $action = strtoupper(trim((string) $command));

        if (!in_array($action, ['REPAIR', 'OPTIMIZE', 'TRUNCATE'], true)) {
            return false;
        }

        $tableName = (string) $table;
        $prefix = (string) $db->getPrefix();
        $allowedPrefix = str_starts_with($tableName, $prefix . 'sportsmanagement_')
            || str_starts_with($tableName, $prefix . 'joomleague_');

        if (!$allowedPrefix || !in_array($tableName, $db->getTableList(), true)) {
            return false;
        }

        $db->setQuery($action . ' TABLE ' . $db->quoteName($tableName));

        return self::runJoomlaQuery(self::class, $db);
    }

    /**
     * Validate configured quote imports and load permitted XML quote sources.
     * Normalises quote type tokens, restricts file locations and builds status output for the administration page.
     *
     * @param mixed $sm_quotes Parameter used by the native checkQuotes() operation.
     * @return string Result of the checkQuotes() operation.
     */
    public function checkQuotes($sm_quotes): string
    {
        $db = self::sportsDatabase();
        $this->my_text = '';

        foreach ((array) $sm_quotes as $definition) {
            [$type, $daily] = array_pad(array_map('trim', explode(',', (string) $definition, 2)), 2, '');
            $type = preg_replace('/[^A-Za-z0-9_-]/', '', $type) ?? '';
            $dailyNumber = (int) $daily;

            if ($type === '' || $dailyNumber <= 0) {
                continue;
            }

            $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/xml_files/quote_' . $type . '.xml';
            $xml = $this->loadXml($file);

            if ($xml === null) {
                continue;
            }

            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_rquote'))
                ->where($db->quoteName('daily_number') . ' = :quoteDailyNumber')
                ->bind(':quoteDailyNumber', $dailyNumber, ParameterType::INTEGER);
            $db->setQuery($query);
            $exists = (int) $db->loadResult() > 0;
            $version = (string) ($xml->version ?? '');

            if ($exists) {
                $this->my_text .= '<span style="color:' . $this->existingInDbColor . '"><strong>'
                    . Text::_('Installierte Zitate') . '</strong></span><br />';
                $this->my_text .= Text::_('Zitate ' . $type . ' Version : ' . $version . ' ist installiert !') . '<br />';
                continue;
            }

            $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                . Text::_('Installiere Zitate') . '</strong></span><br />';
            $this->my_text .= Text::_('Zitate ' . $type . ' Version : ' . $version . ' wird installiert !') . '<br />';

            foreach ($xml->children() as $node) {
                if (!isset($node->quote)) {
                    continue;
                }

                $quote = trim((string) $node->quote);

                if ($quote === '') {
                    continue;
                }

                $attrs = $node->quote->attributes();
                $record = (object) [
                    'daily_number' => isset($attrs['daily_number']) ? (int) $attrs['daily_number'] : $dailyNumber,
                    'author' => isset($attrs['author']) ? (string) $attrs['author'] : '',
                    'quote' => $quote,
                    'notes' => isset($attrs['notes']) ? (string) $attrs['notes'] : '',
                ];

                try {
                    $db->insertObject('#__sportsmanagement_rquote', $record);
                } catch (\Throwable $e) {
                    self::writeErrorLog(self::class, __FUNCTION__, __FILE__, $e->getMessage(), __LINE__);
                }
            }
        }

        return $this->my_text;
    }

    /**
     * Write a structured database tool error into the Joomla log.
     * Includes the reporting class, function, source filename and line while ignoring empty error descriptions.
     *
     * @param mixed $class Parameter used by the native writeErrorLog() operation.
     * @param mixed $function Parameter used by the native writeErrorLog() operation.
     * @param mixed $file Parameter used by the native writeErrorLog() operation.
     * @param mixed $text Parameter used by the native writeErrorLog() operation.
     * @param mixed $line Parameter used by the native writeErrorLog() operation.
     * @return void Result of the writeErrorLog() operation.
     */
    public static function writeErrorLog($class, $function, $file, $text, $line): void
    {
        if ((string) $text === '') {
            return;
        }

        Log::add(
            sprintf('%s::%s (%s:%d) %s', (string) $class, (string) $function, (string) $file, (int) $line, (string) $text),
            Log::ERROR,
            'jsmerror'
        );
    }

    /**
     * Import standard age groups for the selected nation and sport.
     * Sanitises the country token and uses parameter-bound sport-category lookups to determine required defaults.
     *
     * @param mixed $search_nation Parameter used by the native insertAgegroup() operation.
     * @param mixed $filter_sports_type Parameter used by the native insertAgegroup() operation.
     * @return string Result of the insertAgegroup() operation.
     */
    public function insertAgegroup($search_nation, $filter_sports_type): string
    {
        $db = self::sportsDatabase();
        $country = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $search_nation) ?? '');
        $sportTypeId = (int) $filter_sports_type;
        $query = $db->createQuery()
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->where($db->quoteName('id') . ' = :agegroupSportTypeId')
            ->bind(':agegroupSportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $sportTypeName = (string) $db->loadResult();
        $parts = explode('_', $sportTypeName);
        $sportToken = strtolower((string) array_pop($parts));
        $filename = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/xml_files/agegroup_'
            . strtolower($country) . '_' . $sportToken . '.xml';
        $xml = $this->loadXml($filename);

        if ($country === '' || $sportTypeId <= 0 || $xml === null) {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>'
                . Text::_('Fehlende Altersgruppen') . '</strong></span><br />'
                . Text::sprintf('Die Datei %1$s ist nicht vorhanden!', basename($filename)) . '<br />';

            return $this->my_text;
        }

        $this->my_text = '<span style="color:' . $this->existingInDbColor . '"><strong>'
            . Text::_('Installierte Altersgruppen') . '</strong></span><br />'
            . Text::sprintf('Die Datei %1$s ist vorhanden!', basename($filename)) . '<br />';

        foreach ($xml->agegroups as $node) {
            $name = trim((string) $node->agegroup);

            if ($name === '') {
                continue;
            }

            $attrs = $node->agegroup->attributes();
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_agegroup'))
                ->where($db->quoteName('name') . ' = :agegroupName')
                ->where($db->quoteName('country') . ' = :agegroupCountry')
                ->where($db->quoteName('sportstype_id') . ' = :agegroupSportstypeId')
                ->bind(':agegroupName', $name, ParameterType::STRING)
                ->bind(':agegroupCountry', $country, ParameterType::STRING)
                ->bind(':agegroupSportstypeId', $sportTypeId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            $record = (object) [
                'name' => $name,
                'picture' => 'images/com_sportsmanagement/database/agegroups/' . basename((string) ($attrs['picture'] ?? '')),
                'info' => (string) ($attrs['info'] ?? ''),
                'sportstype_id' => $sportTypeId,
                'country' => $country,
            ];

            try {
                $db->insertObject('#__sportsmanagement_agegroup', $record);
                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                    . Text::_('Installierte Altersgruppen') . '</strong></span><br />'
                    . Text::sprintf('Die Altersgruppe %1$s wurde angelegt!!', $name) . '<br />';
            } catch (\Throwable $e) {
                self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $this->my_text;
    }

    /**
     * Verify or populate country associations configured for the component.
     * Normalises the configured country list and uses existing SportsManagement records to determine missing associations.
     *
     * @return bool Result of the checkAssociations() operation.
     */
    public function checkAssociations(): bool
    {
        $db = self::sportsDatabase();
        $app = self::resolveAdministratorApplication();
        $configured = (array) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_country_associations', []);
        $countries = [];

        foreach ($configured as $country) {
            $token = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $country) ?? '');

            if ($token !== '') {
                $countries[] = $token;
            }
        }

        $countries = array_values(array_unique($countries));

        if (!$countries) {
            return true;
        }

        $query = $db->createQuery()
            ->delete($db->quoteName('#__sportsmanagement_associations'));
        $countryPlaceholders = $query->bindArray($countries, ParameterType::STRING);
        $query->where($db->quoteName('country') . ' NOT IN (' . implode(',', $countryPlaceholders) . ')');
        $db->setQuery($query);
        self::runJoomlaQuery(self::class, $db);

        $this->_assoclist = [];

        foreach ($countries as $country) {
            $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/xml_files/associations_' . $country . '.xml';
            $xml = $this->loadXml($file);

            if ($xml === null) {
                $app->enqueueMessage('Für das Land: ' . $country . ' gibt es keine Datei mit Regionen.', 'error');
                continue;
            }

            $idMap = [];

            foreach ($xml->associations as $association) {
                if (!isset($association->assocname)) {
                    continue;
                }

                $attrs = $association->assocname->attributes();
                $name = trim((string) $association->assocname);
                $rowCountry = strtoupper((string) ($attrs['country'] ?? $country));
                $main = (string) ($attrs['main'] ?? '');
                $parentMain = (string) ($attrs['parentmain'] ?? '');

                if ($name === '' || $rowCountry !== $country) {
                    continue;
                }

                $parentId = $parentMain !== '' && $parentMain !== '0' ? (int) ($idMap[$parentMain] ?? 0) : 0;
                $shortName = trim((string) ($attrs['shortname'] ?? '')) ?: $name;
                $values = [
                    'country' => $country,
                    'name' => $name,
                    'parent_id' => $parentId,
                    'picture' => 'images/com_sportsmanagement/database/associations/' . basename((string) ($attrs['icon'] ?? '')),
                    'assocflag' => (string) ($attrs['flag'] ?? ''),
                    'website' => (string) ($attrs['website'] ?? ''),
                    'short_name' => $shortName,
                    'middle_name' => $name,
                    'alias' => OutputFilter::stringURLSafe($name),
                ];

                $query = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_associations'))
                    ->where($db->quoteName('country') . ' = :associationCountry')
                    ->where($db->quoteName('name') . ' = :associationName')
                    ->bind(':associationCountry', $country, ParameterType::STRING)
                    ->bind(':associationName', $name, ParameterType::STRING);
                $db->setQuery($query, 0, 1);
                $id = (int) $db->loadResult();

                try {
                    if ($id > 0) {
                        $record = (object) (['id' => $id] + $values);
                        $db->updateObject('#__sportsmanagement_associations', $record, 'id');
                    } else {
                        $record = (object) $values;
                        $db->insertObject('#__sportsmanagement_associations', $record);
                        $id = (int) $db->insertid();
                    }
                } catch (\Throwable $e) {
                    $app->enqueueMessage($e->getMessage(), 'error');
                    continue;
                }

                if ($main !== '') {
                    $idMap[$main] = $id;
                    $holder = new \stdClass();
                    $holder->id = $id;
                    $this->_assoclist[$country][$main] = [$holder];
                }
            }
        }

        return true;
    }

    /**
     * Import bundled country data from the component's SQL file.
     * Rejects missing or unreadable files, executes the bundled statements and returns a translated success or failure message.
     *
     * @return string Result of the insertCountries() operation.
     */
    public function insertCountries(): string
    {
        $db = self::sportsDatabase();
        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/sql/countries.sql';

        if (!is_file($file) || !is_readable($file)) {
            return $this->countryStatus(false);
        }

        $sql = file_get_contents($file);

        if ($sql === false) {
            return $this->countryStatus(false);
        }

        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $statements = array_values(array_filter(array_map('trim', preg_split('/;\s*(?:\R|$)/', $sql) ?: [])));

        try {
            foreach ($statements as $statement) {
                $db->setQuery($db->replacePrefix($statement));
                $db->execute();
            }
        } catch (\Throwable $e) {
            self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return $this->countryStatus(false);
        }

        return $this->countryStatus(true);
    }

    /**
     * Create a standard sport-type definition from an approved XML structure.
     * Validates the sport name, reads its built-in XML and populates default events and positions for the new sport.
     *
     * @param mixed $type Parameter used by the native insertSportType() operation.
     * @return mixed Result of the insertSportType() operation.
     */
    public function insertSportType($type)
    {
        $token = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $type) ?? '');

        if ($token === '' || !$this->checkSportTypeStructur($token)) {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>'
                . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_XML_ERROR', strtoupper($token))
                . '</strong></span><br />';

            return false;
        }

        $db = self::sportsDatabase();
        $name = 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($token);
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->where($db->quoteName('name') . ' = :sportTypeName')
            ->bind(':sportTypeName', $name, ParameterType::STRING);
        $db->setQuery($query, 0, 1);
        $id = (int) $db->loadResult();
        $isNew = $id <= 0;

        if ($isNew) {
            $record = (object) [
                'name' => $name,
                'icon' => 'images/com_sportsmanagement/database/placeholders/placeholder_21.png',
            ];

            try {
                $db->insertObject('#__sportsmanagement_sports_type', $record);
                $id = (int) $db->insertid();
                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                    . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS', strtoupper($token))
                    . '</strong></span><br />';
            } catch (\Throwable $e) {
                self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');

                return false;
            }
        }

        if ((bool) ComponentHelper::getParams('com_sportsmanagement')->get('install_standard_position', 0)) {
            $this->addStandardForSportType($name, $id, $token, $isNew ? 0 : 1);
        }

        return $id;
    }

    /**
     * Read the bundled standard event and position structure for a sport.
     * Validates the sport type token, parses XML without network access and populates import arrays for later bootstrap writes.
     *
     * @param mixed $type Parameter used by the native checkSportTypeStructur() operation.
     * @return bool Result of the checkSportTypeStructur() operation.
     */
    public function checkSportTypeStructur($type): bool
    {
        $token = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $type) ?? '');

        if ($token === '') {
            return false;
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sp_structur/' . $token . '.xml';
        $xml = $this->loadXml($file);

        if ($xml === null) {
            return false;
        }

        $this->_sport_types_events[$token] = [];
        $this->_sport_types_position[$token] = [];

        foreach ($xml->events as $event) {
            $row = new \stdClass();
            $row->name = 'COM_SPORTSMANAGEMENT_' . strtoupper($token) . '_E_' . strtoupper((string) $event->name);
            $row->icon = 'images/com_sportsmanagement/database/events/' . $token . '/' . basename(strtolower((string) $event->name['icon']));
            $this->_sport_types_events[$token][] = $row;
        }

        foreach ($xml->mainpositions as $position) {
            $row = new \stdClass();
            $row->name = 'COM_SPORTSMANAGEMENT_' . strtoupper($token) . '_F_' . strtoupper((string) $position->mainname);
            $row->switch = strtolower((string) $position->mainname['switch']);
            $row->parent = (int) $position->mainname['parent'];
            $row->content = (string) $position->mainname['content'];
            $this->_sport_types_position[$token][] = $row;
        }

        foreach ($xml->parentpositions as $parent) {
            $mainKey = 'COM_SPORTSMANAGEMENT_' . strtoupper($token) . '_F_' . strtoupper((string) $parent->parentname['main']);
            $row = new \stdClass();
            $row->name = 'COM_SPORTSMANAGEMENT_' . strtoupper($token) . '_'
                . strtoupper((string) $parent->parentname['art']) . '_' . strtoupper((string) $parent->parentname);
            $row->switch = strtolower((string) $parent->parentname['switch']);
            $row->parent = (int) $parent->parentname['parent'];
            $row->content = (string) $parent->parentname['content'];
            $row->events = (string) $parent->parentname['events'];
            $this->_sport_types_position_parent[$mainKey][] = $row;
        }

        return true;
    }

    /**
     * Insert missing event and position defaults for an existing sports category.
     * Uses the cached XML definitions and avoids duplicating named records already linked to the sports-type ID.
     *
     * @param mixed $name Parameter used by the native addStandardForSportType() operation.
     * @param mixed $id Parameter used by the native addStandardForSportType() operation.
     * @param mixed $type Parameter used by the native addStandardForSportType() operation.
     * @param mixed $update Parameter used by the native addStandardForSportType() operation.
     * @return void Result of the addStandardForSportType() operation.
     */
    public function addStandardForSportType($name, $id, $type, $update = 0): void
    {
        $sportTypeId = (int) $id;
        $token = strtolower((string) $type);

        if ($sportTypeId <= 0 || $token === '') {
            return;
        }

        if (!isset($this->_sport_types_events[$token]) && !$this->checkSportTypeStructur($token)) {
            return;
        }

        $db = self::sportsDatabase();

        foreach ($this->_sport_types_events[$token] ?? [] as $event) {
            if ($this->findNamedSportRow($db, '#__sportsmanagement_eventtype', (string) $event->name, $sportTypeId) > 0) {
                continue;
            }

            $query = $this->build_InsertQuery_Event('eventtype', $event->name, $event->icon, $sportTypeId, 2);
            $db->setQuery($query);
            self::runJoomlaQuery(self::class, $db);

            if (!(bool) $update) {
                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                    . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS', $event->name)
                    . '</strong></span><br />';
            }
        }

        foreach ($this->_sport_types_position[$token] ?? [] as $position) {
            $parentId = $this->findNamedSportRow($db, '#__sportsmanagement_position', (string) $position->name, $sportTypeId);

            if ($parentId <= 0) {
                $query = $this->build_InsertQuery_Position(
                    'position',
                    $position->name,
                    $position->switch,
                    $position->parent,
                    $position->content,
                    $sportTypeId,
                    1
                );
                $db->setQuery($query);
                self::runJoomlaQuery(self::class, $db);
                $parentId = (int) $db->insertid();

                if (!(bool) $update) {
                    $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                        . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS', $position->name)
                        . '</strong></span><br />';
                }
            }

            foreach ($this->_sport_types_position_parent[$position->name] ?? [] as $child) {
                if ($this->findNamedSportRow($db, '#__sportsmanagement_position', (string) $child->name, $sportTypeId) > 0) {
                    continue;
                }

                $query = $this->build_InsertQuery_Position(
                    'position',
                    $child->name,
                    $child->switch,
                    $parentId,
                    $child->content,
                    $sportTypeId,
                    2
                );
                $db->setQuery($query);
                self::runJoomlaQuery(self::class, $db);

                if (!(bool) $update) {
                    $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                        . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS', $child->name)
                        . '</strong></span><br />';
                }
            }
        }
    }

    /**
     * Construct a name/sport-scoped lookup query for bootstrap records.
     * Restricts selectable tables to the eventtype and position allowlist and binds the supplied search name and sport ID.
     *
     * @param mixed $tablename Parameter used by the native build_SelectQuery() operation.
     * @param mixed $param1 Parameter used by the native build_SelectQuery() operation.
     * @param mixed $st_id Parameter used by the native build_SelectQuery() operation.
     * @return mixed Result of the build_SelectQuery() operation.
     */
    public function build_SelectQuery($tablename, $param1, $st_id = 0)
    {
        $table = $this->allowedBootstrapTable((string) $tablename, ['eventtype', 'position']);
        $db = self::sportsDatabase();

        $name = (string) $param1;
        $sportTypeId = (int) $st_id;

        return $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_' . $table))
            ->where($db->quoteName('name') . ' = :bootstrapName')
            ->where($db->quoteName('sports_type_id') . ' = :bootstrapSportTypeId')
            ->bind(':bootstrapName', $name, ParameterType::STRING)
            ->bind(':bootstrapSportTypeId', $sportTypeId, ParameterType::INTEGER);
    }

    /**
     * Build a SQL insert for a standard sport event type.
     * Requires the whitelisted eventtype table and quotes the event name, alias, icon and ordering fields safely.
     *
     * @param mixed $tablename Parameter used by the native build_InsertQuery_Event() operation.
     * @param mixed $param1 Parameter used by the native build_InsertQuery_Event() operation.
     * @param mixed $param2 Parameter used by the native build_InsertQuery_Event() operation.
     * @param mixed $sports_type_id Parameter used by the native build_InsertQuery_Event() operation.
     * @param mixed $order_count Parameter used by the native build_InsertQuery_Event() operation.
     * @return mixed Result of the build_InsertQuery_Event() operation.
     */
    public function build_InsertQuery_Event($tablename, $param1, $param2, $sports_type_id, $order_count)
    {
        $table = $this->allowedBootstrapTable((string) $tablename, ['eventtype']);
        $db = self::sportsDatabase();
        $this->ensureImportIdColumn($db, '#__sportsmanagement_' . $table);

        return $db->createQuery()
            ->insert($db->quoteName('#__sportsmanagement_' . $table))
            ->columns($db->quoteName(['name', 'alias', 'icon', 'sports_type_id', 'published', 'ordering', 'import_id']))
            ->values(implode(',', [
                $db->quote((string) $param1),
                $db->quote(OutputFilter::stringURLSafe((string) $param1)),
                $db->quote((string) $param2),
                (int) $sports_type_id,
                1,
                (int) $order_count,
                0,
            ]));
    }

    /**
     * Build an insert query for a standard sport position.
     * Allows only the position table, validates the configured switch column against the physical schema and safely inserts position details.
     *
     * @param mixed $tablename Parameter used by the native build_InsertQuery_Position() operation.
     * @param mixed $param1 Parameter used by the native build_InsertQuery_Position() operation.
     * @param mixed $param2 Parameter used by the native build_InsertQuery_Position() operation.
     * @param mixed $param3 Parameter used by the native build_InsertQuery_Position() operation.
     * @param mixed $param4 Parameter used by the native build_InsertQuery_Position() operation.
     * @param mixed $sports_type_id Parameter used by the native build_InsertQuery_Position() operation.
     * @param mixed $order_count Parameter used by the native build_InsertQuery_Position() operation.
     * @return mixed Result of the build_InsertQuery_Position() operation.
     */
    public function build_InsertQuery_Position($tablename, $param1, $param2, $param3, $param4, $sports_type_id, $order_count)
    {
        $table = $this->allowedBootstrapTable((string) $tablename, ['position']);
        $db = self::sportsDatabase();
        $fullTable = '#__sportsmanagement_' . $table;
        $this->ensureImportIdColumn($db, $fullTable);
        $switchColumn = preg_replace('/[^A-Za-z0-9_]/', '', (string) $param2) ?? '';
        $columns = $db->getTableColumns($fullTable, false);

        if ($switchColumn === '' || !array_key_exists($switchColumn, $columns)) {
            throw new \InvalidArgumentException('Invalid position switch column.');
        }

        return $db->createQuery()
            ->insert($db->quoteName($fullTable))
            ->columns($db->quoteName(['name', 'alias', $switchColumn, 'parent_id', 'sports_type_id', 'published', 'ordering', 'import_id']))
            ->values(implode(',', [
                $db->quote((string) $param1),
                $db->quote(OutputFilter::stringURLSafe((string) $param1)),
                $db->quote((string) $param4),
                (int) $param3,
                (int) $sports_type_id,
                1,
                (int) $order_count,
                0,
            ]));
    }

    /**
     * Create a relation insert for a sport position and event type.
     * Uses the fixed component relation table, checks import_id support and inserts numeric position/event identifiers.
     *
     * @param mixed $param1 Parameter used by the native build_InsertQuery_PositionEventType() operation.
     * @param mixed $param2 Parameter used by the native build_InsertQuery_PositionEventType() operation.
     * @return mixed Result of the build_InsertQuery_PositionEventType() operation.
     */
    public function build_InsertQuery_PositionEventType($param1, $param2)
    {
        $db = self::sportsDatabase();
        $table = '#__sportsmanagement_position_eventtype';
        $this->ensureImportIdColumn($db, $table);

        return $db->createQuery()
            ->insert($db->quoteName($table))
            ->columns($db->quoteName(['position_id', 'eventtype_id', 'import_id']))
            ->values(implode(',', [(int) $param1, (int) $param2, 0]));
    }

    /**
     * List existing tables with a permitted component prefix.
     * Filters the database driver table list to the current Joomla prefix plus the requested namespace and sorts it predictably.
     *
     * @param mixed $needle Parameter used by the native componentTables() operation.
     * @return array Result of the componentTables() operation.
     */
    private function componentTables(string $needle): array
    {
        $db = self::sportsDatabase();
        $prefix = (string) $db->getPrefix();
        $wanted = $prefix . $needle;
        $tables = array_values(array_filter(
            $db->getTableList(),
            static fn (string $table): bool => str_starts_with($table, $wanted)
        ));
        sort($tables, SORT_NATURAL | SORT_FLAG_CASE);

        return $tables;
    }

    /**
     * Replace an obsolete translation or path fragment in a trusted column.
     * Uses parameter-bound search and replacement strings to update only matching entries in a predefined table and field.
     *
     * @param mixed $table Parameter used by the native replaceFieldValue() operation.
     * @param mixed $field Parameter used by the native replaceFieldValue() operation.
     * @param mixed $search Parameter used by the native replaceFieldValue() operation.
     * @param mixed $replace Parameter used by the native replaceFieldValue() operation.
     * @return int Result of the replaceFieldValue() operation.
     */
    private function replaceFieldValue(string $table, string $field, string $search, string $replace): int
    {
        $db = self::sportsDatabase();
        $likeSearch = '%' . $search . '%';
        $query = $db->createQuery()
            ->update($db->quoteName($table))
            ->set(
                $db->quoteName($field) . ' = REPLACE('
                . $db->quoteName($field) . ', :replaceSearch, :replaceValue)'
            )
            ->where($db->quoteName($field) . ' LIKE :replaceLike')
            ->bind(':replaceSearch', $search, ParameterType::STRING)
            ->bind(':replaceValue', $replace, ParameterType::STRING)
            ->bind(':replaceLike', $likeSearch, ParameterType::STRING);
        $db->setQuery($query);

        if (!self::runJoomlaQuery(self::class, $db)) {
            return 0;
        }

        $affected = (int) $db->getAffectedRows();
        self::resolveAdministratorApplication()->enqueueMessage(Text::_('Wir haben ' . $affected . ' Datensätze aktualisiert.'), 'notice');

        return $affected;
    }

    /**
     * Construct translated administrator feedback for country bootstrap results.
     * Stores a success or error message with the configured colour used by the legacy database-tool view.
     *
     * @param mixed $success Parameter used by the native countryStatus() operation.
     * @return string Result of the countryStatus() operation.
     */
    private function countryStatus(bool $success): string
    {
        if ($success) {
            $this->my_text = '<span style="color:' . $this->storeSuccessColor . '"><strong>'
                . Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS') . '</strong></span><br />';
        } else {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>'
                . Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR') . '</strong></span><br />';
        }

        return $this->my_text;
    }

    /**
     * Read trusted local bootstrap XML without external entity or network access.
     * Rejects missing/unreadable files, enables safe libxml error handling and returns parsed XML or null.
     *
     * @param mixed $file Parameter used by the native loadXml() operation.
     * @return ?\SimpleXMLElement Result of the loadXml() operation.
     */
    private function loadXml(string $file): ?\SimpleXMLElement
    {
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_file($file, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);

            return $xml instanceof \SimpleXMLElement ? $xml : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * Execute a Joomla query and return object rows with error reporting.
     * Uses the provided DatabaseInterface and enqueues an administrator error message instead of propagating SQL exceptions.
     *
     * @param mixed $db Parameter used by the native loadObjectList() operation.
     * @param mixed $query Parameter used by the native loadObjectList() operation.
     * @return array Result of the loadObjectList() operation.
     */
    private function loadObjectList(DatabaseInterface $db, $query): array
    {
        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Find an existing sport event or position by name and sports-type ID.
     * Builds a parameter-bound single-record lookup so defaults are not inserted twice.
     *
     * @param mixed $db Parameter used by the native findNamedSportRow() operation.
     * @param mixed $table Parameter used by the native findNamedSportRow() operation.
     * @param mixed $name Parameter used by the native findNamedSportRow() operation.
     * @param mixed $sportTypeId Parameter used by the native findNamedSportRow() operation.
     * @return int Result of the findNamedSportRow() operation.
     */
    private function findNamedSportRow(DatabaseInterface $db, string $table, string $name, int $sportTypeId): int
    {
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName($table))
            ->where($db->quoteName('name') . ' = :namedSportName')
            ->where($db->quoteName('sports_type_id') . ' = :namedSportTypeId')
            ->bind(':namedSportName', $name, ParameterType::STRING)
            ->bind(':namedSportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /**
     * Add the legacy import_id column when a trusted bootstrap table lacks it.
     * Inspects current table columns and performs the required schema update through the guarded query runner.
     *
     * @param mixed $db Parameter used by the native ensureImportIdColumn() operation.
     * @param mixed $table Parameter used by the native ensureImportIdColumn() operation.
     * @return void Result of the ensureImportIdColumn() operation.
     */
    private function ensureImportIdColumn(DatabaseInterface $db, string $table): void
    {
        $columns = $db->getTableColumns($table, false);

        if (array_key_exists('import_id', $columns)) {
            return;
        }

        $db->setQuery(
            'ALTER TABLE ' . $db->quoteName($table)
            . ' ADD ' . $db->quoteName('import_id') . " INT NOT NULL DEFAULT 0"
        );
        self::runJoomlaQuery(self::class, $db);
    }

    /**
     * Enforce the exact allowlist of tables used by sport bootstrap SQL.
     * Rejects punctuation, whitespace and table names that are not explicitly permitted to prevent arbitrary table selection.
     *
     * @param mixed $table Parameter used by the native allowedBootstrapTable() operation.
     * @param mixed $allowed Parameter used by the native allowedBootstrapTable() operation.
     * @return string Result of the allowedBootstrapTable() operation.
     */
    private function allowedBootstrapTable(string $table, array $allowed): string
    {
        $clean = preg_replace('/[^A-Za-z0-9_]/', '', $table) ?? '';

        if ($clean !== $table || !in_array($clean, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid bootstrap table.');
        }

        return $clean;
    }

    /**
     * Resolve the primary SportsManagement database through Joomla DI.
     * Obtains DatabaseInterface from the service container and delegates its selection to the administrator database resolver.
     *
     * @return DatabaseInterface Result of the sportsDatabase() operation.
     */
    private static function sportsDatabase(): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve(
            0,
            Factory::getContainer()->get(DatabaseInterface::class)
        );
    }
    /**
     * Resolve the native Joomla administrator application for database tooling.
     * Loads AdministratorApplication from the container and checks its client type before maintenance error reporting.
     *
     * @return AdministratorApplication Result of the resolveAdministratorApplication() operation.
     */
    private static function resolveAdministratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement database tool model requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
