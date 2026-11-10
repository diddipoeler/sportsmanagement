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
     * Lists the module's packaged quote-text subdirectory selected by its
     * XML filelist field, excluding symlinks, scripts and unsafe basenames.
     * The native frontend reader additionally supports old root-level TXT
     * files for existing configuration values without exposing them here.
     * A missing, symlinked or concurrently removed module directory returns
     * an empty list so Joomla 5/6 can continue rendering the administrator
     * file selection form. Results remain naturally sorted.
     *
     * @return array<int, string> Readable and validated TXT file names.
     */
    public function getTXTFiles(): array
    {
        $path = JPATH_SITE
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';

        if (!is_dir($path) || is_link($path)) {
            return [];
        }

        $files = [];

        try {
            // Joomla upgrades can replace the module directory between
            // is_dir() and iterator construction. Keep the administrator
            // file picker usable instead of throwing an SPL exception.
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
        } catch (\UnexpectedValueException) {
            // The module files are optional. Missing or inaccessible folders
            // should produce an empty file list without a PHP fatal error.
            return [];
        }

        natcasesort($files);

        return array_values($files);
    }
}
