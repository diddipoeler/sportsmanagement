<?php
/**
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementClubBirthday\Site\Dispatcher;

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
     * Load club anniversary information for the frontend module layout.
     *
     * Normalises optional club records and display mode, loads component translations and enables Bootstrap carousel only for the compatible birthday-carousel layout.
     *
     * Optional database and helper failures are logged by exception class
     * and return empty template records so the surrounding page stays usable.
     *
     * @return array|false Layout variables, or false if Joomla skips rendering.
     * @throws \RuntimeException When called outside the site.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Club Birthday requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_club_birthday', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        try {
            $database = $this->joomlaDatabase();
            $result = $this->getHelperFactory()
                ->getHelper('ClubBirthdayHelper')
                ->getData($data['params'], $app, $database);
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', \Joomla\CMS\Log\Log::ERROR, 'com_sportsmanagement');
            $result = [];
        }

        // Legacy helpers can return no result when club data is unavailable.
        $result = is_array($result) ? $result : [];

        $data['clubs'] = is_array($result['clubs'] ?? null) ? $result['clubs'] : [];
        $mode = $result['mode'] ?? 'L';
        $data['mode'] = is_string($mode) && in_array($mode, ['L', 'BC'], true) ? $mode : 'L';

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument && $data['mode'] === 'BC') {
            $document->getWebAssetManager()->useScript('bootstrap.carousel');
        }

        return $data;
    }

    /**
     * Resolve the Joomla database service required by the Club Birthday helper.
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
            throw new \RuntimeException('SportsManagement database service is unavailable.', 500);
        }

        return $database;
    }
}
