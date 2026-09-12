<?php
/**
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRquotes\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
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
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $result = $this->getHelperFactory()->getHelper('RquotesHelper')->getData(
            $data['params'],
            ComponentHelper::getParams('com_sportsmanagement'),
            $app,
            $database
        );

        $data['source'] = $result['source'];
        $data['quoteStyle'] = $result['style'];
        $data['list'] = $result['list'];
        $data['textLine'] = $result['textLine'];
        $data['pictureServer'] = $result['pictureServer'];

        $app->getDocument()
            ->getWebAssetManager()
            ->registerAndUseStyle(
                'mod_sportsmanagement_rquotes',
                'modules/mod_sportsmanagement_rquotes/assets/rquote.css'
            );

        return $data;
    }
}
