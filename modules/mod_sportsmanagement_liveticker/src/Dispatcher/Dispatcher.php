<?php
/**
 * Native Joomla 5/6 dispatcher for the SportsManagement liveticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementLiveticker\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Uri\Uri;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare the live match ticker and its AJAX refresh endpoint.
     *
     * Loads match events, copies only the expected template variables from
     * its helper, registers frontend assets and supplies the com_ajax refresh
     * URL. Framework-owned Joomla layout data cannot be overwritten.
     *
     * @return array|false Layout variables, or false if Joomla skips rendering.
     * @throws \RuntimeException When called outside the site or without a database.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Liveticker requires the Joomla site application.', 500);
        }

        $database = $this->joomlaDatabase();
        $result = $this->getHelperFactory()
            ->getHelper('LivetickerHelper')
            ->getData($data['params'], $data['module'], $app, $database);

        $result = is_array($result) ? $result : [];

        // Only these values belong to the public Liveticker layout contract.
        // Unexpected helper keys must never replace framework-owned context.
        $payload = [];

        foreach (['listHtml', 'updateTimeout', 'cssFile', 'moduleId'] as $key) {
            if (array_key_exists($key, $result)) {
                $payload[$key] = $result[$key];
            }
        }

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $assets = $document->getWebAssetManager();
            $assets->registerAndUseScript(
                'mod_sportsmanagement_liveticker',
                'modules/mod_sportsmanagement_liveticker/js/turtushout.js',
                ['version' => 'auto'],
                ['defer' => true]
            );

            $rawCssFile = $payload['cssFile'] ?? '';
            $cssFile = is_string($rawCssFile) && strlen($rawCssFile) <= 100
                && preg_match('/^[A-Za-z0-9_-][A-Za-z0-9_.-]*\.css$/D', $rawCssFile)
                ? $rawCssFile
                : '';
            if ($cssFile !== '') {
                $assets->registerAndUseStyle(
                    'mod_sportsmanagement_liveticker',
                    'modules/mod_sportsmanagement_liveticker/css/' . $cssFile,
                    ['version' => 'auto']
                );
            }
        }

        $payload['refreshUrl'] = rtrim((string) Uri::base(), '/')
            . '/index.php?option=com_ajax&module=sportsmanagement_liveticker&method=refresh&format=raw';

        return array_merge($data, $payload);
    }

    /**
     * Resolve the Joomla database service required by the Liveticker helper.
     *
     * Reads the database dependency from Joomla's service container and
     * checks its interface before passing it to the module's query methods.
     *
     * @return DatabaseInterface Connection for module data queries.
     * @throws \RuntimeException If no compatible database service exists.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Liveticker database service is unavailable.', 500);
        }

        return $database;
    }
}
