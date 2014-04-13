<?php


defined('_JEXEC') or die();

JLoader::import('joomla.database.table');
JLoader::import('joomla.utilities.simplecrypt');

class  sportsmanagementTablejsmGCalendar extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__sportsmanagement_gcalendar', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			// Convert the params field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($array['params']);
			$array['params'] = (string)$parameter;
		}

		return parent::bind($array, $ignore);
	}

	public function load($keys = null, $reset = true)
	{
		$result = parent::load($keys, $reset);

		if(isset($this->password) && !empty($this->password)){
			$cryptor = new JSimpleCrypt();
			$this->password = $cryptor->decrypt($this->password);
		}

		return $result;
	}

	public function store($updateNulls = false)
	{
		$oldPassword = $this->password;
		if(!empty($oldPassword)){
			$cryptor = new JSimpleCrypt();
			$this->password = $cryptor->encrypt($oldPassword);
		}
		$result = parent::store($updateNulls);

		$this->password = $oldPassword;

		return $result;
	}
}