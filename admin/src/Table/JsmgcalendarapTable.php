<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Asset;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 table for SportsManagement GCalendar action-pack data. */
final class JsmgcalendarapTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_gcalendarap', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['rules']) && is_array($array['rules'])) {
            $this->setRules(new Rules($array['rules']));
        }

        return parent::bind($array, $ignore);
    }

    protected function _getAssetName()
    {
        $key = $this->_tbl_key;

        return 'com_gcalendarap.event.' . (int) $this->{$key};
    }

    protected function _getAssetParentId($table = null, $id = null)
    {
        // ACL assets always live in Joomla's own database, even when
        // SportsManagement uses an external database for competition data.
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $asset = new Asset($joomlaDatabase);
        $asset->loadByName('com_gcalendarap');

        return (int) $asset->id;
    }
}
