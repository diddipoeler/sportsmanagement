<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement New Project module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

if (!class_exists(NewProjectHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/NewProjectHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(NewProjectHelper::class)) {
    throw new \RuntimeException('SportsManagement New Project helper could not be loaded.', 500);
}

if (!class_exists('modJSMNewProjectHelper', false)) {
    final class modJSMNewProjectHelper
    {
        /**
         * Compatibility adapter for extensions still calling the historical helper signature.
         */
        public static function getData($newProjectArticle = 0, $categoryId = 0): array
        {
            $params = new Registry([
                'new_project_article' => (int) $newProjectArticle,
                'mycategory' => (int) $categoryId,
            ]);
            /** @var SiteApplication $app */
            $app = Factory::getContainer()->get(SiteApplication::class);
            $rows = (new NewProjectHelper())->getData($params, $app);
            $result = [];

            foreach ($rows as $row) {
                $legacy = clone $row;
                $legacy->id = (string) ($row->project_slug ?? $row->id ?? '');
                $legacy->liganame = (string) ($row->league_name ?? '');
                $legacy->roundcode = (string) ($row->round_slug ?? '');
                $result[] = $legacy;
            }

            return $result;
        }
    }
}
