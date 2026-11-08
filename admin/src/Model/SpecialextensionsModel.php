<?php
/**
 * Joomla 5/6 administrator special-extensions list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list model for installed SportsManagement extensions.
 */
final class SpecialextensionsModel extends SportsManagementListModel
{
    /**
     * Supply a deliberately empty query for a filesystem-backed list view.
     *
     * No SportsManagement database table contains these extension folders;
     * the false WHERE condition keeps Joomla pagination and list-model
     * plumbing compatible without accidentally fetching unrelated records.
     *
     * @return mixed Joomla query with no matching rows.
     */
    protected function getListQuery()
    {
        // This screen is filesystem-backed and does not render database items.
        return $this->getDatabase()->createQuery()->select('1 AS id')->where('1 = 0');
    }

    /**
     * Enumerate installed SportsManagement extension directories safely.
     *
     * Reads only direct child directories under the Joomla site component
     * extension folder and rejects symbolic links so a listed entry cannot
     * reference an unrelated filesystem location. Names are naturally
     * sorted for the administrator UI; unreadable folders return no items.
     *
     * @return array<int, string> Local extension directory names.
     */
    public function getSpecialExtensions(): array
    {
        $path = JPATH_SITE . '/components/com_sportsmanagement/extensions';
        if (!is_dir($path) || is_link($path) || !is_readable($path)) {
            return [];
        }

        $extensions = [];

        try {
            foreach (new \DirectoryIterator($path) as $entry) {
                if ($entry->isDot() || !$entry->isDir() || $entry->isLink()) {
                    continue;
                }

                $extensions[] = $entry->getFilename();
            }
        } catch (\UnexpectedValueException) {
            return [];
        }

        natcasesort($extensions);

        return array_values($extensions);
    }
}
