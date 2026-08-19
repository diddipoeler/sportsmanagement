<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Native Joomla 5/6 model for listing random-quote module source files.
 */
final class SmquotestxtModel extends BaseDatabaseModel
{
    public function getTXTFiles(): array
    {
        $path = JPATH_SITE
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';

        if (!is_dir($path)) {
            return [];
        }

        $files = [];

        foreach (new DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $extension = strtolower($fileInfo->getExtension());

            if (!in_array($extension, ['txt', 'php'], true)) {
                continue;
            }

            $files[] = $fileInfo->getFilename();
        }

        natcasesort($files);

        return array_values($files);
    }
}
