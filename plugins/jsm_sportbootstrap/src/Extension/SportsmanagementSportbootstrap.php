<?php
/**
 * Install SportsManagement sport types after saving component options.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Plugin\Extension\SportsmanagementSportbootstrap\Extension;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredSportTypeInstaller;
use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * React to an authorised com_config.component save without touching frontend
 * requests or unrelated extensions. The saved Extension table carries the
 * current options; ComponentHelper's cached parameters may still be stale.
 */
final class SportsmanagementSportbootstrap extends CMSPlugin implements SubscriberInterface
{
    /**
     * Subscribe to the Joomla 5/6 extension configuration save notification.
     *
     * The com_config component dispatches this event only after its options
     * have been stored, allowing the installer to use saved selections.
     *
     * @return array<string, string> Joomla event name and handler mapping.
     */
    public static function getSubscribedEvents(): array
    {
        return ['onExtensionAfterSave' => 'onExtensionAfterSave'];
    }

    /**
     * Install selected but missing sport types after a configuration save.
     *
     * Checks the exact com_config.component event, component extension and
     * administrator's core.admin permission. Reads the freshly saved params
     * from the Extension table, not ComponentHelper's stale in-request cache.
     * Adds only absent sport types and optionally uses the existing database
     * tool for XML-defined events and positions. Errors are logged privately
     * and surfaced only as a generic administrator message.
     *
     * @param AfterSaveEvent $event Joomla extension after-save event.
     * @return void
     */
    public function onExtensionAfterSave(AfterSaveEvent $event): void
    {
        if ($event->getArgument('context') !== 'com_config.component') {
            return;
        }

        $extension = $event->getArgument('subject');

        if (!is_object($extension) || ($extension->element ?? null) !== 'com_sportsmanagement'
            || !is_string($extension->params ?? null)) {
            return;
        }

        $app = $this->getApplication();

        if (!$app->isClient('administrator')) {
            return;
        }

        $identity = $app->getIdentity();

        if ($identity === null || (int) ($identity->id ?? 0) <= 0
            || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            return;
        }

        try {
            $params = new Registry($extension->params);
            $selected = $params->get('cfg_sport_types', []);

            if ($selected === [] || $selected === '') {
                return;
            }

            $component = $app->bootComponent('com_sportsmanagement');
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$db instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement database service is unavailable.');
            }

            $seed = null;

            // Use the *saved* option for standards, not ComponentHelper's
            // request-local cache (which can still hold the previous value).
            if ((string) $params->get('install_standard_position', '0') === '1') {
                $model = $component->getMVCFactory()->createModel(
                    'Databasetool',
                    'Administrator',
                    ['ignore_request' => true]
                );

                if (!$model instanceof DatabasetoolModel) {
                    throw new \RuntimeException('SportsManagement sport standards model is unavailable.');
                }

                $seed = static function (string $name, int $id, string $token) use ($model): void {
                    $model->addStandardForSportType($name, $id, $token, 0);
                };
            }

            $installed = (new ConfiguredSportTypeInstaller($db))->installMissing($selected, $seed);

            if ($installed !== []) {
                $app->getLanguage()->load(
                    'plg_extension_jsm_sportbootstrap',
                    JPATH_ADMINISTRATOR,
                    $app->getLanguage()->getTag(),
                    true
                );
                $app->enqueueMessage(
                    Text::sprintf('PLG_EXTENSION_JSM_SPORTBOOTSTRAP_INSTALLED', count($installed)),
                    'message'
                );
            }
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement configured sport installation failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }
    }
}
