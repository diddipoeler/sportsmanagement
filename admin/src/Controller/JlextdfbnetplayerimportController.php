<?php
/**
 * Native Joomla 5/6 controller for the DFB.net CSV/ICS import workflow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextdfbnetplayerimportModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\LegacyImportZipService;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the DFB.net CSV/ICS import workflow. */
final class JlextdfbnetplayerimportController extends BaseController
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
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'
            );
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        // Never overwrite another upload with the same user-supplied name.
        $destination = $temporaryDirectory . '/jsm-upload-' . bin2hex(random_bytes(12)) . '.' . $extension;
        $importFile = $temporaryDirectory . '/sportsmanagement_import.csv';

        if (is_link($importFile)) {
            return false;
        }

        if (!File::upload($temporaryPath, $destination)) {
            return $this->redirectUploadError(
                $option,
                'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'
            );
        }

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $importFile, $option);
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

    private function prepareArchive(string $archiveFile, string $importFile, string $option): bool
    {
        // Read a single supported ZIP entry directly. Never extract into
        // Joomla's shared tmp folder or pick up a stale file left there.
        try {
            $staged = LegacyImportZipService::stage(
                $archiveFile,
                $importFile,
                ['csv', 'ics']
            );
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            $staged = false;
        } finally {
            File::delete($archiveFile);
        }

        if (!$staged) {
            return $this->redirectUploadError($option, 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR');
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

    private function getImportModel(): JlextdfbnetplayerimportModel
    {
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextdfbnetplayerimport', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof JlextdfbnetplayerimportModel) {
            throw new \RuntimeException('SportsManagement DFB.net import model not found.', 500);
        }

        return $model;
    }
}
