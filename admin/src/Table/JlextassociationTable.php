<?php
/**
 * Joomla 5/6 administrator association table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class JlextassociationTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla association table on the selected database.
     *
     * @param DatabaseInterface $db Shared SportsManagement database.
     * @param bool $resolved Whether the supplied connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_associations', 'id', $db, $resolved);
    }

    /**
     * Refuse parents from another country, self-links and hierarchical cycles.
     *
     * A parent field selected in the administrator form can reference an
     * existing association, but it must belong to the same country and must
     * not contain the row being saved in its ancestor chain. A bounded read
     * of the stored parent chain protects both new records and existing
     * associations against corrupted or cross-country references.
     *
     * @return bool Whether the proposed association and hierarchy are valid.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) ($this->name ?? ''));

        $rawParent = $this->parent_id ?? 0;
        $parentId = $rawParent === '' || $rawParent === null
            ? 0 : filter_var($rawParent, FILTER_VALIDATE_INT);

        if ($parentId === false || $parentId < 0) {
            return $this->rejectInvalidParent();
        }

        if ($parentId === 0) {
            return true;
        }

        $country = is_string($this->country ?? null)
            ? strtoupper(trim($this->country)) : '';

        if (preg_match('/^[A-Z]{3}$/D', $country) !== 1) {
            return $this->rejectInvalidParent();
        }

        $id = (int) ($this->id ?? 0);
        $seen = [];
        $db = $this->getDatabase();

        try {
            // A bounded ancestor lookup is sufficient even for long legacy
            // trees; cycles and missing rows must never be silently accepted.
            for ($depth = 0; $depth < 256; $depth++) {
                if ($parentId === 0) {
                    return true;
                }

                if (($parentId === $id && $id > 0) || isset($seen[$parentId])) {
                    return $this->rejectInvalidParent();
                }

                $seen[$parentId] = true;
                $query = $db->createQuery()
                    ->select([
                        $db->quoteName('country'),
                        $db->quoteName('parent_id'),
                    ])
                    ->from($db->quoteName('#__sportsmanagement_associations'))
                    ->where($db->quoteName('id') . ' = :associationParentId')
                    ->bind(':associationParentId', $parentId, ParameterType::INTEGER);
                $db->setQuery($query);
                $row = $db->loadObject();

                if (!is_object($row) || !is_string($row->country ?? null)
                    || strtoupper(trim($row->country)) !== $country) {
                    return $this->rejectInvalidParent();
                }

                $nextParent = filter_var($row->parent_id ?? null, FILTER_VALIDATE_INT);

                if ($nextParent === false || $nextParent < 0) {
                    return $this->rejectInvalidParent();
                }

                $parentId = $nextParent;
            }
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement association parent validation failed ('
                    . get_class($exception) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
        }

        return $this->rejectInvalidParent();
    }

    /**
     * Add a translated validation error without exposing database details.
     *
     * @return bool False so Joomla will not save an invalid parent reference.
     */
    private function rejectInvalidParent(): bool
    {
        $this->setError(Text::_('COM_SPORTSMANAGEMENT_ASSOCIATION_INVALID_PARENT'));

        return false;
    }
}
