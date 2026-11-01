<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement birthday module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementBirthday\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare birthday records and display settings for the module template.
     *
     * Loads translations, passes component settings and the Joomla database
     * to the birthday helper, and converts missing optional values to safe
     * template defaults. Enables Bootstrap carousel assets only for relevant
     * modes in an HTML document.
     *
     * @return array|false Birthday template variables, or false if skipped.
     * @throws \RuntimeException Outside the site application or without a database.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Birthday requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_birthday', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $database = $this->joomlaDatabase();
        $result = $this->getHelperFactory()->getHelper('BirthdayHelper')->getData(
            $data['params'],
            ComponentHelper::getParams('com_sportsmanagement'),
            $app,
            $database
        );

        // Keep rendering safe when a legacy helper supplies no result array.
        $result = is_array($result) ? $result : [];

        // Helpers may return no records; keep the template data shape stable.
        $data['persons'] = is_array($result['persons'] ?? null) ? $result['persons'] : [];
        $data['mode'] = is_scalar($result['mode'] ?? null) ? (string) $result['mode'] : 'L';
        $data['pictureServer'] = is_string($result['pictureServer'] ?? null)
            ? $result['pictureServer']
            : '';

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument && in_array($data['mode'], ['B', 'J'], true)) {
            $document->getWebAssetManager()->useScript('bootstrap.carousel');
        }

        return $data;
    }

    /**
     * Retrieve the database service used for birthday queries.
     *
     * Ensures the container value implements Joomla's DatabaseInterface,
     * avoiding invalid dependency types in the legacy-compatible helper.
     *
     * @return DatabaseInterface Database used to load birthday records.
     * @throws \RuntimeException If no compatible service is available.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Birthday database service is unavailable.', 500);
        }

        return $database;
    }
}
