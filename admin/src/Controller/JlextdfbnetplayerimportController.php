<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the DFB.net CSV/ICS import workflow. */
final class JlextdfbnetplayerimportController extends BaseController
{
    public function save()
    {
        $this->checkToken();

        $app = $this->getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $post = $input->post->getArray();
        $whichFile = $input->getCmd('whichfile', '');
        $filterSeason = (int) ($post['filter_season'] ?? 0);
        $delimiter = $input->getString('delimiter', '');

        if ($whichFile === 'playerfile' && $filterSeason <= 0) {
            $this->setRedirect(
                'index.php?option=' . $option . '&view=jlextdfbnetplayerimport',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE_NO_SEASON'),
                'error'
            );

            return false;
        }

        $message = $this->getImportMessage($whichFile, !empty($post['dfbimportupdate']));

        if ($message !== '') {
            $app->enqueueMessage($message, 'notice');
        }

        if ((int) ($post['sent'] ?? 0) === 1) {
            $upload = (array) $input->files->get('import_package', []);
            $app->setUserState($option . 'lmoimportuseteams', $input->getInt('lmoimportuseteams', 0));
            $app->setUserState($option . 'whichfile', $whichFile);
            $app->setUserState($option . 'delimiter', $delimiter);
            $app->setUserState($option . 'uploadArray', $upload);

            if (!$this->prepareImportFile($upload, $option)) {
                return false;
            }
        }

        if (!empty($post['dfbimportupdate'])) {
            $this->setRedirect(
                'index.php?option=' . $option
                . '&view=jlextdfbnetplayerimport&task=jlextdfbnetplayerimport.update',
                $message,
                'notice'
            );

            return true;
        }

        $model = $this->getImportModel();
        $model->getData($post);

        $link = 'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit';

        if ($whichFile !== 'matchfile') {
            $link .= '&filter_season=' . $filterSeason;
        }

        $this->setRedirect($link, $message, 'notice');

        return true;
    }

    private function getImportMessage(string $whichFile, bool $update): string
    {
        if ($whichFile === 'playerfile') {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE');
        }

        if ($whichFile === 'matchfile') {
            return Text::_(
                $update
                    ? 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE_UPDATE'
                    : 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE'
            );
        }

        return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_ICSFILE');
    }

    private function prepareImportFile(array $upload, string $option): bool
    {
        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = File::makeSafe((string) ($upload['name'] ?? ''));

        if ($temporaryPath === '' || $originalName === '' || !File::exists($temporaryPath)) {
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'
            );
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
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'
            );
        }

        $extension = strtolower(File::getExt($destination));

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $temporaryDirectory, $importFile, $option);
        }

        if (!in_array($extension, ['csv', 'ics'], true)) {
            File::delete($destination);

            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'
            );
        }

        if (!@rename($destination, $importFile)) {
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'
            );
        }

        return true;
    }

    private function prepareArchive(
        string $archiveFile,
        string $temporaryDirectory,
        string $importFile,
        string $option
    ): bool {
        try {
            $archive = new Archive();
            $result = $archive->extract($archiveFile, $temporaryDirectory);
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');

            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'
            );
        }

        File::delete($archiveFile);

        if ($result === false) {
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'
            );
        }

        $sources = Folder::files($temporaryDirectory, '\\.(csv|ics)$', false, true);

        if (!$sources) {
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_NOJLG'
            );
        }

        if (!@rename((string) $sources[0], $importFile)) {
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_ERROR_RENAME'
            );
        }

        return true;
    }

    private function redirectUploadError(string $option, string $languageKey): bool
    {
        $message = Text::_($languageKey);
        Log::add($message, Log::WARNING, 'jsmerror');
        $this->setRedirect(
            'index.php?option=' . $option . '&view=jlextdfbnetplayerimport',
            $message,
            'error'
        );

        return false;
    }

    private function getImportModel(): object
    {
        $model = $this->getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextdfbnetplayerimport', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement DFB.net import model not found.', 500);
        }

        return $model;
    }
}
