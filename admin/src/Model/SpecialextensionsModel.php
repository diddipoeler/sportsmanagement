<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 list model for installed SportsManagement extensions.
 */
final class SpecialextensionsModel extends SportsManagementListModel
{
    protected function getListQuery()
    {
        // This screen is filesystem-backed and does not render database items.
        return $this->getDatabase()->getQuery(true)->select('1 AS id')->where('1 = 0');
    }

    public function getSpecialExtensions(): array
    {
        $path = JPATH_SITE . '/components/com_sportsmanagement/extensions';
        if (!is_dir($path) || !is_readable($path)) {
            return [];
        }

        $extensions = [];

        try {
            foreach (new \DirectoryIterator($path) as $entry) {
                if ($entry->isDot() || !$entry->isDir()) {
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
