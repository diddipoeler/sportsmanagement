<?php
namespace Diddipoeler\Module\SportsManagementRanking\Site\Dispatcher;

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
        $data['params']->set('layout', 'native');
        $this->getApplication()->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $moduleName = (string) ($data['module']->module ?? 'mod_sportsmanagement_ranking');
        $style = 'modules/' . $moduleName . '/css/' . $moduleName . '.css';

        if (is_file(JPATH_ROOT . '/' . $style)) {
            $this->getApplication()
                ->getDocument()
                ->getWebAssetManager()
                ->registerAndUseStyle($moduleName, $style);
        }

        $data['list'] = $this->getHelperFactory()->getHelper('RankingHelper')->getData(
            $data['params'],
            $data['module'],
            $this->getApplication()
        );

        return $data;
    }
}
