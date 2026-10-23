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

    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Birthday database service is unavailable.', 500);
        }

        return $database;
    }
}
