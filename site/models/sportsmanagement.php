<?php
/**
 * Joomla 5/6 compatibility model for the historic SportsManagement frontend root view.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright (C) 2013-2026 Fussball in Europa
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\LegacySportsmanagementTable;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\DatabaseInterface;

/**
 * sportsmanagementModelsportsmanagement
 *
 * Historic site model retained for compatibility with legacy callers.
 */
class sportsmanagementModelsportsmanagement extends ItemModel
{
    /** @var object|null */
    protected $item;

    /**
     * Return the historic SportsManagement compatibility table without using
     * Joomla's removed static Table::getInstance() factory.
     */
    public function getTable($type = 'sportsmanagement', $prefix = 'sportsmanagementTable', $config = array())
    {
        if (!class_exists(LegacySportsmanagementTable::class)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/LegacySportsmanagementTable.php';
        }

        if (!class_exists(LegacySportsmanagementTable::class)) {
            throw new \RuntimeException('SportsManagement legacy table bridge could not be loaded.', 500);
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return new LegacySportsmanagementTable($db);
    }

    /**
     * Get the historic item payload.
     */
    public function getItem($pk = null)
    {
        return $this->item;
    }

    /**
     * Method to auto-populate the model state.
     */
    protected function populateState()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $this->setState('message.id', $input->getInt('id'));
        $this->setState('params', $app->getParams());

        parent::populateState();
    }
}
