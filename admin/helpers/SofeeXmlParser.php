<?php
/**
 * File-level attribution and license; original notices are preserved below.
 *
 * @version    $Revision: 1.1 $
 * @author    Justin Wu <wenlong@php.net>
 * @copyright    Copyright (c) 2004-2005 Sofee Development Team.(http://www.sofee.cn)
 * @license    GNU Lesser General Public License version 2.1 or later
 */
/*

  +----------------------------------------------------------------------+
  | Sofee Framework For PHP4                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 2004-2005 The Sofee Development Team                   |
  +----------------------------------------------------------------------+
  | This source file is subject to the GNU Lesser Public License (LGPL), |
  | that is bundled with this package in the file LICENSE, and is        |
  | available at through the world-wide-web at                           |
  | http://www.fsf.org/copyleft/lesser.html                              |
  | If you did not receive a copy of the LGPL and are unable to          |
  | obtain it through the world-wide-web, you can get it by writing the  |
  | Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, |
  | MA 02111-1307, USA.                                                  |
  +----------------------------------------------------------------------+
  | Author: Justin Wu <ezdevelop@gmail.com>                              |
  +----------------------------------------------------------------------+
*/

// $Id: SofeeXmlParser.php,v 1.3 2005/05/30 06:30:14 wenlong Exp $

/**
 * Sofee XML Parser class - This is an XML parser based on PHP's "xml" extension.
 *
 * The SofeeXmlParser class provides a very simple and easily usable toolset to convert XML
 * to an array that can be processed with array iterators.
 *
 * @package   SofeeFramework
 * @access    public
 * @version   $Revision: 1.1 $
 * @author    Justin Wu <wenlong@php.net>
 * @homepage  http://www.sofee.cn
 * @copyright Copyright (c) 2004-2005 Sofee Development Team.(http://www.sofee.cn)
 * @license   GNU Lesser General Public License version 2.1 or later
 * @since     2005-05-30
 * @see       PEAR:XML_Parser | SimpleXML extension
 */

defined('_JEXEC') or die('Restricted access');

class SofeeXmlParser
{
	/**
	 * XML parser handle
	 *
	 * @var resource|object|null
	 * @see xml_parser_create()
	 */
	var $parser;

	/**
	 * source encoding
	 *
	 * @var string|null
	 */
	var $srcenc;

	/**
	 * target encoding
	 *
	 * @var string|null
	 */
	var $dstenc;

	/**
	 * the original struct
	 *
	 * @access private
	 * @var    array
	 */
	var $_struct = array();

	/**
	 * Number of parsed XML structure entries.
	 *
	 * Declared explicitly to avoid PHP 8.2 dynamic-property deprecations.
	 *
	 * @var int
	 */
	var $_count = 0;

	/**
	 * PHP 8 compatible constructor.
	 *
	 * @param mixed $srcenc source encoding
	 * @param mixed $dstenc target encoding
	 */
	public function __construct($srcenc = null, $dstenc = null)
	{
		$this->srcenc = $srcenc;
		$this->dstenc = $dstenc;
		$this->parser = null;
		$this->_struct = array();
		$this->_count = 0;
	}

	/**
	 * Historical PHP 4 constructor retained for callers invoking it explicitly.
	 *
	 * @deprecated Use __construct().
	 */
	public function SofeeXmlParser($srcenc = null, $dstenc = null): void
	{
		$this->__construct($srcenc, $dstenc);
	}

	/**
	 * Load an XML source and parse it into the historical Sofee tree structure.
	 *
	 * Rejects unreadable files before calling the XML extension and reports
	 * a recoverable exception instead of terminating the entire Joomla
	 * administrator request. Empty inputs are passed to the XML parser so
	 * that malformed-document handling remains consistent.
	 *
	 * @param string $file Path of the XML file being imported.
	 * @return void Updates the parser's stored element structure.
	 * @throws \RuntimeException When the file is unavailable or invalid.
	 */
	function parseFile($file)
	{
		if (!is_string($file) || $file === '') {
			throw new \InvalidArgumentException('A valid XML import file path is required.');
		}

		$data = @file_get_contents($file);

		if ($data === false) {
			throw new \RuntimeException('Cannot read the XML import file.');
		}

		$this->parseString($data);
	}

