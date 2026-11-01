<?php
/**
 * @version    5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRquotes\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare random quotation data for the frontend template.
     *
     * Loads component settings and language strings, fetches quotations through the database-backed helper, normalises absent records, and registers the module stylesheet for HTML output.
     *
     * @return array|false Module layout variables, or false if skipped.
     * @throws \RuntimeException When called outside the Joomla site application.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Random Quotes requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $database = $this->joomlaDatabase();
        $result = $this->getHelperFactory()->getHelper('RquotesHelper')->getData(
            $data['params'],
            ComponentHelper::getParams('com_sportsmanagement'),
            $app,
            $database
        );

        $result = is_array($result) ? $result : [];

        $data['source'] = is_string($result['source'] ?? null) ? $result['source'] : '';
        $data['quoteStyle'] = is_string($result['style'] ?? null) ? $result['style'] : '';
        $data['list'] = is_array($result['list'] ?? null) ? $result['list'] : [];
        $data['textLine'] = is_string($result['textLine'] ?? null) ? $result['textLine'] : '';
        $data['pictureServer'] = is_string($result['pictureServer'] ?? null) ? $result['pictureServer'] : '';

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_rquotes',
                'modules/mod_sportsmanagement_rquotes/assets/rquote.css'
            );
        }

        return $data;
    }

    /**
     * Obtain the Joomla database interface for Random Quotes queries.
     *
     * Resolves the service from Joomla's dependency injection container and
     * rejects missing or incompatible services before invoking the helper.
     *
     * @return DatabaseInterface Valid database service.
     * @throws \RuntimeException If the database dependency is unavailable.
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
