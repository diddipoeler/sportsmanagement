<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the DBB CSV/ICS import workflow. */
final class JlextdbbimportController extends BaseController
{
    public function save()
    {
        $this->checkToken();

        $app = $this->getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $post = $input->post->getArray();
        $whichFile = $input->getCmd('whichfile', '');
        $delimiter = $input->getString('delimiter', '');
        $model = $this->getImportModel();

        if ($whichFile === 'playerfile') {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_PLAYERFILE'), Log::NOTICE, 'jsmerror');
        } elseif ($whichFile === 'matchfile') {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE'), Log::NOTICE, 'jsmerror');

            if (!empty($post['dfbimportupdate'])) {
                Log::add(
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE_UPDATE'),
                    Log::NOTICE,
                    'jsmerror'
                );
            }
        }

        if ((int) ($post['sent'] ?? 0) === 1) {
            $upload = (array) $input->files->get('import_package', []);
            $app->setUserState($option . 'lmoimportuseteams', $input->getInt('lmoimportuseteams', 0));
            $app->setUserState($option . 'whichfile', $whichFile);
            $app->setUserState($option . 'delimiter', $delimiter);
            $app->setUserState($option . 'uploadArray', $upload);

            if (!$this->prepareImportFile($upload)) {
                return false;
            }
        }

        if (!empty($post['dfbimportupdate'])) {
            $this->setRedirect(
                'index.php?option=' . $option . '&view=jlextdfbnetplayerimport&task=jlextdbbimport.update'
            );

            return true;
        }

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
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_CANT_UPLOAD'), Log::NOTICE, 'jsmerror');

            return false;
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        $destination = $temporaryDirectory . '/' . $originalName;
        $importFile = $temporaryDirectory . '/sportsmanagement_import.csv';

        if (File::exists($importFile)) {
            File::delete($importFile);
        }

        if (File::exists($destination)) {
            File::delete($destination);
        }

        if (!File::upload($temporaryPath, $destination)) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_CANT_UPLOAD'), Log::NOTICE, 'jsmerror');

            return false;
        }

        $extension = strtolower(File::getExt($destination));

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $temporaryDirectory, $importFile);
        }

        if (!in_array($extension, ['csv', 'ics'], true)) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_WRONG_EXTENSION'), Log::NOTICE, 'jsmerror');
            File::delete($destination);

            return false;
        }

        if (!@rename($destination, $importFile)) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_RENAME_FAILED'), Log::NOTICE, 'jsmerror');

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
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_EXTRACT_ERROR'), Log::NOTICE, 'jsmerror');

            return false;
        }

        $sources = Folder::files($temporaryDirectory, '\\.(csv|ics)$', false, true);

        if (!$sources) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_EXTRACT_NOJLG'), Log::NOTICE, 'jsmerror');

            return false;
        }

        if (!@rename((string) $sources[0], $importFile)) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_ERROR_RENAME'), Log::NOTICE, 'jsmerror');

            return false;
        }

        return true;
    }

    private function getImportModel(): object
    {
        $model = $this->getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextdbbimport', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement DBB import model not found.', 500);
        }

        return $model;
    }
}
