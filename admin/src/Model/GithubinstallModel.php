<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** Native Joomla 5/6 model for downloading and unpacking the configured GitHub update archive. */
final class GithubinstallModel extends BaseDatabaseModel
{
    /** @var array<string,string> */
    private array $successText = [];

    public function installfolder(): void
    {
        Factory::getApplication()->redirect(
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
     * The archive is intentionally extracted into the temp root because the existing
     * jsm_update_github.php workflow installs from the GitHub-generated top-level folder.
     *
     * @return array<string,string>|false
     */
    public function CopyGithubLink($link)
    {
        $url = trim((string) $link);

        if (!$this->isAllowedUrl($url)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $downloadedFile = InstallerHelper::downloadPackage($url);

        if (!$downloadedFile) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $tmpPath = rtrim((string) Factory::getApplication()->get('tmp_path'), '/\\');
        $archivePath = $tmpPath . DIRECTORY_SEPARATOR . basename((string) $downloadedFile);

        if (!is_file($archivePath)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $this->successText['Komponente:'] = '<span class="text-success">'
            . Text::sprintf(
                'Die ZIP-Datei der Komponente [ %1$s ] konnte kopiert werden!',
                '<strong>' . htmlspecialchars(basename($archivePath), ENT_QUOTES, 'UTF-8') . '</strong>'
            )
            . '</span><br />';

        try {
            $archive = new Archive(['tmp_path' => $tmpPath]);
            $result = $archive->extract($archivePath, $tmpPath);
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        if (!$result) {
            Factory::getApplication()->enqueueMessage(Text::_('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT'), 'error');

            return false;
        }

        $this->successText['Module:'] = '';

        return $this->successText;
    }

    private function isAllowedUrl(string $url): bool
    {
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
