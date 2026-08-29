<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Administrator\Service\XmlEventImportService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\XmlPositionImportService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\XmlStatisticImportService;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use RuntimeException;

/**
 * Native Joomla 5/6 facade for the XML import workflow.
 *
 * Normal JLG/XML parsing, standalone event/position/statistic writes and
 * read-only lookup/update operations are handled natively. Only the historical
 * project write engine, the remaining standalone import types and the special
 * Èlanska source format still cross the explicit legacy boundary.
 */
final class JlxmlimportModel extends BaseDatabaseModel
{
    private const RECORD_COLLECTIONS = [
        'LeagueDivision' => 'division',
        'Club' => 'club',
        'JL_Team' => 'team',
        'ProjectTeam' => 'projectteam',
        'TeamTool' => 'teamtool',
        'Round' => 'round',
        'Match' => 'match',
        'Playground' => 'playground',
        'Template' => 'template',
        'EventType' => 'event',
        'Position' => 'position',
        'ParentPosition' => 'parentposition',
        'ProjectReferee' => 'projectreferee',
        'ProjectPosition' => 'projectposition',
        'Person' => 'person',
        'TeamPlayer' => 'teamplayer',
        'TeamStaff' => 'teamstaff',
        'TeamTraining' => 'teamtraining',
        'MatchPlayer' => 'matchplayer',
        'MatchStaff' => 'matchstaff',
        'MatchReferee' => 'matchreferee',
        'MatchEvent' => 'matchevent',
        'PositionEventType' => 'positioneventtype',
        'Statistic' => 'statistic',
        'PositionStatistic' => 'positionstatistic',
        'MatchStaffStatistic' => 'matchstaffstatistic',
        'MatchStatistic' => 'matchstatistic',
        'Treeto' => 'treeto',
        'TreetoNode' => 'treetonode',
        'TreetoMatch' => 'treetomatch',
    ];

    public string $import_version = '';

    private ?object $legacyModel = null;
    private array $parsedData = [];

    public function getDataUpdateImportID(): int|false
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $projectId = (int) $app->getUserState($option . '.pid', 0);

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('import_project_id'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $importProjectId = $db->loadResult();

        return $importProjectId === null ? false : (int) $importProjectId;
    }

