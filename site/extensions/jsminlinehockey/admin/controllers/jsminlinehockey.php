<?php
/**
 * SportsManagement Inline Hockey legacy-extension controller bridge.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubLogoService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubTeamImportService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyMatchImportService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
use Joomla\Archive\Archive;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class sportsmanagementControllerjsminlinehockey extends AdminController
{
    /**
     * Import an Inline Hockey project's match records and synchronise club logos.
     * Checks the CSRF token, reads request and component settings, calls the native match and logo services, and redirects with a translated result.
     * @return void Redirects to the project list.
     */
    public function getmatches(): void
    {
        $this->checkToken();

        try {
            $input = $this->app->getInput();
            $projectId = $input->post->getInt('projectid', $input->getInt('pid', 0));
            $matchLink = $input->post->getString('matchlink', '');
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $username = (string) $params->get('ishd_benutzername', '');
            $password = (string) $params->get('ishd_kennwort', '');
            $changed = $this->matchImporter()->importMatches(
                $projectId,
                $matchLink,
                $username,
                $password
            );
            $logos = 0;

            try {
                $logos = $this->logoImporter()->syncProjectLogos(
                    $projectId,
                    $matchLink,
                    $username,
                    $password
                );
            } catch (\Throwable $exception) {
                Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            }

            $message = sprintf(
                '%d Spiele importiert/aktualisiert, %d Vereinslogos synchronisiert',
                $changed,
                $logos
            );
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            $message = 'Spielimport fehlgeschlagen';
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=projects', $message);
    }

    /**
     * Import clubs from the Inline Hockey API.
     * Checks the CSRF token, loads configured credentials, delegates to the club importer and redirects to the Joomla club list.
     * @return void Redirects after the import.
     */
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

    /**
     * Import or update Inline Hockey team records.
     * Validates the request token, uses configured credentials and delegates synchronization to the namespaced import service.
     * @return void Redirects to the team list.
     */
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

    /**
     * Import or update Inline Hockey player records.
     * Checks the request token, invokes the native player importer and logs any service failure before redirecting.
     * @return void Redirects to the player list.
     */
    public function getplayers(): void
    {
        $this->checkToken();

        try {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $changed = $this->clubTeamImporter()->importPlayers(
                (string) $params->get('ishd_benutzername', ''),
                (string) $params->get('ishd_kennwort', '')
            );
            $message = sprintf('%d Spieler importiert/aktualisiert', $changed);
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            $message = 'Spielerimport fehlgeschlagen';
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=players', $message);
    }

    /**
     * Stage an uploaded XLS, ICS or ZIP import source for processing.
     * Checks the request token and upload metadata, prepares the Joomla temporary directory and stages a supported import file; errors are logged.
     * @return void
     */
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

    /**
     * Create the namespaced club and team import service.
     * Resolves the Joomla database connection and constructs the API-backed importer for clubs, teams and players.
     * @return InlineHockeyClubTeamImportService Configured importer.
     */
    private function clubTeamImporter(): InlineHockeyClubTeamImportService
    {
        return new InlineHockeyClubTeamImportService($this->database(), new InlineHockeyApiClient());
    }

    /**
     * Create the match import service for the selected project.
     * Shares one Joomla database connection between the native project resolver and match importer.
     * @return InlineHockeyMatchImportService Configured match importer.
     */
    private function matchImporter(): InlineHockeyMatchImportService
    {
        $db = $this->database();

        return new InlineHockeyMatchImportService(
            $db,
            new InlineHockeyApiClient(),
            new InlineHockeyProjectService($db)
        );
    }

    /**
     * Create the Inline Hockey club-logo synchronisation service.
     * Uses the Joomla database and project service with a new remote API client.
     * @return InlineHockeyClubLogoService Configured logo importer.
     */
    private function logoImporter(): InlineHockeyClubLogoService
    {
        $db = $this->database();

        return new InlineHockeyClubLogoService(
            $db,
            new InlineHockeyApiClient(),
            new InlineHockeyProjectService($db)
        );
    }

    /**
     * Get the Joomla 5/6 database service for imports.
     * Resolves the DatabaseInterface from the active application's service container instead of legacy database factories.
     * @return DatabaseInterface Active database driver.
     */
    private function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $db */
        $db = $this->app->getContainer()->get(DatabaseInterface::class);

        return $db;
    }

    /**
     * Log a translated message describing a failed upload.
     * Converts the supplied Joomla language key into a warning used by the upload workflow.
     * @param string $languageKey Joomla translation key.
     * @return void
     */
    private function logUploadWarning(string $languageKey): void
    {
        Log::add(Text::_($languageKey), Log::WARNING, 'jsmerror');
    }
}