	/**
	 * Parse XML text using Expat and store the flattened element structure.
	 *
	 * Initialises the configured source and target encodings, preserves the
	 * original case-sensitive element names and releases the native parser
	 * after success or failure. Invalid XML raises an exception instead of
	 * printing diagnostics followed by exit(), allowing Joomla to report it.
	 * Clears previous data before each import to prevent stale tree records.
	 *
	 * @param string $data XML document contents to parse.
	 * @return void Populates the internal element table and count.
	 * @throws \RuntimeException For invalid encodings or malformed XML.
	 */
	function parseString($data)
	{
		if (!is_string($data)) {
			throw new \InvalidArgumentException('XML import data must be a string.');
		}

		$this->_struct = array();
		$this->_count = 0;

		try {
			$this->parser = $this->srcenc === null
				? xml_parser_create()
				: xml_parser_create($this->srcenc);

			if ($this->dstenc !== null) {
				if (!xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->dstenc)) {
					throw new \RuntimeException('Invalid XML target encoding.');
				}
			}

			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);

			if (!xml_parse_into_struct($this->parser, $data, $this->_struct)) {
				throw new \RuntimeException(
					'XML parsing failed at line ' . xml_get_current_line_number($this->parser)
					. ': ' . xml_error_string(xml_get_error_code($this->parser))
				);
			}

			$this->_count = count($this->_struct);
		} finally {
			$this->free();
		}
	}

	/**
	 * Free the resources
	 *
	 * @access public
	 * @return void
	 **/
	function free()
	{
		if ($this->parser !== null)
		{
			xml_parser_free($this->parser);
			$this->parser = null;
		}
	}

	/**
	 * return the data struction
	 *
	 * @access public
	 * @return array
	 */
	function getTree()
	{
		if ($this->_count === 0 || !isset($this->_struct[0]['tag'])) {
			return array();
		}

		$i    = 0;
		$tree = array();

		$tree = $this->addNode(
			$tree,
			$this->_struct[$i]['tag'],
			(isset($this->_struct[$i]['value'])) ? $this->_struct[$i]['value'] : '',
			(isset($this->_struct[$i]['attributes'])) ? $this->_struct[$i]['attributes'] : '',
			$this->getChild($i)
		);

		unset($this->_struct);

		return ($tree);
	}

	/**
	 * Appends some values to an array
	 *
	 * @access public
	 *
	 * @param   array  $target
	 * @param   string $key
	 * @param   string $value
	 * @param   array  $attributes
	 * @param   array  $child the children
	 *
	 * @return array
	 */
	function addNode($target, $key, $value = '', $attributes = '', $child = '')
	{
		if (!isset($target[$key]['value']) && !isset($target[$key][0]))
		{
			if ($child != '')
			{
				$target[$key] = $child;
			}

			if ($attributes != '')
			{
				foreach ($attributes as $k => $v)
				{
					$target[$key][$k] = $v;
				}
			}

			$target[$key]['value'] = $value;
		}
		else
		{
			if (!isset($target[$key][0]))
			{
				$oldvalue        = $target[$key];
				$target[$key]    = array();
				$target[$key][0] = $oldvalue;
				$index           = 1;
			}
			else
			{
				$index = count($target[$key]);
			}

			if ($child != '')
			{
				$target[$key][$index] = $child;
			}

			if ($attributes != '')
			{
				foreach ($attributes as $k => $v)
				{
					$target[$key][$index][$k] = $v;
				}
			}

			$target[$key][$index]['value'] = $value;
		}

		return $target;
	}

	/**
	 * recursion the children node data
	 *
	 * @access public
	 *
	 * @param   integer $i the last struct index
	 *
	 * @return array|null
	 */
	function getChild(&$i)
	{
		$children = array();

		while (++$i < $this->_count)
		{
			$tagname    = $this->_struct[$i]['tag'];
			$value      = isset($this->_struct[$i]['value']) ? $this->_struct[$i]['value'] : '';
			$attributes = isset($this->_struct[$i]['attributes']) ? $this->_struct[$i]['attributes'] : '';

			switch ($this->_struct[$i]['type'])
			{
				case 'open':
					$child = $this->getChild($i);
					$children = $this->addNode($children, $tagname, $value, $attributes, $child);
					break;
				case 'complete':
					$children = $this->addNode($children, $tagname, $value, $attributes);
					break;
				case 'cdata':
					$children['value'] = ($children['value'] ?? '') . $value;
					break;
				case 'close':
					return $children;
			}
		}

		return $children;
	}
}
