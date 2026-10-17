<?php
/**
 * Native Joomla 5/6 controller for the legacy LMO import workflow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextlmoimportsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\LegacyImportZipService;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Native Joomla 5/6 controller for the legacy LMO import workflow.
 */
final class JlextlmoimportsController extends BaseController
{
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

        if ((int) ($post['sent'] ?? 0) === 1) {
            $upload = (array) $input->files->get('import_package', []);
            $useTeams = $input->getInt('lmoimportuseteams', 0);
            $app->setUserState($option . 'lmoimportuseteams', $useTeams);
            $app->setUserState($option . 'uploadArray', $upload);

            if (!$this->prepareImportFile($upload)) {
                return false;
            }
        }

        $model = $this->getImportModel();
        $model->getData();

        $this->setRedirect(
            'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit'
        );

        return true;
    }

    private function prepareImportFile(array $upload): bool
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
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_CANT_UPLOAD'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        // Never overwrite another upload with the same user-supplied name.
        $destination = $temporaryDirectory . '/jsm-upload-' . bin2hex(random_bytes(12)) . '.' . $extension;
        $importFile = $temporaryDirectory . '/sportsmanagement_import.l98';

        if (is_link($importFile)) {
            return false;
        }

        if (!File::upload($temporaryPath, $destination)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_CANT_UPLOAD'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $importFile);
        }

        if ($extension !== 'l98') {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_WRONG_EXTENSION'),
                Log::NOTICE,
                'jsmerror'
            );
            File::delete($destination);

            return false;
        }

        if (!@rename($destination, $importFile)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_RENAME_FAILED'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        return true;
    }

    private function prepareArchive(string $archiveFile, string $importFile): bool
    {
        // Read a single supported ZIP entry directly. Never extract into
        // Joomla's shared tmp folder or pick up a stale file left there.
        try {
            $staged = LegacyImportZipService::stage(
                $archiveFile,
                $importFile,
                ['l98']
            );
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::NOTICE, 'jsmerror');
            $staged = false;
        } finally {
            File::delete($archiveFile);
        }

        if (!$staged) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_ERROR'), Log::NOTICE, 'jsmerror');

            return false;
        }

        return true;
    }

    private function getImportModel(): JlextlmoimportsModel
    {
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextlmoimports', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof JlextlmoimportsModel) {
            throw new \RuntimeException('SportsManagement LMO import model not found.', 500);
        }

        return $model;
    }
}
