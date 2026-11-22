<?php
/**
 * Native Joomla 5/6 landing model for the XML import workflow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 landing model for the XML import workflow. */
final class JlxmlimportsModel extends BaseDatabaseModel
{
    /**
     * Configure the XML import model's SportsManagement database service.
     *
     * Use the component's database resolver to select the supported Joomla
     * connection and pass it to the core BaseDatabaseModel setter.
     * This keeps the XML import entry point independent of removed Joomla
     * global database factories.
     *
     * @param DatabaseInterface $db Joomla's default database connection.
     * @return void The database service on the model is replaced.
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        parent::setDatabase((new SportsManagementDatabaseResolver())->resolve(null, $db));
    }
}
