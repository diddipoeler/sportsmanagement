<?php
/**
 * Native Joomla 5/6 controller for the ProfiLeague XML import workflow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextprofleagimportModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\LegacyImportZipService;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the ProfiLeague XML import workflow. */
final class JlextprofleagimportController extends BaseController
{
    /**
     * Start a ProfiLeague XML import from uploaded or existing staging data.
     * Requires a valid Joomla form token and component admin permission,
     * normalises the staged source and delegates loading to the importer.
     * @return bool Whether the import workflow advanced.
     */
    public function save()
    {
        $this->checkToken();

        // CSRF validation is not an authorization decision. Importers can
        // persist data and replace import files, so require component admin.
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $app = $this->app;
        $input = $app->getInput();
        $option = 'com_sportsmanagement';
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

    /**
     * Validate and stage an uploaded ProfiLeague XML document.
     * Accepts bounded XML or ZIP inputs, avoids symlink destinations and
     * uses a random temporary upload name before moving the file.
     * @param array $upload Joomla uploaded file metadata.
     * @param string $importFile XML staging destination.
     * @return bool Whether a valid document was staged.
     */
    private function prepareImportFile(array $upload, string $importFile): bool
    {
        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = File::makeSafe((string) ($upload['name'] ?? ''));

        $extension = strtolower(File::getExt($originalName));
        $size = $temporaryPath !== '' && is_file($temporaryPath) ? filesize($temporaryPath) : false;
        $maxBytes = $extension === 'zip' ? 32 * 1024 * 1024 : 25 * 1024 * 1024;

        if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK
            || $temporaryPath === ''
            || $originalName === ''
            || $size === false
            || $size < 1
            || $size > $maxBytes
            || is_link($temporaryPath)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        // Never overwrite another upload with the same user-supplied name.
        $destination = $temporaryDirectory . '/jsm-upload-' . bin2hex(random_bytes(12)) . '.' . $extension;

        if (is_link($importFile)) {
            return false;
        }

        if (!File::upload($temporaryPath, $destination)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $importFile);
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

    /**
     * Stage an XML entry from a ProfiLeague ZIP upload.
     * Avoids arbitrary archive extraction and deletes the staged ZIP.
     * @param string $archiveFile Temporary ZIP archive path.
     * @param string $importFile Destination XML import path.
     * @return bool Whether the supported XML entry was staged.
     */
    private function prepareArchive(string $archiveFile, string $importFile): bool
    {
        // Read a single supported ZIP entry directly. Never extract into
        // Joomla's shared tmp folder or pick up a stale file left there.
        try {
            $staged = LegacyImportZipService::stage(
                $archiveFile,
                $importFile,
                ['xml']
            );
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            $staged = false;
        } finally {
            File::delete($archiveFile);
        }

        if (!$staged) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_ERROR'), Log::WARNING, 'jsmerror');

            return false;
        }

        return true;
    }

    /**
     * Convert legacy ProfiLeague source markup to import-friendly UTF-8.
     * Removes historical HTML wrappers and converts ISO-8859-1 when the
     * required PHP encoding extension is available.
     * @param string $source Raw imported document content.
     * @return string Normalised import document.
     */
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

    /**
     * Resolve and validate the native ProfiLeague administrator model.
     * @return JlextprofleagimportModel Native importer model.
     * @throws \RuntimeException If the model is unavailable.
     */
    private function getImportModel(): JlextprofleagimportModel
    {
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextprofleagimport', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof JlextprofleagimportModel) {
            throw new \RuntimeException('SportsManagement ProfiLeague import model not found.', 500);
        }

        return $model;
    }
}
