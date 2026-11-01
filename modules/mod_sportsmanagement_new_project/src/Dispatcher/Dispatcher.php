<?php
/**
 * Joomla 5/6 dispatcher for mod_sportsmanagement_new_project.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNewProject\Site\Dispatcher;

\defined('_JEXEC') or die;

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
     * Prepare the native new-project module template and its permissions.
     *
     * Loads translations, selects the Joomla 5/6 native layout, queries the
     * project helper and determines whether the user can create articles.
     * Adds the module CSS and JavaScript only for HTML documents.
     *
     * @return array|false Render variables or false when rendering is skipped.
     * @throws \RuntimeException Outside the site client or without a database.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement New Project requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $helper = $this->getHelperFactory()->getHelper('NewProjectHelper');
        $data['params']->set('layout', 'native');
        $database = $this->joomlaDatabase();
        $projects = $helper->getData($data['params'], $app, $database);
        $data['list'] = is_array($projects) ? $projects : [];
        $data['canCreateArticles'] = (bool) $helper->canCreateArticles($data['params'], $app);

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $assets = $document->getWebAssetManager();
            $assets->registerAndUseStyle(
                'mod_sportsmanagement_new_project',
                'modules/mod_sportsmanagement_new_project/css/mod_sportsmanagement_new_project.css',
                ['version' => 'auto']
            );
            $assets->registerAndUseScript(
                'mod_sportsmanagement_new_project.native',
                'modules/mod_sportsmanagement_new_project/js/native.js',
                ['version' => 'auto'],
                ['defer' => true]
            );
        }

        return $data;
    }

    /**
     * Obtain a typed database dependency for the project helper.
     *
     * Retrieves the database from Joomla's dependency injection container and
     * rejects absent or incompatible services before they reach query methods.
     *
     * @return DatabaseInterface Valid Joomla database service.
     * @throws \RuntimeException When the service is not available.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement New Project database service is unavailable.', 500);
        }

        return $database;
    }
}
