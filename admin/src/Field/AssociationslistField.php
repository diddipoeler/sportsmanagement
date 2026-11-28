<?php
/**
 * Joomla 5/6 native associations list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

/** Joomla 5/6-native replacement for the historical associationslist field. */
final class AssociationslistField extends SportsManagementListField
{
    protected $type = 'AssociationsList';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Show valid associations for the active administrator selector.
     *
     * The association edit form now includes potential parents even for a new
     * association with ID 0. Its initial choices include country-prefixed
     * entries; after a country is submitted only that country's hierarchy is
     * shown. Editing an existing association excludes itself and descendants
     * so the form cannot suggest a self-referential parent chain.
     *
     * @return array<int,object> Joomla select field options.
     */
    protected function getOptions(): array
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $input = $app->getInput();
        $view = $input->getCmd('view');
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $filter = $input->post->get('filter', [], 'array');

        $rawCountry = is_array($filter) ? ($filter['search_nation'] ?? '') : null;
        if (!is_string($rawCountry)) {
            return parent::getOptions();
        }
        $country = trim($rawCountry);

        if ($view === 'projects') {
            $country = $country !== ''
                ? $country
                : trim((string) $app->getUserState($option . '.projects_search_nation', ''));

            return array_merge(parent::getOptions(), $this->getCountryOptions($country, false));
        }

        if ($view === 'leagues' || $view === 'clubs') {
            $country = $country !== ''
                ? $country
                : trim((string) $app->getUserState($option . '.clubnation', ''));

            return array_merge(parent::getOptions(), $this->getCountryOptions($country, true));
        }

        $selectedId = $input->get('id', 0, 'raw');

        if (is_array($selectedId)) {
            $selectedId = array_is_list($selectedId) && count($selectedId) === 1 ? $selectedId[0] : null;
        }

        $selectedId = (is_int($selectedId) || is_string($selectedId))
            ? filter_var($selectedId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : false;
        if ($selectedId === false) {
            return parent::getOptions();
        }

        if ($selectedId <= 0) {
            if ($view !== 'jlextassociation') {
                return parent::getOptions();
            }

            // The Joomla edit form has not saved its own country yet.
            // Offer existing countries with a visible ISO-3 prefix initially;
            // on a submitted form use its validated selected country.
            $form = $input->post->get('jform', [], 'array');
            $rawCountry = is_array($form) ? ($form['country'] ?? null) : null;
            $submittedCountry = is_string($rawCountry) ? strtoupper(trim($rawCountry)) : '';

            if ($submittedCountry !== ''
                && preg_match('/^[A-Z]{3}$/D', $submittedCountry) === 1) {
                return array_merge(parent::getOptions(), $this->getCountryTreeOptions($submittedCountry));
            }

            return array_merge(parent::getOptions(), $this->getAllAssociationOptions());
        }

        $targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targettable'] ?? ''));

        if ($targetTable === '') {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('country'))
            ->from($db->quoteName('#__sportsmanagement_' . $targetTable))
            ->where($db->quoteName('id') . ' = :selectedId')
            ->bind(':selectedId', $selectedId, ParameterType::INTEGER);
        $db->setQuery($query);
        $country = trim((string) $db->loadResult());

        if ($country === '') {
            return parent::getOptions();
        }

        return array_merge(
            parent::getOptions(),
            $this->getCountryTreeOptions($country, $view === 'jlextassociation' ? $selectedId : 0)
        );
    }

    /**
     * List country-labelled parent candidates when creating a new association.
     *
     * At that point no source row has been inserted, so the existing
     * getCountryTreeOptions() lookup by row ID cannot work. Only validated
     * association IDs and three-letter country codes become form choices.
     *
     * @return array<int,object> Safe existing associations, labelled by country.
     */
    private function getAllAssociationOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations'))
            ->order([$db->quoteName('country'), $db->quoteName('name')]);
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $row) {
            if (!is_object($row)
                || !is_string($row->country ?? null)
                || !is_string($row->name ?? null)) {
                continue;
            }

            $id = filter_var($row->id ?? null, FILTER_VALIDATE_INT);
            $code = strtoupper(trim($row->country));
            $name = trim($row->name);

            if ($id === false || $id <= 0 || $name === ''
                || preg_match('/^[A-Z]{3}$/D', $code) !== 1) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $id,
                'text' => '(' . $code . ') ' . $name,
            ];
        }

        return $options;
    }

    /**
     * Retrieve association options within an explicitly selected country.
     *
     * @param string $country ISO-3 country identifier.
     * @param bool $topLevelOnly Whether to limit to root-level associations.
     * @return array<int,object> Matching option records.
     */
    private function getCountryOptions(string $country, bool $topLevelOnly): array
    {
        if ($country === '') {
            return [];
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations'))
            ->where($db->quoteName('country') . ' = :country')
            ->order($db->quoteName('name'))
            ->bind(':country', $country, ParameterType::STRING);

        if ($topLevelOnly) {
            $query->where($db->quoteName('parent_id') . ' = 0');
        }

        $db->setQuery($query);
        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return $options;
    }

    /**
     * Build the country-specific association tree for form selections.
     *
     * The active row and all its children can be omitted from this chooser
     * when selecting a parent for an already existing association.
     *
     * @param string $country ISO-3 country code.
     * @param int $excludeId Edited association whose subtree must be hidden.
     * @return array<int,object> Indented parent association options.
     */
    private function getCountryTreeOptions(string $country, int $excludeId = 0): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('parent_id'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations'))
            ->where($db->quoteName('country') . ' = :country')
            ->order([
                $db->quoteName('ordering'),
                $db->quoteName('name'),
            ])
            ->bind(':country', $country, ParameterType::STRING);
        $db->setQuery($query);

        $children = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $children[(int) $item->parent_id][] = $item;
        }

        $options = [];
        $this->appendChildren($options, $children, 0, 0, $excludeId);

        return $options;
    }

    /**
     * Traverse and indent valid association children without offering cycles.
     *
     * @param array<int,object> $options Accumulated Joomla select options.
     * @param array<int,array<int,object>> $children Country tree indexed by parent ID.
     * @param int $parentId Parent currently being visited.
     * @param int $level Depth used for an indented label.
     * @param int $excludeId Association subtree excluded from parent choices.
     * @return void
     */
    private function appendChildren(
        array &$options,
        array $children,
        int $parentId,
        int $level,
        int $excludeId
    ): void
    {
        if ($level > 10 || empty($children[$parentId])) {
            return;
        }

        foreach ($children[$parentId] as $item) {
            $id = (int) $item->id;

            if ($id <= 0 || $id === $excludeId) {
                continue;
            }

            $indent = str_repeat('...', $level);
            $prefix = $level > 0 ? "\u{00A0}" : '';
            $options[] = (object) [
                'value' => (string) $id,
                'text' => $indent . $prefix . (string) $item->name,
            ];
            $this->appendChildren($options, $children, $id, $level + 1, $excludeId);
        }
    }
}
