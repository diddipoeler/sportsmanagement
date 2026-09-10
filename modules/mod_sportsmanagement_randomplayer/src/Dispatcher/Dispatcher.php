<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Random Player module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Dispatcher;

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

        $data['params']->set('layout', 'native');

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement RandomPlayer requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_randomplayer', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $data['list'] = $this->getHelperFactory()
            ->getHelper('RandomPlayerHelper')
            ->getData($data['params'], $database);

        $app->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'mod_sportsmanagement_randomplayer',
            'modules/mod_sportsmanagement_randomplayer/css/mod_sportsmanagement_randomplayer.css',
            ['version' => 'auto']
        );

        return $data;
    }
}
