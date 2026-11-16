<?php
/**
 * Joomla 5/6 administrator associations list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredAssociationInstaller;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 administrator list model for associations. */
final class JlextassociationsModel extends SportsManagementListModel
{
    /**
     * Initialise the administrator association list's safe sorting fields.
     *
     * @param array $config Joomla MVC list-model configuration options.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     */
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

    /**
     * Build the native Joomla 5/6 association-country filter choices.
     *
     * Uses registered country names from the shared country options helper,
     * then adds valid three-letter country codes already stored on association
     * rows. This fallback keeps imported associations filterable when their
     * country has not yet been registered in the separate countries table.
     * Excludes malformed codes, removes duplicates and returns sorted, safe
     * scalar option values without exposing database errors in the backend.
     *
     * @return array<int,object> Country options with value and text properties.
     */
    public function getCountryOptions(): array
    {
        $db = $this->getDatabase();
        $options = [];
        $seen = [];

        foreach (CountryOptionsHelper::getOptions($db) as $option) {
            if (!is_object($option)
                || !is_string($option->value ?? null)
                || !is_string($option->text ?? null)) {
                continue;
            }

            $code = strtoupper(trim($option->value));

            if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                continue;
            }

            $seen[$code] = true;
            $options[] = (object) ['value' => $code, 'text' => $option->text];
        }