    public function getUserList(bool $isAdmin = false): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('username'),
            ])
            ->from($db->quoteName('#__users'))
            ->order($db->quoteName('username') . ' ASC');

        // The historical `usertype` column no longer exists in modern Joomla.
        // Current XML import callers request the complete user list, so keep
        // the argument for API compatibility without reintroducing that query.
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getTemplateList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('master_template') . ' = 0')
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getNewClubList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getNewClubListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getClubAndTeamList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->order($db->quoteName('c.name') . ' ASC, ' . $db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getClubAndTeamListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                'CONCAT('
                    . $db->quoteName('c.name') . ', '
                    . $db->quote(' - ') . ', '
                    . $db->quoteName('t.name') . ', '
                    . $db->quote(' (') . ', '
                    . $db->quoteName('t.info') . ', '
                    . $db->quote(')')
                    . ') AS ' . $db->quoteName('text'),
                $db->quoteName('t.club_id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->order($db->quoteName('c.name') . ' ASC, ' . $db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getData(array $post = []): mixed
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';

        // Keep the historical Slovenian source parser behind the legacy
        // boundary until its non-JLG data shape is migrated separately.
        if ((bool) $app->getUserState($option . 'importelanska', false)) {
            $result = $this->legacy()->getData($post);
            $this->syncLegacyState();

            return $result;
        }

        $this->parsedData = [];
        $this->import_version = '';
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xmlData = $this->loadXmlImport();

        if ($xmlData === false) {
            $this->reportXmlErrors();

            return false;
        }

        if (!isset($xmlData->record) || !is_object($xmlData->record)) {
            $app->enqueueMessage(
                Text::sprintf(
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR',
                    'Something is wrong inside the import file'
                ),
                'error'
            );

            return false;
        }

        foreach ($xmlData->record as $record) {
            $objectType = (string) $record['object'];

            switch ($objectType) {
                case 'JoomLeagueVersion':
                case 'SportsManagementVersion':
                    $this->parsedData['exportversion'] = $record;
                    break;

                case 'JoomLeague':
                    $this->parsedData['project'] = $record;
                    $this->import_version = 'OLD';
                    $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_093'), '');
                    break;

                case 'JoomLeague15':
                    $this->parsedData['project'] = $record;
                    $this->import_version = 'NEW';
                    $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_15'), '');
                    break;

                case 'JoomLeague20':
                case 'SportsManagement':
                    $this->parsedData['project'] = $record;
                    $this->import_version = 'NEW';
                    $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_20'), '');
                    break;

                case 'League':
                    $this->parsedData['league'] = $record;
                    break;

                case 'Season':
                    $this->parsedData['season'] = $record;
                    break;

                case 'SportsType':
                    $this->parsedData['sportstype'] = $record;
                    break;

                default:
                    $collection = self::RECORD_COLLECTIONS[$objectType] ?? null;

                    if ($collection !== null) {
                        $this->parsedData[$collection][] = $record;
                    }
                    break;
            }
        }

        $this->normaliseParsedData($option);

        if (!empty($this->parsedData['teamtool'])) {
            $this->parsedData['projectteam'] = array_values($this->parsedData['teamtool']);
        }

        return $this->parsedData;
    }

    public function getDataUpdate(): array
    {
        if ($this->parsedData === []) {
            if ($this->legacyModel !== null) {
                $result = $this->legacyModel->getDataUpdate();
                $this->syncLegacyState();

                return is_array($result) ? $result : [];
            }

            $data = $this->getData();

            if (!is_array($data)) {
                return [];
            }
        }

        $db = $this->getDatabase();
        $message = '';

        foreach ((array) ($this->parsedData['match'] ?? []) as $value) {
            $importMatchId = (int) ($value->id ?? 0);

            if ($importMatchId <= 0) {
                continue;
            }

            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('m.id'),
                    $db->quoteName('m.match_date'),
                    $db->quoteName('t1.name', 'hometeam'),
                    $db->quoteName('t2.name', 'awayteam'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                    . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                    . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_team', 't1')
                    . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                    . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                    . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_team', 't2')
                    . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id')
                )
                ->where($db->quoteName('m.import_match_id') . ' = ' . $importMatchId);
            $db->setQuery($query, 0, 1);
            $match = $db->loadObject();

            if (!$match) {
                continue;
            }

            $message .= '<span style="color:green">';
            $message .= Text::sprintf(
                'Update Match: %1$s / Match: %2$s - %3$s / Result: %4$s - %5$s',
                '</span><strong>' . (int) $match->id . '</strong><span style="color:green">',
                '</span><strong>' . (string) $match->hometeam . '</strong>',
                '<strong>' . (string) $match->awayteam . '</strong>',
                '<strong>' . (string) ($value->team1_result ?? '') . '</strong>',
                '<strong>' . (string) ($value->team2_result ?? '') . '</strong>'
            );
            $message .= '<br />';

            if (!isset($value->team1_result) || (string) $value->team1_result === '') {
                continue;
            }

            $update = (object) [
                'id' => (int) $match->id,
                'team1_result' => (int) $value->team1_result,
                'team2_result' => (int) ($value->team2_result ?? 0),
            ];

            try {
                $stored = $db->updateObject('#__sportsmanagement_match', $update, 'id');
            } catch (\Throwable) {
                $stored = false;
            }

            if (!$stored) {
                $message .= '<span style="color:red"> nicht gesichert - '
                    . (string) $match->match_date . '</span><br />';
                continue;
            }

            $message .= '<span style="color:green"> gesichert - '
                . (string) $match->match_date . '</span><br />';
        }

        return ['Update match data:' => $message];
    }

    public function importData(array $post): mixed
    {
        $nativeWriter = null;

        if (empty($post['importProject'])) {
            $nativeWriter = match ((string) ($post['importType'] ?? '')) {
                'events' => new XmlEventImportService($this->getDatabase()),
                'positions' => new XmlPositionImportService($this->getDatabase()),
                'statistics' => new XmlStatisticImportService($this->getDatabase()),
                default => null,
            };
        }

        if ($nativeWriter !== null) {
            if ($this->parsedData === []) {
                $data = $this->getData($post);

                if (!is_array($data)) {
                    $this->deleteImportFile();

                    return false;
                }
            }

            try {
                return $nativeWriter->import($post, $this->parsedData);
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                return false;
            } finally {
                $this->deleteImportFile();
            }
        }

        $result = $this->legacy()->importData($post);
        $this->syncLegacyState();

        return $result;
    }

    public function getCountryByOldid(): array
    {
        $countries = explode(
            ',',
            ',AFG,ALB,DZA,ASM,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,KHM,CMR,CAN,CPV,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COK,CRI,CIV,HRV,CUB,CYP,CZE,DNK,DJI,DMA,DOM,TMP,ECU,EGY,SLV,GNQ,ERI,EST,ETH,FLK,FRO,FJI,FIN,FRA,FXX,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GIN,GNB,GUY,HTI,HMD,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,ISR,ITA,JAM,JPN,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MKD,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,ANT,NCL,NZL,NIC,NER,NGA,NIU,NFK,MNP,NOR,OMN,PAK,PLW,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROM,RUS,RWA,KNA,LCA,VCT,WSM,SMR,STP,SAU,SEN,SYC,SLE,SGP,SVK,SVN,SLB,SOM,ZAF,SGS,ESP,LKA,SHN,SPM,SDN,SUR,SJM,SWZ,SWE,CHE,SYR,TWN,TJK,TZA,THA,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,USA,UMI,URY,UZB,VUT,VAT,VEN,VNM,VGB,VIR,WLF,ESH,YEM'
        );
        $countries[238] = 'ZMB';
        $countries[239] = 'ZWE';
        $countries[240] = 'ENG';
        $countries[241] = 'SCO';
        $countries[242] = 'WAL';
        $countries[243] = 'ALA';
        $countries[244] = 'NEI';
        $countries[245] = 'MNE';
        $countries[246] = 'SRB';

        return $countries;
    }

    private function loadXmlImport(): \SimpleXMLElement|false
    {
        $path = JPATH_SITE . '/tmp/sportsmanagement_import.jlg';

        if (!is_file($path)) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Missing import file'),
                'error'
            );

            return false;
        }

        if (!function_exists('simplexml_load_file')) {
            Factory::getApplication()->enqueueMessage(
                Text::_('SimpleXML does not exist on your system!'),
                'error'
            );

            return false;
        }

        return @simplexml_load_file(
            $path,
            \SimpleXMLElement::class,
            LIBXML_NOCDATA | LIBXML_NONET
        );
    }

    private function deleteImportFile(): void
    {
        $path = JPATH_SITE . '/tmp/sportsmanagement_import.jlg';

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function reportXmlErrors(): void
    {
        $app = Factory::getApplication();
        $app->enqueueMessage(
            Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR',
                ' Load of the importfile failed:'
            ),
            'error'
        );
        $errors = libxml_get_errors();

        if ($errors === []) {
            $app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', ' Unknown error :-('),
                'error'
            );

            return;
        }

        foreach ($errors as $error) {
            $app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', trim($error->message)),
                'error'
            );
        }
    }

    private function normaliseParsedData(string $option): void
    {
        $params = ComponentHelper::getParams($option);

        foreach (['person', 'teamplayer', 'teamstaff', 'projectreferee'] as $collection) {
            foreach ((array) ($this->parsedData[$collection] ?? []) as $record) {
                $this->normaliseImageProperty($record, 'picture', (string) $params->get('ph_player', ''));
            }
        }

        foreach (['team', 'projectteam'] as $collection) {
            foreach ((array) ($this->parsedData[$collection] ?? []) as $record) {
                $this->normaliseImageProperty($record, 'picture', (string) $params->get('ph_team', ''));
            }
        }

        foreach ((array) ($this->parsedData['club'] ?? []) as $club) {
            $this->normaliseImageProperty($club, 'logo_big', (string) $params->get('ph_logo_big', ''));
            $this->normaliseImageProperty($club, 'logo_middle', (string) $params->get('ph_logo_medium', ''));
            $this->normaliseImageProperty($club, 'logo_small', (string) $params->get('ph_logo_small', ''));
        }

        foreach ((array) ($this->parsedData['playground'] ?? []) as $playground) {
            if (trim((string) ($playground->country ?? '')) === '') {
                $playground->country = 'DEU';
            }

            $this->normaliseImageProperty(
                $playground,
                'picture',
                (string) $params->get('ph_stadium', '')
            );
        }

        $this->normaliseLanguageKeys($option);
    }

    private function normaliseImageProperty(object $record, string $property, string $placeholder): void
    {
        $value = (string) ($record->{$property} ?? '');
        $value = str_replace('com_joomleague', 'com_sportsmanagement', $value);
        $value = str_replace('media', 'images', $value);

        if ($value === '' || preg_match('/placeholders/i', $value)) {
            $value = $placeholder;
        }

        $record->{$property} = $value;
    }

    private function normaliseLanguageKeys(string $option): void
    {
        $prefix = strtoupper($option);
        $sportTypeName = '';

        if (isset($this->parsedData['sportstype'])) {
            $name = str_replace(
                'COM_JOOMLEAGUE',
                $prefix,
                (string) ($this->parsedData['sportstype']->name ?? '')
            );
            $this->parsedData['sportstype']->name = $name;
            $parts = explode('_', $name);
            $sportTypeName = (string) array_pop($parts);
        }

        foreach ((array) ($this->parsedData['event'] ?? []) as $event) {
            $event->name = str_replace(
                'COM_JOOMLEAGUE',
                $prefix . ($sportTypeName !== '' ? '_' . $sportTypeName : ''),
                (string) ($event->name ?? '')
            );
        }

        $knownPositions = [];

        if ($sportTypeName !== '') {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('name'),
                    $db->quoteName('alias'),
                ])
                ->from($db->quoteName('#__sportsmanagement_position'))
                ->where($db->quoteName('name') . ' LIKE ' . $db->quote('%' . $sportTypeName . '%'));
            $db->setQuery($query);
            $knownPositions = $db->loadObjectList() ?: [];
        }

        foreach (['position', 'parentposition'] as $collection) {
            foreach ((array) ($this->parsedData[$collection] ?? []) as $position) {
                $position->name = str_replace(
                    'COM_JOOMLEAGUE',
                    $prefix . ($sportTypeName !== '' ? '_' . $sportTypeName : ''),
                    (string) ($position->name ?? '')
                );

                foreach ($knownPositions as $knownPosition) {
                    if ((string) $position->name === Text::_((string) $knownPosition->name)) {
                        $position->name = $knownPosition->name;
                        $position->alias = $knownPosition->alias;
                        break;
                    }
                }
            }
        }
    }

    private function legacy(): object
    {
        if ($this->legacyModel !== null) {
            return $this->legacyModel;
        }

        LegacyBootstrap::bootForView('jlxmlimport');
        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlxmlimport.php';

        if (!class_exists('sportsmanagementModelJLXMLImport', false) && is_file($file)) {
            require_once $file;
        }

        if (!class_exists('sportsmanagementModelJLXMLImport', false)) {
            throw new RuntimeException('Legacy SportsManagement XML import engine not found.', 500);
        }

        try {
            $this->legacyModel = new \sportsmanagementModelJLXMLImport();
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'The remaining legacy XML import engine could not be initialised: ' . $e->getMessage(),
                500,
                $e
            );
        }

        $this->syncLegacyState();

        return $this->legacyModel;
    }

    private function syncLegacyState(): void
    {
        if ($this->legacyModel !== null && isset($this->legacyModel->import_version)) {
            $this->import_version = (string) $this->legacyModel->import_version;
        }
    }
}
