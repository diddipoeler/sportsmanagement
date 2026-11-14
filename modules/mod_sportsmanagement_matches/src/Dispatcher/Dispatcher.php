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

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Assemble the matches module's template data for Joomla 5/6 rendering.
     *
     * Loads frontend and component languages, obtains a typed database service,
     * and requests matches from the module helper. Normalises empty helper
     * results and safely chooses optional per-template CSS without allowing
     * module parameters to introduce arbitrary filesystem paths. Reuses
     * registered styles across multiple module instances on the same page.
     *
     * @return array|false Matches and layout variables, or false if skipped.
     * @throws \RuntimeException If called outside the site or without a database.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Matches requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_matches', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $database = $this->joomlaDatabase();
        $result = $this->getHelperFactory()
            ->getHelper('MatchesHelper')
            ->getData($data['params'], $app, $data['module'], $database);

        // A helper may return null or false when no database data is available.
        // Avoid offset access on non-arrays in PHP 8.3 and newer.
        $result = is_array($result) ? $result : [];

        // Keep template data types stable when optional helper output is missing.
        $data['matches'] = is_array($result['matches'] ?? null) ? $result['matches'] : [];
        $data['legacyUpdateRequested'] = (bool) ($result['legacy_update_requested'] ?? false);

        $rawTemplate = $data['params']->get('template', 'default_tableless');
        // Never turn an invalid template name into a different valid directory.
        $template = is_string($rawTemplate) && preg_match('/^[A-Za-z0-9_-]+$/D', $rawTemplate)
            ? $rawTemplate
            : 'default_tableless';
        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $this->useMatchesStyles($document, $template);
        }

        return $data;
    }

    /**
     * Enable the shared Matches CSS and any selected template-specific CSS.
     *
     * The module can appear several times on the same Joomla page. Register
     * the native stylesheet once per HTML WebAssetManager and enable it on
     * every render. The caller has validated the template folder identifier;
     * optional template styles are only used when their packaged CSS exists.
     * Distinct template layouts retain independent asset names and sources.
     *
     * @param HtmlDocument $document Site HTML response shared by module instances.
     * @param string $template Validated module template directory name.
     * @return void
     */
    private function useMatchesStyles(HtmlDocument $document, string $template): void
    {
        $assets = $document->getWebAssetManager();
        $nativeName = 'mod_sportsmanagement_matches.native';

        if (!$assets->assetExists('style', $nativeName)) {
            $assets->registerStyle(
                $nativeName,
                'modules/mod_sportsmanagement_matches/assets/css/native.css'
            );
        }

        $assets->useStyle($nativeName);

        $templateCss = 'modules/mod_sportsmanagement_matches/tmpl/' . $template
            . '/mod_sportsmanagement_matches.css';

        if (!is_file(JPATH_ROOT . '/' . $templateCss)) {
            return;
        }

        $templateName = 'mod_sportsmanagement_matches.template.' . $template;

        if (!$assets->assetExists('style', $templateName)) {
            $assets->registerStyle($templateName, $templateCss);
        }

        $assets->useStyle($templateName);
    }

    /**
     * Resolve the database interface required by the matches helper.
     *
     * Verifies the dependency injection container supplied a compatible
     * Joomla database implementation before any match queries are executed.
     *
     * @return DatabaseInterface Database used to fetch match records.
     * @throws \RuntimeException When no compatible database is available.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Matches database service is unavailable.', 500);
        }

        return $database;
    }
}
