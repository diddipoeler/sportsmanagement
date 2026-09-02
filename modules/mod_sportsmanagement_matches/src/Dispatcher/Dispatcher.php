<?php
/**
 * Native Joomla 5/6 dispatcher for the matches module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

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

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $result = $this->getHelperFactory()
            ->getHelper('MatchesHelper')
            ->getData($data['params'], $app, $data['module']);

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
