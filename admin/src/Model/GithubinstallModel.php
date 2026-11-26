<?php
/**
 * Joomla 5/6 administrator GitHub installation model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/** Native Joomla 5/6 model for downloading and unpacking the configured GitHub update archive. */
final class GithubinstallModel extends BaseDatabaseModel
{
    /** @var array<string,string> */
    private array $successText = [];

    /**
     * Redirect to the component's existing GitHub installation workflow.
     * Includes the current Joomla form token in the administrator URL.
     * @return void
     */
    public function installfolder(): void
    {
        $this->administratorApplication()->redirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=update&task=update.save&file_name=jsm_update_github.php'
                . '&' . Session::getFormToken() . '=1',
                false
            ),
            303
        );
    }

    /**
     * Download the configured repository archive and extract it into Joomla's temp directory.
     *
     * Each archive is extracted into a new private staging directory below
     * Joomla's temporary root. An older extraction of the same branch cannot
     * be selected by the folder-installer handoff. Failed extractions are
     * removed without following symbolic links; a successful package is
     * retained for the subsequent Joomla installer request.
     * Rejects malformed/non-string archive settings, resets stale success state
     * on each attempt and catches download and extraction exceptions.
     * Records only exception classes in Joomla's server log so archive URLs,
     * tokens and filesystem paths are never reflected in diagnostics.
     *
     * @param mixed $link Configured GitHub archive URL.
     * @return array<string,string>|false Update status or false on failure.
     */
    public function CopyGithubLink($link)
    {
        $url = is_string($link) && strlen($link) <= 2048 ? trim($link) : '';
        $app = $this->administratorApplication();
        $this->successText = [];

        // A failed download must not reuse an extracted directory from a
        // previous request when the follow-up installer starts.
        $app->setUserState('com_sportsmanagement.github_update_dir', null);

        if (!$this->isAllowedUrl($url)) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        try {
            $downloadedFile = InstallerHelper::downloadPackage($url);
        } catch (\Throwable $e) {
            // Downloader exceptions can contain credentials and internal paths.
            Log::add(__METHOD__ . ': download failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        if (!$downloadedFile) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $rawTmpPath = $app->get('tmp_path');
        $tmpPath = is_string($rawTmpPath) && $rawTmpPath !== '' ? realpath($rawTmpPath) : false;
        if ($tmpPath === false || !is_dir($tmpPath) || !is_writable($tmpPath)
            || !is_string($downloadedFile) || basename($downloadedFile) !== $downloadedFile
            || in_array($downloadedFile, ['.', '..'], true) || str_contains($downloadedFile, chr(92))
            || preg_match('/[\x00-\x1F\x7F]/', $downloadedFile)) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $archivePath = $tmpPath . DIRECTORY_SEPARATOR . $downloadedFile;

        if (!File::exists($archivePath) || is_link($archivePath)) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $this->successText['Komponente:'] = '<span class="text-success">'
            . Text::sprintf(
                'Die ZIP-Datei der Komponente [ %1$s ] konnte kopiert werden!',
                '<strong>' . htmlspecialchars(basename($archivePath), ENT_QUOTES, 'UTF-8') . '</strong>'
            )
            . '</span><br />';

        $stage = null;
        try {
            $stage = $this->createStagingDirectory($tmpPath);
            $archive = new Archive(['tmp_path' => $stage]);
            $result = $archive->extract($archivePath, $stage);
        } catch (\Throwable $e) {
            if ($stage !== null) {
                $this->discardStagingDirectory($tmpPath, $stage);
            }
            // Archive paths, stack details and credentials must stay private.
            Log::add(__METHOD__ . ': extraction failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT'), 'error');

            return false;
        }

        if (!$result) {
            $this->discardStagingDirectory($tmpPath, $stage);
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT'), 'error');

            return false;
        }

        $installDirectory = $this->findExtractedDirectory($stage, $downloadedFile, $url);

        if ($installDirectory === null) {
            $this->discardStagingDirectory($tmpPath, $stage);
            $app->enqueueMessage('SportsManagement update archive directory could not be found after extraction.', 'error');
            return false;
        }

        $app->setUserState('com_sportsmanagement.github_update_dir', $installDirectory);
        $this->successText['Module:'] = '';

        return $this->successText;
    }

    /**
     * Create a fresh private extraction directory for exactly one update.
     *
     * A random name and exclusive directory creation prevent reuse of
     * existing contents, including an older download of the same branch.
     *
     * @param string $tmpPath Canonical Joomla temporary root.
     * @return string Canonical newly created extraction directory.
     * @throws \RuntimeException When staging cannot be created safely.
     */
    private function createStagingDirectory(string $tmpPath): string
    {
        $stage = $tmpPath . DIRECTORY_SEPARATOR . 'jsm-github-' . bin2hex(random_bytes(16));

        if (!@mkdir($stage, 0700) || is_link($stage) || realpath($stage) !== $stage) {
            throw new \RuntimeException('SportsManagement archive staging directory is unavailable.');
        }

        return $stage;
    }

    /**
     * Remove only the staging directory created for this failed download.
     *
     * Verify its canonical parent and random directory name before walking
     * the tree. Symbolic links are removed as links, never traversed. Cleanup
     * is best effort so a secondary filesystem failure cannot hide the
     * original extraction failure or expose its private diagnostic details.
     *
     * @param string $tmpPath Canonical Joomla temporary root.
     * @param string $stage Newly created staging directory.
     * @return void
     */
    private function discardStagingDirectory(string $tmpPath, string $stage): void
    {
        if (dirname($stage) !== $tmpPath
            || preg_match('/^jsm-github-[a-f0-9]{32}$/D', basename($stage)) !== 1
            || is_link($stage) || realpath($stage) !== $stage || !is_dir($stage)) {
            return;
        }

        try {
            $entries = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($stage, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($entries as $entry) {
                $path = $entry->getPathname();
                if ($entry->isLink() || !$entry->isDir()) {
                    @unlink($path);
                } else {
                    @rmdir($path);
                }
            }

            @rmdir($stage);
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
        }
    }

    /**
     * Find the expected extracted GitHub archive folder.
     * Only considers names associated with the current downloaded archive,
     * and rejects symbolic links or unrelated/stale folder candidates so
     * a previous extraction cannot be installed by another request.
     * Requires the component manifest in the new canonical directory.
     * @param string $tmpPath Newly created extraction directory.
     * @param string $downloadedFile Downloaded archive filename.
     * @param string $url Original archive URL.
     * @return string|null Matching extracted folder or null.
     */
    private function findExtractedDirectory(string $tmpPath, string $downloadedFile, string $url): ?string
    {
        $stems = array_values(array_unique(array_filter([
            pathinfo(basename($downloadedFile), PATHINFO_FILENAME),
            pathinfo(basename((string) parse_url($url, PHP_URL_PATH)), PATHINFO_FILENAME),
        ])));

        foreach ($stems as $stem) {
            foreach ([$stem, 'sportsmanagement-' . $stem] as $candidateName) {
                $candidate = $tmpPath . DIRECTORY_SEPARATOR . basename($candidateName);

                // A symlinked old extraction directory must not be accepted
                // as the source of an administrator component installation.
                if (Folder::exists($candidate) && !is_link($candidate)
                    && realpath($candidate) === $candidate
                    && is_file($candidate . DIRECTORY_SEPARATOR . 'sportsmanagement.xml')
                    && !is_link($candidate . DIRECTORY_SEPARATOR . 'sportsmanagement.xml')) {
                    return $candidate;
                }
            }
        }

        // Never pick the newest unrelated sportsmanagement-* directory:
        // it might be a stale or attacker-controlled previous extraction.
        return null;
    }

    /**
     * Accept only official GitHub HTTPS repository archive endpoints.
     *
     * A configured update package is executable application code, so generic
     * HTTP URLs or arbitrary hosts must never be passed to Joomla's
     * downloader. Reject URL user-info, custom ports, fragments and queries
     * (including embedded credentials), and require a genuine GitHub archive
     * path. This also prevents direct requests to local or metadata services.
     * Network-level redirects still depend on Joomla's installer transport.
     *
     * @param string $url Configured update archive URL.
     * @return bool Whether this is an official GitHub archive endpoint.
     */
    private function isAllowedUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > 2048
            || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);

        if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user'], $parts['pass'])
            || isset($parts['user']) || isset($parts['port'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if (str_contains($path, '%') || str_contains($path, '\\')
            || preg_match('~(?:^|/)(?:\\.|\\.\\.)(?:/|$)~', $path) === 1
            || str_contains($path, '//')) {
            return false;
        }

        return match ($host) {
            'github.com' => preg_match(
                '~^/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/archive/(?:refs/(?:heads|tags)/)?[A-Za-z0-9_./-]+\.zip$~D',
                $path
            ) === 1,
            'codeload.github.com' => preg_match(
                '~^/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/zip/(?:refs/(?:heads|tags)/)?[A-Za-z0-9_./-]+$~D',
                $path
            ) === 1,
            default => false,
        };
    }

    /**
     * Resolve and validate the Joomla administrator application service.
     * @return AdministratorApplication Active Joomla administrator.
     * @throws \RuntimeException If the application service is unavailable.
     */
    private function administratorApplication(): AdministratorApplication
    {
        $application = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$application instanceof AdministratorApplication || !$application->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement GitHub installer requires the Joomla administrator application.', 500);
        }

        return $application;
    }
}