        try {
            $query = $db->createQuery()
                ->select('DISTINCT ' . $db->quoteName('country'))
                ->from($db->quoteName('#__sportsmanagement_associations'))
                ->where($db->quoteName('country') . ' IS NOT NULL')
                ->order($db->quoteName('country') . ' ASC');
            $db->setQuery($query);
            $countries = $db->loadColumn();

            if (is_array($countries)) {
                foreach ($countries as $value) {
                    if (!is_string($value)) {
                        continue;
                    }

                    $code = strtoupper(trim($value));

                    if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                        continue;
                    }

                    $seen[$code] = true;
                    $options[] = (object) ['value' => $code, 'text' => $code];
                }
            }
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement association country filter failed (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );
        }

        usort(
            $options,
            static fn (object $left, object $right): int => strnatcasecmp($left->text, $right->text)
        );

        return $options;
    }

    /**
     * List only existing parent associations for the administrator filter.
     *
     * The original filter was a numeric ID box labelled Federation even
     * though its query tests the association parent_id column. Read the
     * actual association hierarchy and show only nodes referenced by a
     * child in the same country. A selected country narrows the choices,
     * while the unfiltered view prefixes labels with their ISO-3 code.
     * Invalid row data and cross-country parent references are ignored;
     * errors are logged privately without breaking the list page.
     *
     * @return array<int,object> Distinct parent IDs with escaped-at-render labels.
     */
    public function getParentAssociationOptions(): array
    {
        $selected = $this->getState('filter.search_nation', '');
        $countryFilter = is_string($selected) ? strtoupper(trim($selected)) : '';

        if ($countryFilter !== '' && preg_match('/^[A-Z]{3}$/D', $countryFilter) !== 1) {
            return [];
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('parent_id'),
                    $db->quoteName('name'),
                    $db->quoteName('country'),
                ])
                ->from($db->quoteName('#__sportsmanagement_associations'))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement association parent filter failed (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return [];
        }

        if (!is_array($rows)) {
            return [];
        }

        $associations = [];
        $childParents = [];

        foreach ($rows as $row) {
            if (!is_object($row)
                || !is_string($row->country ?? null)
                || !is_string($row->name ?? null)) {
                continue;
            }

            $country = strtoupper(trim($row->country));
            $name = trim($row->name);
            $id = filter_var($row->id ?? null, FILTER_VALIDATE_INT);
            $parentId = filter_var($row->parent_id ?? null, FILTER_VALIDATE_INT);

            if ($id === false || $id <= 0
                || $parentId === false || $parentId < 0 || $name === ''
                || preg_match('/^[A-Z]{3}$/D', $country) !== 1) {
                continue;
            }

            $associations[$id] = ['country' => $country, 'name' => $name];

            if ($parentId > 0) {
                $childParents[] = ['parentId' => $parentId, 'country' => $country];
            }
        }

        $options = [];
        $seen = [];

        foreach ($childParents as $child) {
            $id = $child['parentId'];
            $parent = $associations[$id] ?? null;

            if ($parent === null || $parent['country'] !== $child['country']
                || ($countryFilter !== '' && $parent['country'] !== $countryFilter)
                || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $options[] = (object) [
                'value' => (string) $id,
                'text' => $countryFilter === ''
                    ? '(' . $parent['country'] . ') ' . $parent['name']
                    : $parent['name'],
            ];
        }

        usort(
            $options,
            static fn (object $left, object $right): int => strnatcasecmp($left->text, $right->text)
        );

        return $options;
    }

    /**
     * Retrieve associations by name and optional parent federation.
     *
     * Applies the selected country as a parameter-bound filter and returns
     * name and text labels suitable for other administrator selectors.
     *
     * @param int $federation Optional parent association ID.
     * @return array<int,object> Matching association select-list entries.
     */
    public function getAssociations(int $federation = 0): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            $query
                ->where(
                    '(' . $db->quoteName('parent_id') . ' = :associationParent'
                    . ' OR ' . $db->quoteName('id') . ' = :associationId)'
                )
                ->bind(':associationParent', $federation, ParameterType::INTEGER)
                ->bind(':associationId', $federation, ParameterType::INTEGER);
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '' && $country !== '0') {
            $query
                ->where($db->quoteName('country') . ' = :associationCountry')
                ->bind(':associationCountry', $country, ParameterType::STRING);
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
     * Import only missing selected-country associations without changing existing rows.
     *
     * The administrator's manual Import action now uses the same guarded
     * importer as component option saves and extension upgrades. In
     * particular, it never removes records of unselected countries or
     * updates administrator-edited associations. Reports newly inserted
     * records and missing bundled XML, with private error diagnostics.
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

        try {
            $selected = ComponentHelper::getParams('com_sportsmanagement')
                ->get('cfg_country_associations', []);
            $installer = new ConfiguredAssociationInstaller($this->getDatabase());
            $installed = $installer->installMissing($selected);
            $summary['inserted'] = array_sum($installed);
            $summary['missing'] = $installer->getMissingSources();

            $app = $this->administratorApplication();

            if ($installed !== []) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ASSOCIATIONS_IMPORT_INSTALLED',
                        $summary['inserted'],
                        count($installed)
                    ),
                    'message'
                );
            }

            if ($summary['missing'] !== []) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ASSOCIATIONS_IMPORT_XML_MISSING',
                        implode(', ', $summary['missing'])
                    ),
                    'warning'
                );
            }
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement association manual import failed (' . get_class($exception) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
            $summary['failed'] = true;
            $this->administratorApplication()->enqueueMessage(
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'warning'
            );
        }

        return $summary;
    }

    /**
     * Load the association search, country and parent-federation list filters.
     *
     * Preserves the historical flat request keys for existing administrator
     * links alongside Joomla's standard filter[search_nation] form setting.
     *
     * @param string $ordering Default association ordering column.
     * @param string $direction Default ascending or descending direction.
     * @return void
     */
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

    /**
     * Build the filtered native Joomla 5/6 association administrator query.
     *
     * Uses parameter binding for name, ISO-country, publication state and
     * parent association ID, and permits only predefined ORDER BY columns.
     *
     * @return mixed Native Joomla database query for the association list.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            $searchToken = '%' . $db->escape($search, true) . '%';
            $query
                ->where('LOWER(' . $db->quoteName('objassoc.name') . ') LIKE LOWER(:searchToken)')
                ->bind(':searchToken', $searchToken, ParameterType::STRING);
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '' && $country !== '0') {
            $query
                ->where($db->quoteName('objassoc.country') . ' = :filterCountry')
                ->bind(':filterCountry', $country, ParameterType::STRING);
        }

        $federation = (int) $this->getState('filter.federation', 0);

        if ($federation > 0) {
            $query
                ->where($db->quoteName('objassoc.parent_id') . ' = :federation')
                ->bind(':federation', $federation, ParameterType::INTEGER);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $publishedState = (int) $state;
            $query
                ->where($db->quoteName('objassoc.published') . ' = :publishedState')
                ->bind(':publishedState', $publishedState, ParameterType::INTEGER);
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
