<?php
/**
 * Joomla 5/6 SportsManagement matches module PHP implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Dispatcher;

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

        $language->load('mod_sportsmanagement_matches', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $result = $this->getHelperFactory()
            ->getHelper('MatchesHelper')
            ->getData($data['params'], $app, $data['module'], $database);

        $data['matches'] = $result['matches'];
        $data['legacyUpdateRequested'] = $result['legacy_update_requested'];

        $template = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $data['params']->get('template', 'default_tableless'));
        $template = $template !== '' ? $template : 'default_tableless';
        $wam = $app->getDocument()->getWebAssetManager();
        $wam->registerAndUseStyle(
            'mod_sportsmanagement_matches.native',
            'modules/mod_sportsmanagement_matches/assets/css/native.css'
        );

        $templateCss = 'modules/mod_sportsmanagement_matches/tmpl/' . $template . '/mod_sportsmanagement_matches.css';
        if (is_file(JPATH_ROOT . '/' . $templateCss)) {
            $wam->registerAndUseStyle('mod_sportsmanagement_matches.template.' . $template, $templateCss);
        }

        return $data;
    }
}
