<?php
/**
 * Joomla 5/6 administrator quote source-files list model.
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
 * Native Joomla 5/6 model for listing random-quote module source files.
 */
final class SmquotestxtModel extends BaseDatabaseModel
{
    /**
     * Enumerate only usable text quote files from the frontend module folder.
     *
     * Limits enumeration to readable regular files in the module directory,
     * excludes symlinks and executable PHP sources, and applies the same
     * basename allowlist as the native module's TXT reader. The resulting
     * filenames are ordered naturally for the Joomla administrator picker.
     *
     * @return array<int, string> Readable and validated TXT file names.
     */
    public function getTXTFiles(): array
    {
        $path = JPATH_SITE
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';

        if (!is_dir($path)) {
            return [];
        }

        $files = [];

        foreach (new DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isFile() || $fileInfo->isLink() || !$fileInfo->isReadable()) {
                continue;
            }

            $name = $fileInfo->getFilename();

            if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\.txt$/iD', $name)
                || str_contains($name, '..')) {
                continue;
            }

            $files[] = $name;
        }

        natcasesort($files);

        return array_values($files);
    }
}
