<?php
namespace Diddipoeler\Plugin\System\SportsmanagementRegistercomp\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use Joomla\Http\HttpFactory;

/**
 * Joomla 5/6 system plugin for SportsManagement registration and browser policy.
 */
final class SportsmanagementRegistercomp extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    private const REGISTRATION_ENDPOINT = 'https://www.fussballineuropa.de/diddipoeler/jsmpaket.php';

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
        ];
    }

    public function onAfterRoute(AfterRouteEvent $event): void
    {
        $app = $this->getApplication();

        if ($app->getInput()->getCmd('option') !== 'com_sportsmanagement') {
            return;
        }

        if ($app->isClient('administrator')) {
            if ((int) $this->params->get('load_debug', 1) === 1) {
                $this->registerInstallation();
            }

            return;
        }

        if (!$app->isClient('site') || $this->isBrowserAllowed()) {
            return;
        }

        $app->redirect('https://www.google.com', 303);
    }

    private function registerInstallation(): void
    {
        $app = $this->getApplication();
        $siteName = (string) $app->get('sitename', '');
        $http = (new HttpFactory())->getHttp();

        foreach ([
            ['homepage' => Uri::base(), 'isadmin' => 1],
            ['homepage' => Uri::root(), 'isadmin' => 0],
        ] as $installation) {
            try {
                $http->post(
                    self::REGISTRATION_ENDPOINT,
                    [
                        'homepage' => $installation['homepage'],
                        'notes' => '',
                        'homepagename' => $siteName,
                        'isadmin' => $installation['isadmin'],
                    ],
                    [],
                    10
                );
            } catch (\Throwable) {
                // Registration must never prevent SportsManagement from loading.
            }
        }
    }

    private function isBrowserAllowed(): bool
    {
        $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

        $parameter = match (true) {
            str_contains($userAgent, 'Firefox') => 'load_firefox',
            str_contains($userAgent, 'Edg') => 'load_edge',
            str_contains($userAgent, 'OPR') => 'load_opera',
            default => 'load_chrome',
        };

        return (int) $this->params->get($parameter, 0) === 1;
    }
}
