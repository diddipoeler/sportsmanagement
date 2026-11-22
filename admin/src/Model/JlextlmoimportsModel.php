<?php
/**
 * Joomla 5/6 administrator model entry point for the historical LMO importer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\Mail\MailerFactoryAwareInterface;
use Joomla\CMS\Mail\MailerFactoryAwareTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;

LegacyBootstrap::boot();

if (!class_exists('sportsmanagementModeljlextlmoimports', false)) {
    $legacyModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextlmoimports.php';

    if (is_file($legacyModel)) {
        require_once $legacyModel;
    }
}

if (!class_exists('sportsmanagementModeljlextlmoimports', false)) {
    throw new \RuntimeException('Legacy SportsManagement LMO import engine could not be loaded.', 500);
}

/** Native MVCFactory entry point for the LMO importer. */
final class JlextlmoimportsModel extends \sportsmanagementModeljlextlmoimports implements MailerFactoryAwareInterface
{
    use MailerFactoryAwareTrait;

    /**
     * Initialise the legacy LMO importer through Joomla MVC compatibility.
     * @param array $config Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct();
    }

    /**
     * Initialise the historical LMO extension only for component admins.
     *
     * Verify administrator core.admin permission before this public model
     * method sends its legacy notification email and writes the startup
     * marker to Joomla's temporary directory. Skip the notification when
     * the marker already exists, preserving the established one-time flow.
     *
     * @return void No action is taken when the marker already exists.
     * @throws \RuntimeException If the caller lacks component admin access.
     */
    public function checkStartExtension(): void
    {
        // This legacy method sends an email and writes an import marker.
        // Model calls can bypass the administrator controller's ACL checks.
        $identity = \Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver::resolve()
            ->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $fileextension = JPATH_SITE . '/tmp/lmoimport-2-0.txt';

        if (File::exists($fileextension)) {
            return;
        }

        $subject = 'LMO-Import Extension';
        $message = 'LMO-Import Extension wurde auf der Seite : ' . Uri::base() . ' gestartet.';
        $mailer = $this->getMailerFactory()->createMailer();
        $mailer->addRecipient('diddipoeler@gmx.de');
        $mailer->setSubject($subject);
        $mailer->setBody($message);
        $mailer->send();

        File::write($fileextension, $message);
    }
}
