<?php
/**
 * Joomla 5/6 federations list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;

/**
 * Joomla 5/6-native replacement for the historical federationslist field.
 *
 * The league filter keeps the old top-level-only behaviour. Other edit views
 * retain the hierarchical federation tree when an item is being edited.
 */
final class FederationslistField extends SportsManagementListField
{
    protected $type = 'FederationsList';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Select federation list data appropriate for the administrator view.
     *
     * League lists display only root federations, while edit views with a
     * valid record identifier render the complete hierarchy. Normalises
     * a single legacy array ID safely and rejects malformed nested IDs
     * rather than converting them into unrelated records.
     *
     * @return array<int,object> Joomla inherited and federation choices.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $input = $app->getInput();
        $view = $input->getCmd('view');
        $selectedId = $input->get('id', 0, 'raw');

        if (is_array($selectedId)) {
            $selectedId = array_is_list($selectedId) && count($selectedId) === 1
                ? $selectedId[0] : null;
        }

        $selectedId = self::recordId($selectedId) ?? 0;

        if ($view === 'leagues') {
            return array_merge(parent::getOptions(), $this->getTopLevelOptions());
        }

        if ($selectedId <= 0) {
            return parent::getOptions();
        }

        return array_merge(parent::getOptions(), $this->getTreeOptions());
    }

    /**
     * Read root-level federations for a league listing.
     *
     * Queries federation rows whose parent is zero, preserving their
     * name ordering and Joomla's value/text select-field representation.
     *
     * @return array<int,object> Root federation options.
     */
    private function getTopLevelOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return $options;
    }

    /**
     * Construct a validated federation hierarchy for editing forms.
     *
     * Groups only records with valid numeric ID and parent references,
     * preserving their stored order. Recursion tracks already included
     * IDs so corrupted duplicate and circular records cannot fill
     * the selector or trigger repeated branches.
     *
     * @return array<int,object> Indented federation hierarchy options.
     */
    private function getTreeOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('parent_id'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->order([
                $db->quoteName('ordering'),
                $db->quoteName('name'),
            ]);
        $db->setQuery($query);

        $children = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item) || !is_string($item->name ?? null)) {
                continue;
            }
            $id = self::recordId($item->id ?? null);
            $parent = self::recordId($item->parent_id ?? null, true);

            if ($id === null || $parent === null) {
                continue;
            }

            $children[$parent][] = $item;
        }

        $options = [];
        $visited = [];
        $this->appendChildren($options, $children, 0, 0, $visited);

        return $options;
    }

    /**
     * Append federation descendants with a bounded, cycle-safe traversal.
     *
     * Indents descendants using the legacy display style, keeps the
     * maximum depth at ten, and skips IDs already emitted from another
     * branch of possibly inconsistent imported hierarchy data.
     *
     * @param array<int,object> $options Output choices modified in place.
     * @param array<int,array<int,object>> $children Records grouped by parent.
     * @param int $parentId Current parent federation identifier.
     * @param int $level Current indentation depth.
     * @param array<int,bool> $visited Federation IDs already emitted.
     * @return void
     */
    private function appendChildren(array &$options, array $children, int $parentId, int $level, array &$visited): void
    {
        if ($level > 10 || empty($children[$parentId])) {
            return;
        }

        foreach ($children[$parentId] as $item) {
            $id = self::recordId($item->id ?? null);

            if ($id === null || isset($visited[$id])) {
                continue;
            }

            $visited[$id] = true;
            $indent = str_repeat('...', $level);
            $prefix = $level > 0 ? "\u{00A0}" : '';
            $options[] = (object) [
                'value' => (string) $id,
                'text' => $indent . $prefix . (string) $item->name,
            ];
            $this->appendChildren($options, $children, $id, $level + 1, $visited);
        }
    }

    /**
     * Normalise a federation record or parent ID without partial conversion.
     *
     * Accepts positive integers and complete zero-padded decimal strings.
     * Zero is permitted only for the root parent. Returns null for invalid
     * arrays, booleans, negatives, truncated strings and overflow.
     *
     * @param mixed $raw Stored federation identifier or form input.
     * @param bool $allowZero Whether zero represents the hierarchy root.
     * @return int|null Valid identifier or null for malformed values.
     */
    private static function recordId(mixed $raw, bool $allowZero = false): ?int
    {
        if (is_int($raw)) {
            return $raw > 0 || ($allowZero && $raw === 0) ? $raw : null;
        }

        if (!is_string($raw) || $raw === '' || strlen($raw) > 64
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return null;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return $allowZero ? 0 : null;
        }

        $value = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $value === false ? null : $value;
    }
}
