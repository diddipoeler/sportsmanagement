<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Asset;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 table for the historic SportsManagement sample record.
 */
final class LegacySportsmanagementTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__session', 'session_id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    public function load($pk = null, $reset = true)
    {
        if (!parent::load($pk, $reset)) {
            return false;
        }

        if (property_exists($this, 'params') && is_string($this->params)) {
            $registry = new Registry();
            $registry->loadString($this->params);
            $this->params = $registry;
        }

        return true;
    }

    protected function _getAssetName()
    {
        $key = $this->_tbl_key;

        return 'com_sportsmanagement.message.' . (int) $this->{$key};
    }

    protected function _getAssetTitle()
    {
        return (string) ($this->greeting ?? '');
    }

    protected function _getAssetParentId()
    {
        $asset = new Asset($this->getDatabase());
        $asset->loadByName('com_sportsmanagement');

        return (int) $asset->id;
    }
}
