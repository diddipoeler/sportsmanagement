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
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $this->getApplication()->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
        $data['statistics'] = $this->getHelperFactory()
            ->getHelper('SportsTypeStatisticsHelper')
            ->getData($data['params'], $this->getApplication());

        $document = $this->getApplication()->getDocument();
        if (method_exists($document, 'getWebAssetManager')) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_sports_type_statistics',
                'modules/mod_sportsmanagement_sports_type_statistics/css/mod_sportsmanagement_sports_type_statistics.css'
            );
        }

        return $data;
    }
}
