<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\Database\DatabaseInterface;

return new class () implements InstallerScriptInterface {
    public function install(InstallerAdapter $adapter): bool
    {
        return $this->disableLegacySearchPlugin();
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return $this->disableLegacySearchPlugin();
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

    private function disableLegacySearchPlugin(): bool
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $legacyElements = [
                $db->quote('search_sportsmanagement'),
                $db->quote('jsm_search'),
            ];

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 0')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('search'))
                ->where($db->quoteName('element') . ' IN (' . implode(',', $legacyElements) . ')');

            $db->setQuery($query)->execute();
        } catch (\Throwable $exception) {
            Factory::getApplication()->enqueueMessage(
                'SportsManagement: the obsolete Joomla Search plugin could not be disabled automatically: '
                . $exception->getMessage(),
                'warning'
            );
        }

        return true;
    }
};
