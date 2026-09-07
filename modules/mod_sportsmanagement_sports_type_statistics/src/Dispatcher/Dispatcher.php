<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
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
        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_sports_type_statistics', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $data['statistics'] = $this->getHelperFactory()
            ->getHelper('SportsTypeStatisticsHelper')
            ->getData($data['params'], $database);

        $document = $app->getDocument();
        if (method_exists($document, 'getWebAssetManager')) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_sports_type_statistics',
                'modules/mod_sportsmanagement_sports_type_statistics/css/mod_sportsmanagement_sports_type_statistics.css'
            );
        }

        return $data;
    }
}
