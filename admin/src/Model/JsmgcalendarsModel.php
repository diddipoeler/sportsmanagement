<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/** Native Joomla 5/6 administrator list model for Google calendars. */
final class JsmgcalendarsModel extends SportsManagementListModel
{
    /** Ensure the configured Google API package is available. */
    public function check_google_api(): bool
    {
        $target = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/google-php';
        $composer = $target . '/composer.json';

        if (is_file($composer)) {
            Log::add(Text::_('Google API vorhanden'), Log::NOTICE, 'jsmerror');

            return true;
        }

        Log::add(Text::_('Google API nicht vorhanden'), Log::WARNING, 'jsmerror');
        $app = Factory::getApplication();
        $url = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('google_api_datei', ''));

        if ($url === '') {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $package = InstallerHelper::downloadPackage($url);

        if (!$package) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $tmpPath = rtrim((string) $app->get('tmp_path', JPATH_SITE . '/tmp'), '/\\');
        $packagePath = $tmpPath . DIRECTORY_SEPARATOR . basename((string) $package);

        if (!is_file($packagePath)) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

            return false;
        }

        $extractDir = $tmpPath . DIRECTORY_SEPARATOR . 'jsmgcalendar-google-api-' . bin2hex(random_bytes(6));

        if (!mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
            @unlink($packagePath);

            return false;
        }

        try {
            $archive = new Archive();

            if (!$archive->extract($packagePath, $extractDir)) {
                throw new \RuntimeException('Google API archive could not be extracted.');
            }

            $source = $this->findGoogleApiRoot($extractDir);

            if ($source === null) {
                throw new \RuntimeException('Google API package does not contain composer.json.');
            }

            $this->copyDirectory($source, $target);
            Log::add(Text::_('Google API entpackt'), Log::NOTICE, 'jsmerror');

            return is_file($composer);
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');

            return false;
        } finally {
            @unlink($packagePath);
            $this->removeDirectory($extractDir);
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));

        $calendarIds = $this->getState('ids');

        if (is_array($calendarIds)) {
            $calendarIds = array_values(array_unique(array_filter(array_map('intval', $calendarIds))));

            if ($calendarIds) {
                $query->where($db->quoteName('id') . ' IN (' . implode(',', $calendarIds) . ')');
            }
        } elseif ($calendarIds !== null && $calendarIds !== '') {
            $query->where($db->quoteName('id') . ' = ' . (int) rtrim((string) $calendarIds, ','));
        }

        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.admin', 'com_sportsmanagement')) {
            $levels = array_values(array_unique(array_filter(array_map(
                'intval',
                $user->getAuthorisedViewLevels()
            ))));

            if ($levels) {
                $query->where($db->quoteName('access') . ' IN (' . implode(',', $levels) . ')');
            } else {
                $query->where('1 = 0');
            }
        }

        $query->order($db->quoteName('name') . ' ASC');

        return $query;
    }

    private function findGoogleApiRoot(string $directory): ?string
    {
        if (is_file($directory . '/composer.json')) {
            return $directory;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir() && is_file($item->getPathname() . '/composer.json')) {
                return $item->getPathname();
            }
        }

        return null;
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new \RuntimeException('Google API destination directory could not be created.');
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($source) + 1);
            $target = $destination . DIRECTORY_SEPARATOR . $relative;

            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                    throw new \RuntimeException('Google API directory could not be created: ' . $relative);
                }
            } elseif (!copy($item->getPathname(), $target)) {
                throw new \RuntimeException('Google API file could not be copied: ' . $relative);
            }
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($directory);
    }
}
