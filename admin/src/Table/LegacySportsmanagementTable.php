<?php
/**
 * Joomla 5/6 administrator legacy SportsManagement core table bridge.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 table for the historic SportsManagement sample record.
 *
 * This table maps Joomla core tables (#__session / #__assets), so it must use
 * Joomla's database connection rather than the optional SportsManagement DB.
 */
final class LegacySportsmanagementTable extends Table
{
    /**
     * Initialise the historical core-table bridge for Joomla's session table using its session identifier as primary key.
     * @param DatabaseInterface $db Joomla core database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__session', 'session_id', $db);
    }

    /**
     * Serialize array-valued parameters through Joomla Registry before delegating record binding to the parent table.
     * @param array|object $array Record data accepted by the parent table.
     * @param array|string $ignore Fields excluded from binding.
     * @return bool Parent binding result.
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Load the selected record through the parent table and decode a string-valued params property into a Registry when present.
     * @param mixed $pk Optional primary key.
     * @param bool $reset Whether to reset table properties before loading.
     * @return bool Whether loading succeeded.
     */
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

    /**
     * Construct the historical component message asset name from the current table primary-key value cast to an integer.
     * @return string Asset name.
     */
    protected function _getAssetName()
    {
        $key = $this->_tbl_key;

        return 'com_sportsmanagement.message.' . (int) $this->{$key};
    }

    /**
     * Return the historical greeting property as the asset title, using an empty string if absent.
     * @return string Asset title.
     */
    protected function _getAssetTitle()
    {
        return (string) ($this->greeting ?? '');
    }

    /**
     * Load the component asset using the same Joomla database connection and return its identifier as the parent asset.
     * @return int Component asset identifier.
     */
    protected function _getAssetParentId()
    {
        $asset = new Asset($this->getDatabase());
        $asset->loadByName('com_sportsmanagement');

        return (int) $asset->id;
    }
}
