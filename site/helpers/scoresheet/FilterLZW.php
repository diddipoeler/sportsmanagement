<?php
/**
 * FPDI LZW stream filter bundled with SportsManagement's FPDM copy.
 *
 * @version    1.3.4
 * @author     Jan Slabon, Setasign
 * @copyright  Copyright 2004-2010 Setasign - Jan Slabon
 * @license    Apache License, Version 2.0
 */
//
//  FPDI - Version 1.3.4
//
//    Copyright 2004-2010 Setasign - Jan Slabon
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//
\defined('_JEXEC') or die;

$__tmp = version_compare(phpversion(), "5") == -1 ? array('FilterLZW') : array('FilterLZW', false);
if (!call_user_func_array('class_exists', $__tmp))
{

	if (isset($FPDM_FILTERS)) array_push($FPDM_FILTERS, "LZWDecode");

	class FilterLZW
	{

		var $sTable = array();
		var $data = null;
		var $dataLength = 0;
		var $tIdx;
		var $bitsToGet = 9;
		var $bytePointer;
		var $bitPointer;
		var $nextData = 0;
		var $nextBits = 0;
		var $andTable = array(511, 1023, 2047, 4095);

		/**
		 * Terminate decoding when the legacy LZW PDF filter encounters an error.
		 * Maintains the original filter contract of stopping rather than producing partial invalid PDF bytes.
		 * @param string $msg Description of the unsupported or malformed input.
		 * @return never Terminates the current request.
		 */
		function error($msg)
		{
			die($msg);
		}

		/**
		 * Method to decode LZW compressed data.
		 *
		 * @param   string data    The compressed data.
		 */
		function decode($data)
		{

			if (strlen($data) >= 2 && $data[0] === "\x00" && $data[1] === "\x01")
			{
				$this->error('LZW flavour not supported.');
			}

			$this->initsTable();

			$this->data       = $data;
			$this->dataLength = strlen($data);

			// Initialize pointers
			$this->bytePointer = 0;
			$this->bitPointer  = 0;

			$this->nextData = 0;
			$this->nextBits = 0;

			$oldCode = 0;

			$string     = '';
			$uncompData = '';

			while (($code = $this->getNextCode()) != 257)
			{
				if ($code == 256)
				{
					$this->initsTable();
					$code = $this->getNextCode();

					if ($code == 257)
					{
						break;
					}

					$uncompData .= $this->sTable[$code];
					$oldCode    = $code;

				}
				else
				{

					if ($code < $this->tIdx)
					{
						$string     = $this->sTable[$code];
						$uncompData .= $string;

						$this->addStringToTable($this->sTable[$oldCode], $string[0]);
						$oldCode = $code;
					}
					else
					{
						$string     = $this->sTable[$oldCode];
						$string     = $string . $string[0];
						$uncompData .= $string;

						$this->addStringToTable($string);
						$oldCode = $code;
					}
				}
			}

			return $uncompData;
		}


		/**
		 * Initialize the string table.
		 */
		function initsTable()
		{
			$this->sTable = array();

			for ($i = 0; $i < 256; $i++)
				$this->sTable[$i] = chr($i);

			$this->tIdx      = 258;
			$this->bitsToGet = 9;
		}

		/**
		 * Add a new string to the string table.
		 */
		function addStringToTable($oldString, $newString = '')
		{
			$string = $oldString . $newString;

			// Add this new String to the table
			$this->sTable[$this->tIdx++] = $string;

			if ($this->tIdx == 511)
			{
				$this->bitsToGet = 10;
			}
			else if ($this->tIdx == 1023)
			{
				$this->bitsToGet = 11;
			}
			else if ($this->tIdx == 2047)
			{
				$this->bitsToGet = 12;
			}
		}

		// Returns the next 9, 10, 11 or 12 bits
		/**
		 * Read the next variable-width LZW code from the compressed byte stream.
		 * Maintains the bit buffer and code width, returning the end marker when a stream has no complete additional code; avoids reading past truncated input.
		 * @return int The next 9- to 12-bit code or the end-of-stream marker 257.
		 */
		function getNextCode()
		{
			if ($this->bytePointer >= $this->dataLength)
			{
				return 257;
			}

			$this->nextData = ($this->nextData << 8) | (ord($this->data[$this->bytePointer++]) & 0xff);
			$this->nextBits += 8;

			if ($this->nextBits < $this->bitsToGet)
			{
				// A truncated stream may end with just one partial code byte.
				// Do not read beyond it on PHP 8; treat it as end of stream.
				if ($this->bytePointer >= $this->dataLength)
				{
					return 257;
				}

				$this->nextData = ($this->nextData << 8) | (ord($this->data[$this->bytePointer++]) & 0xff);
				$this->nextBits += 8;
			}

			$code           = ($this->nextData >> ($this->nextBits - $this->bitsToGet)) & $this->andTable[$this->bitsToGet - 9];
			$this->nextBits -= $this->bitsToGet;

			return $code;
		}

		/**
		 * Report that this bundled LZW filter cannot encode PDF data.
		 * Signals the unsupported operation through the historical terminal error method.
		 * @param string $in Raw PDF stream data that cannot be encoded by this filter.
		 * @return never Encoding is unsupported and terminates execution.
		 */
		function encode($in)
		{
			$this->error("LZW encoding not implemented.");
		}
	}
}

unset($__tmp);
