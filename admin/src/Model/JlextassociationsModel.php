<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for associations. */
final class JlextassociationsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'objassoc.name', 'name',
            'objassoc.alias', 'alias',
            'objassoc.short_name', 'short_name',
            'objassoc.country', 'country', 'search_nation',
            'objassoc.id', 'id',
            'objassoc.website', 'website',
            'objassoc.ordering', 'ordering',
            'objassoc.published', 'published', 'state',
            'objassoc.modified', 'modified',
            'objassoc.modified_by', 'modified_by',
            'objassoc.checked_out', 'checked_out',
            'objassoc.checked_out_time', 'checked_out_time',
            'objassoc.assocflag', 'assocflag',
            'objassoc.picture', 'picture',
            'objassoc.flag_maps', 'flag_maps',
            'objassoc.parent_id', 'parent_id', 'federation',
        ];

        parent::__construct($config, $factory);
    }

    public function getAssociations(int $federation = 0): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations'))
            ->order($db->quoteName('name') . ' ASC');

        if ($federation > 0) {
            $query->where(
                '(' . $db->quoteName('parent_id') . ' = ' . $federation
                . ' OR ' . $db->quoteName('id') . ' = ' . $federation . ')'
            );
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '' && $country !== '0') {
            $query->where($db->quoteName('country') . ' = ' . $db->quote($country));
        }

        try {
            $db->setQuery($query);
            $result = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }

        foreach ($result as $association) {
            $association->name = '( ' . $association->country . ' ) ' . Text::_($association->name);
            $association->text = $association->name;
        }

        return $result;
    }

    /**
     * Synchronise configured association countries from the bundled XML files.
     *
     * @return array{inserted:int,updated:int,deleted:int,missing:array<int,string>,failed:bool}
     */
    public function checkAssociations(): array
    {
        $summary = [
            'inserted' => 0,
            'updated' => 0,
            'deleted' => 0,
            'missing' => [],
            'failed' => false,
        ];
        $app = $this->administratorApplication();
        $countries = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_country_associations', []);

        if (is_string($countries)) {
            $countries = preg_split('/\s*,\s*/', $countries, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (!is_array($countries)) {
            $countries = [];
        }

        $countries = array_values(array_unique(array_filter(array_map(
            static fn($country): string => strtoupper(trim((string) $country)),
            $countries
        ))));

        if (!$countries) {
            return $summary;
        }

        $masterFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/xml_files/associations.xml';

        if (!is_file($masterFile)) {
            $app->enqueueMessage('Association import file associations.xml is missing.', 'error');
            $summary['failed'] = true;

            return $summary;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            $quotedCountries = array_map([$db, 'quote'], $countries);
            $delete = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_associations'))
                ->where($db->quoteName('country') . ' NOT IN (' . implode(',', $quotedCountries) . ')');
            $db->setQuery($delete)->execute();

            if (method_exists($db, 'getAffectedRows')) {
                $summary['deleted'] = (int) $db->getAffectedRows();
            }

            foreach ($countries as $configuredCountry) {
                $file = JPATH_ADMINISTRATOR
                    . '/components/com_sportsmanagement/helpers/xml_files/associations_'
                    . $configuredCountry . '.xml';

                if (!is_file($file)) {
                    $summary['missing'][] = $configuredCountry;
                    $app->enqueueMessage(
                        'Für das Land ' . $configuredCountry . ' gibt es keine Datei mit Regionen.',
                        'error'
                    );
                    continue;
                }

                $xml = @simplexml_load_file($file);

                if ($xml === false) {
                    $summary['missing'][] = $configuredCountry;
                    $app->enqueueMessage('Association XML could not be parsed: ' . basename($file), 'error');
                    continue;
                }

                $associationIds = [];

                foreach ($xml->associations as $association) {
                    if (!isset($association->assocname)) {
                        continue;
                    }

                    $node = $association->assocname;
                    $country = strtoupper(trim((string) $node->attributes()->country));

                    if ($country !== $configuredCountry) {
                        continue;
                    }

                    $main = trim((string) $node->attributes()->main);
                    $parentMain = trim((string) $node->attributes()->parentmain);
                    $name = trim((string) $node);

                    if ($name === '') {
                        continue;
                    }

                    $shortName = trim((string) $node->attributes()->shortname);
                    $shortName = $shortName !== '' ? $shortName : $name;
                    $website = trim((string) $node->attributes()->website);
                    $picture = 'images/com_sportsmanagement/database/associations/'
                        . trim((string) $node->attributes()->icon);
                    $flag = trim((string) $node->attributes()->flag);
                    $alias = OutputFilter::stringURLSafe($name);
                    $parentId = 0;

                    if ($parentMain !== '' && $parentMain !== '0' && isset($associationIds[$parentMain])) {
                        $parentId = (int) $associationIds[$parentMain];
                    }

                    $lookup = $db->getQuery(true)
                        ->select($db->quoteName('id'))
                        ->from($db->quoteName('#__sportsmanagement_associations'))
                        ->where($db->quoteName('country') . ' = ' . $db->quote($country))
                        ->where($db->quoteName('name') . ' = ' . $db->quote($name));
                    $db->setQuery($lookup, 0, 1);
                    $id = (int) $db->loadResult();

                    if ($id <= 0) {
                        $row = (object) [
                            'country' => $country,
                            'name' => $name,
                            'parent_id' => $parentId,
                            'picture' => $picture,
                            'assocflag' => $flag,
                            'website' => $website,
                            'short_name' => $shortName,
                            'middle_name' => $name,
                            'alias' => $alias,
                        ];
                        $db->insertObject('#__sportsmanagement_associations', $row);
                        $id = (int) $db->insertid();
                        $summary['inserted']++;
                    } else {
                        $row = (object) [
                            'id' => $id,
                            'website' => $website,
                            'short_name' => $shortName,
                            'middle_name' => $name,
                            'alias' => $alias,
                        ];
                        $db->updateObject('#__sportsmanagement_associations', $row, 'id');
                        $summary['updated']++;
                    }

                    if ($main !== '') {
                        $associationIds[$main] = $id;
                    }
                }
            }

            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $summary['failed'] = true;
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        return $summary;
    }

    protected function populateState($ordering = 'objassoc.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $input = $this->administratorApplication()->getInput();
        $country = $input->getString('filter_search_nation');
        $federation = $input->getInt('filter_federation');

        if ($country !== '') {
            $this->setState('filter.search_nation', $country);
        }

        if ($federation > 0) {
            $this->setState('filter.federation', $federation);
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('objassoc') . '.*',
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 'objassoc'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('objassoc.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('objassoc.name') . ') LIKE LOWER(' . $token . ')');
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '' && $country !== '0') {
            $query->where($db->quoteName('objassoc.country') . ' = ' . $db->quote($country));
        }

        $federation = (int) $this->getState('filter.federation', 0);

        if ($federation > 0) {
            $query->where($db->quoteName('objassoc.parent_id') . ' = ' . $federation);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('objassoc.published') . ' = ' . (int) $state);
        }

        $map = [
            'objassoc.name' => $db->quoteName('objassoc.name'),
            'name' => $db->quoteName('objassoc.name'),
            'objassoc.alias' => $db->quoteName('objassoc.alias'),
            'alias' => $db->quoteName('objassoc.alias'),
            'objassoc.short_name' => $db->quoteName('objassoc.short_name'),
            'short_name' => $db->quoteName('objassoc.short_name'),
            'objassoc.country' => $db->quoteName('objassoc.country'),
            'country' => $db->quoteName('objassoc.country'),
            'search_nation' => $db->quoteName('objassoc.country'),
            'objassoc.id' => $db->quoteName('objassoc.id'),
            'id' => $db->quoteName('objassoc.id'),
            'objassoc.website' => $db->quoteName('objassoc.website'),
            'website' => $db->quoteName('objassoc.website'),
            'objassoc.ordering' => $db->quoteName('objassoc.ordering'),
            'ordering' => $db->quoteName('objassoc.ordering'),
            'objassoc.published' => $db->quoteName('objassoc.published'),
            'published' => $db->quoteName('objassoc.published'),
            'state' => $db->quoteName('objassoc.published'),
            'objassoc.modified' => $db->quoteName('objassoc.modified'),
            'modified' => $db->quoteName('objassoc.modified'),
            'objassoc.modified_by' => $db->quoteName('objassoc.modified_by'),
            'modified_by' => $db->quoteName('objassoc.modified_by'),
            'objassoc.checked_out' => $db->quoteName('objassoc.checked_out'),
            'checked_out' => $db->quoteName('objassoc.checked_out'),
            'objassoc.checked_out_time' => $db->quoteName('objassoc.checked_out_time'),
            'checked_out_time' => $db->quoteName('objassoc.checked_out_time'),
            'objassoc.assocflag' => $db->quoteName('objassoc.assocflag'),
            'assocflag' => $db->quoteName('objassoc.assocflag'),
            'objassoc.picture' => $db->quoteName('objassoc.picture'),
            'picture' => $db->quoteName('objassoc.picture'),
            'objassoc.flag_maps' => $db->quoteName('objassoc.flag_maps'),
            'flag_maps' => $db->quoteName('objassoc.flag_maps'),
            'objassoc.parent_id' => $db->quoteName('objassoc.parent_id'),
            'parent_id' => $db->quoteName('objassoc.parent_id'),
            'federation' => $db->quoteName('objassoc.parent_id'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'objassoc.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['objassoc.name']) . ' ' . $direction);

        return $query;
    }
}
