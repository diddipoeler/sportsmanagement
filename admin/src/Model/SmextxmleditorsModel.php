<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Native Joomla 5/6 model listing SportsManagement extended XML/PHP files.
 */
final class SmextxmleditorsModel extends BaseDatabaseModel
{
    public function getXMLFiles(): array
    {
        $path = JPATH_ADMINISTRATOR
            . DIRECTORY_SEPARATOR . 'components'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'extended';

        if (!is_dir($path)) {
            return [];
        }

        $files = [];

        foreach (new DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $extension = strtolower($fileInfo->getExtension());

            if (!in_array($extension, ['xml', 'php'], true)) {
                continue;
            }

            $files[] = $fileInfo->getFilename();
        }

        natcasesort($files);

        return array_values($files);
    }
}
