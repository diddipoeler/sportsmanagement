<?php
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $data['params']->set('layout', 'native');

        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $data['list'] = $this->getHelperFactory()
            ->getHelper('RandomPlayerHelper')
            ->getData($data['params'], $database);

        $document = $app->getDocument();

        if (method_exists($document, 'getWebAssetManager')) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_randomplayer',
                'modules/mod_sportsmanagement_randomplayer/css/mod_sportsmanagement_randomplayer.css'
            );
        }

        return $data;
    }
}
