<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement UEFA ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementUefaWertung\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare seasonal UEFA coefficient rankings for the frontend module.
     *
     * Retrieves ranking data through Joomla's database service and normalises
     * absent season and ranking collections for the frontend template.
     * For HTML output it checks the shared WebAssetManager before registering
     * the module stylesheet, so multiple module instances reuse one asset.
     * Database or helper failures leave empty collections in the template
     * and log only the exception class rather than private SQL diagnostics.
     *
     * @return array|false Layout data or false if rendering is skipped.
     * @throws \RuntimeException If the site application or database is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement UEFA ranking requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $data['seasons'] = [];
        $data['rankings'] = [];

        try {
            $database = $this->joomlaDatabase();
            $result = $this->getHelperFactory()
                ->getHelper('UefaWertungHelper')
                ->getData($data['params'], $app, $database);

            if (is_array($result)) {
                $data['seasons'] = is_array($result['seasons'] ?? null) ? $result['seasons'] : [];
                $data['rankings'] = is_array($result['rankings'] ?? null) ? $result['rankings'] : [];
            }
        } catch (\Throwable $exception) {
            // Optional ranking data must not fail the entire Joomla page.
            // The public response never includes SQL or database credentials.
            Log::add(
                'SportsManagement UEFA ranking unavailable (' . get_class($exception) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
        }

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            // Several instances may share Joomla's WebAssetManager. Register
            // the stylesheet only once, but activate it for every render.
            $assets = $document->getWebAssetManager();

            if (!$assets->assetExists('style', 'mod_sportsmanagement_uefawertung')) {
                $assets->registerStyle(
                    'mod_sportsmanagement_uefawertung',
                    'modules/mod_sportsmanagement_uefawertung/css/mod_sportsmanagement_uefawertung.css',
                    ['version' => 'auto']
                );
            }

            $assets->useStyle('mod_sportsmanagement_uefawertung');
        }

        return $data;
    }

    /**
     * Resolve the Joomla database dependency for the UEFA Ranking helper.
     *
     * Uses the dependency injection container and verifies that the resolved
     * service implements DatabaseInterface before any queries are performed.
     *
     * @return DatabaseInterface Valid database connection for module queries.
     * @throws \RuntimeException When no compatible database service exists.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement database service is unavailable.', 500);
        }

        return $database;
    }
}
