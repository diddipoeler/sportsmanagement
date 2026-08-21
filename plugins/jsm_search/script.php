<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\Database\DatabaseInterface;

return new class () implements InstallerScriptInterface {
    public function install(InstallerAdapter $adapter): bool
    {
        return $this->migrateLegacySearchPlugin();
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return $this->migrateLegacySearchPlugin();
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }

    private function migrateLegacySearchPlugin(): bool
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $legacyElements = [
                $db->quote('search_sportsmanagement'),
                $db->quote('jsm_search'),
            ];

            $query = $db->getQuery(true)
                ->select('MAX(' . $db->quoteName('enabled') . ')')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('search'))
                ->where($db->quoteName('element') . ' IN (' . implode(',', $legacyElements) . ')');

            $db->setQuery($query);
            $legacyWasEnabled = (int) $db->loadResult() === 1;

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 0')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('search'))
                ->where($db->quoteName('element') . ' IN (' . implode(',', $legacyElements) . ')');

            $db->setQuery($query)->execute();

            if ($legacyWasEnabled) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('enabled') . ' = 1')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('finder'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('jsm_search'));

                $db->setQuery($query)->execute();
            }
        } catch (\Throwable $exception) {
            Factory::getApplication()->enqueueMessage(
                'SportsManagement: the obsolete Joomla Search plugin could not be migrated automatically: '
                . $exception->getMessage(),
                'warning'
            );
        }

        return true;
    }
};
