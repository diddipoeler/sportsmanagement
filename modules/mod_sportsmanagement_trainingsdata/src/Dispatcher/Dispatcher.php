<?php
namespace Diddipoeler\Module\SportsManagementTrainingsData\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $app = $this->getApplication();

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
        $data['trainingsdata'] = $this->getHelperFactory()
            ->getHelper('TrainingsDataHelper')
            ->getData($data['params']);

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_trainingsdata',
                'modules/mod_sportsmanagement_trainingsdata/css/mod_sportsmanagement_trainingsdata.css'
            );
        }

        return $data;
    }
}
