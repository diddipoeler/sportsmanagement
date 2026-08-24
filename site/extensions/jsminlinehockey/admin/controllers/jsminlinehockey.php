<?php
/**
 * SportsManagement Inline Hockey legacy-extension controller bridge.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubTeamImportService;
use Joomla\Archive\Archive;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class sportsmanagementControllerjsminlinehockey extends AdminController
{
    public function getmatches(): void
    {
        $this->checkToken();

        $model = $this->getModel('jsminlinehockey');
        $model->getmatches();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=projects',
            'Spiele importiert'
        );
    }

    public function getclubs(): void
    {
        $this->checkToken();

        try {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $imported = $this->clubTeamImporter()->importClubs(
                (string) $params->get('ishd_benutzername', ''),
                (string) $params->get('ishd_kennwort', '')
            );
            $message = sprintf('%d Vereine importiert', $imported);
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            $message = 'Vereinsimport fehlgeschlagen';
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=clubs', $message);
    }

    public function getteams(): void
    {
        $this->checkToken();

        try {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $changed = $this->clubTeamImporter()->importTeams(
                (string) $params->get('ishd_benutzername', ''),
                (string) $params->get('ishd_kennwort', '')
            );
            $message = sprintf('%d Mannschaften importiert/aktualisiert', $changed);
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            $message = 'Mannschaftsimport fehlgeschlagen';
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=teams', $message);
    }

    public function save(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $post = $input->post->getArray();

        if ((int) ($post['sent'] ?? 0) !== 1) {
            return;
        }

        $upload = $input->files->get('import_package', null, 'array');

        if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->logUploadWarning('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD');

            return;
        }

        $tempFilePath = (string) ($upload['tmp_name'] ?? '');
        $uploadName = File::makeSafe(basename((string) ($upload['name'] ?? '')));
        $extractDir = JPATH_SITE . '/tmp';
        $destination = $extractDir . '/' . $uploadName;
        $importFile = $extractDir . '/ish_bw_import.xls';

        if ($tempFilePath === '' || $uploadName === '' || !File::exists($tempFilePath)) {
            $this->logUploadWarning('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD');

            return;
        }

        try {
            if (!is_dir($extractDir) && !Folder::create($extractDir)) {
                throw new \RuntimeException('Inline-Hockey temporary directory could not be created.');
            }

            if (File::exists($importFile)) {
                File::delete($importFile);
            }

            if (File::exists($destination)) {
                File::delete($destination);
            }

            if (!File::upload($tempFilePath, $destination)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'));
            }

            $extension = strtolower((string) File::getExt($destination));

            if ($extension === 'zip') {
                $archive = new Archive();

                if (!$archive->extract($destination, $extractDir)) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'));
                }

                File::delete($destination);
                $sourceFiles = Folder::files($extractDir, '\.(?:xls|ics)$', false, true);

                if (!$sourceFiles) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_NOJLG'));
                }

                $source = (string) reset($sourceFiles);

                if (!File::move($source, $importFile)) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_ERROR_RENAME'));
                }

                return;
            }

            if (!in_array($extension, ['xls', 'ics'], true)) {
                File::delete($destination);
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'));
            }

            if (!File::move($destination, $importFile)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'));
            }
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
        }
    }

    private function clubTeamImporter(): InlineHockeyClubTeamImportService
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return new InlineHockeyClubTeamImportService($db, new InlineHockeyApiClient());
    }

    private function logUploadWarning(string $languageKey): void
    {
        Log::add(Text::_($languageKey), Log::WARNING, 'jsmerror');
    }
}
