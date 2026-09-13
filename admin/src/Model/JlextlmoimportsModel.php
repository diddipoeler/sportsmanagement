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

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct();
    }

    /**
     * Preserve the historical extension-start marker without removed Joomla 6 APIs.
     */
    public function checkStartExtension(): void
    {
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
