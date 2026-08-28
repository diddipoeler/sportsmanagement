<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 landing model for the XML import workflow. */
final class JlxmlimportsModel extends BaseDatabaseModel
{
    public function setDatabase(DatabaseInterface $db): void
    {
        parent::setDatabase((new SportsManagementDatabaseResolver())->resolve(null, $db));
    }
}
