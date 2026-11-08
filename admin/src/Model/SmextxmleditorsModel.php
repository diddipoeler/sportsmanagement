<?php
/**
 * Joomla 5/6 administrator extended XML/PHP files list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Native Joomla 5/6 model listing SportsManagement extended XML/PHP files.
 */
final class SmextxmleditorsModel extends BaseDatabaseModel
{
    /**
     * Enumerate readable extended XML and PHP editor files from Joomla.
     *
     * Only first-level regular files with an approved basename and an XML
     * or PHP extension are returned. Symbolic links, unreadable files and
     * traversal-like names are ignored; a missing or replaced directory
     * yields an empty list instead of breaking administrator navigation.
     *
     * @return array<int, string> Naturally sorted local editor filenames.
     */
    public function getXMLFiles(): array
    {
        $path = JPATH_ADMINISTRATOR
            . DIRECTORY_SEPARATOR . 'components'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'extended';

        if (!is_dir($path) || is_link($path) || !is_readable($path)) {
            return [];
        }

        $files = [];

        try {
            foreach (new DirectoryIterator($path) as $fileInfo) {
                if (!$fileInfo->isFile() || $fileInfo->isLink() || !$fileInfo->isReadable()) {
                    continue;
                }

                $name = $fileInfo->getFilename();

                if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\\.(?:xml|php)$/iD', $name)
                    || str_contains($name, '..')) {
                    continue;
                }

                $files[] = $name;
            }
        } catch (\UnexpectedValueException) {
            return [];
        }

        natcasesort($files);

        return array_values($files);
    }
}
