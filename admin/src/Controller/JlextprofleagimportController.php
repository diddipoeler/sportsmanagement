<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the ProfiLeague XML import workflow. */
final class JlextprofleagimportController extends BaseController
{
    public function save()
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $post = $input->post->getArray();
        $importFile = JPATH_SITE . '/tmp/sportsmanagement_import.xml';

        if ((int) ($post['sent'] ?? 0) === 1) {
            $upload = (array) $input->files->get('import_package', []);
            $useTeams = $input->getInt('lmoimportuseteams', 0);
            $app->setUserState($option . 'lmoimportuseteams', $useTeams);
            $app->setUserState($option . 'uploadArray', $upload);

            if (!$this->prepareImportFile($upload, $importFile)) {
                return false;
            }
        }

        if (!File::exists($importFile)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $source = file_get_contents($importFile);

        if ($source === false) {
            return false;
        }

        $source = $this->normaliseSource((string) $source);

        if (!File::write($importFile, $source)) {
            return false;
        }

        $model = $this->getImportModel();
        $model->getData();

        $this->setRedirect(
            'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit'
        );

        return true;
    }

    private function prepareImportFile(array $upload, string $importFile): bool
    {
        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = File::makeSafe((string) ($upload['name'] ?? ''));

        if ($temporaryPath === '' || $originalName === '' || !File::exists($temporaryPath)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        $destination = $temporaryDirectory . '/' . $originalName;

        if (File::exists($importFile)) {
            File::delete($importFile);
        }

        if (File::exists($destination)) {
            File::delete($destination);
        }

        if (!File::upload($temporaryPath, $destination)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $extension = strtolower(File::getExt($destination));

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $temporaryDirectory, $importFile);
        }

        if ($extension !== 'xml') {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_WRONG_EXTENSION'),
                Log::WARNING,
                'jsmerror'
            );
            File::delete($destination);

            return false;
        }

        if (!@rename($destination, $importFile)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_RENAME_FAILED'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        return true;
    }

    private function prepareArchive(string $archiveFile, string $temporaryDirectory, string $importFile): bool
    {
        try {
            $archive = new Archive();
            $result = $archive->extract($archiveFile, $temporaryDirectory);
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');

            return false;
        }

        File::delete($archiveFile);

        if ($result === false) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_ERROR'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $sources = Folder::files($temporaryDirectory, '\\.xml$', false, true);

        if (!$sources) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_NOJLG'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        if (!@rename((string) $sources[0], $importFile)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_ERROR_RENAME'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        return true;
    }

    private function normaliseSource(string $source): string
    {
        $source = str_replace(
            [
                'charset=',
                'encoding="ISO-8859-1"',
                '<h2>',
                '</h2>',
                '<h3>',
                '</h3>',
                '<br>',
                '</br>',
                '<b>',
                '</b>',
                '<![CDATA[',
                ']]>',
            ],
            [
                '',
                'encoding="utf-8"',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            $source
        );

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($source, 'UTF-8', 'ISO-8859-1');
        }

        if (function_exists('iconv')) {
            $converted = iconv('ISO-8859-1', 'UTF-8//IGNORE', $source);

            if ($converted !== false) {
                return $converted;
            }
        }

        return $source;
    }

    private function getImportModel(): object
    {
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextprofleagimport', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement ProfiLeague import model not found.', 500);
        }

        return $model;
    }
}
