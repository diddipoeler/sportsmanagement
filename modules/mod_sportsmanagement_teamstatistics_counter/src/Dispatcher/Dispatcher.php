<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Team Statistics Counter module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
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
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $data['data'] = $this->getHelperFactory()
            ->getHelper('TeamStatisticsCounterHelper')
            ->getData($data['params'], $joomlaDatabase);

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_teamstatistics_counter',
                'modules/mod_sportsmanagement_teamstatistics_counter/css/mod_sportsmanagement_teamstatistics_counter.css'
            );
        }

        return $data;
    }
}
