<?php
namespace Diddipoeler\Module\SportsManagementBirthday\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
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
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $result = $this->getHelperFactory()->getHelper('BirthdayHelper')->getData(
            $data['params'],
            ComponentHelper::getParams('com_sportsmanagement'),
            $app,
            $database
        );

        $data['persons'] = $result['persons'];
        $data['mode'] = $result['mode'];
        $data['pictureServer'] = $result['pictureServer'];

        if (in_array($data['mode'], ['B', 'J'], true)) {
            $app->getDocument()->getWebAssetManager()->useScript('bootstrap.carousel');
        }

        return $data;
    }
}
