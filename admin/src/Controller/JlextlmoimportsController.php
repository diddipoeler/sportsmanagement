<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
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

        $app = $this->getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
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

        if ($temporaryPath === '' || $originalName === '' || !File::exists($temporaryPath)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_CANT_UPLOAD'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        $destination = $temporaryDirectory . '/' . $originalName;
        $importFile = $temporaryDirectory . '/sportsmanagement_import.l98';

        if (File::exists($importFile)) {
            File::delete($importFile);
        }

        if (File::exists($destination)) {
            File::delete($destination);
        }

        if (!File::upload($temporaryPath, $destination)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_CANT_UPLOAD'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        $extension = strtolower(File::getExt($destination));

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $temporaryDirectory, $importFile);
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

    private function prepareArchive(string $archiveFile, string $temporaryDirectory, string $importFile): bool
    {
        try {
            $archive = new Archive();
            $result = $archive->extract($archiveFile, $temporaryDirectory);
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::NOTICE, 'jsmerror');

            return false;
        }

        File::delete($archiveFile);

        if ($result === false) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_ERROR'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        $sources = Folder::files($temporaryDirectory, '\\.l98$', false, true);

        if (!$sources) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_NOJLG'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        if (!@rename((string) $sources[0], $importFile)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_ERROR_RENAME'),
                Log::NOTICE,
                'jsmerror'
            );

            return false;
        }

        return true;
    }

    private function getImportModel(): object
    {
        $model = $this->getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextlmoimports', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement LMO import model not found.', 500);
        }

        return $model;
    }
}
