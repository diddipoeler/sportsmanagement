<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement UEFA ranking module.
 *
 * @version    3.8.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementUefaWertung\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $result = $this->getHelperFactory()
            ->getHelper('UefaWertungHelper')
            ->getData($data['params'], $app, $database);

        $data['seasons'] = $result['seasons'];
        $data['rankings'] = $result['rankings'];

        $app->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'mod_sportsmanagement_uefawertung',
            'modules/mod_sportsmanagement_uefawertung/css/mod_sportsmanagement_uefawertung.css'
        );

        return $data;
    }
}
