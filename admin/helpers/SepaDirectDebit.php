<?php
/**
 * PHP-Classs to generate SEPA Direct Debit XML Files (SEPA Basis-Lastschrift)
 *
 * (c) 2013 by Roger Sennert, Blue Star Software
 *
 * Term of use:
 * - You are NOT allowed to redistribute this class.
 * - You can use this class as long as you credit to us.
 *   When using in a web-service or in a web-app you have to add a notice to your Impressum (or something similar)
 *   that you are using this class AND link to http://www.bluestarsoftware.de
 * - If you modify the code please send us your modifications
 *   If we think that the modifications are useful we'll add them to this class and credit to you
 *
 * This class is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * If you have any questions please use the contact form on our website: http://www.bluestarsoftware.de/en/contact.html
 *
 * beispielseite: http://www.bluestarsoftware.de/de/beitraege/sepa-lastschrift.html
 *
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace BlueStarSoftware\SEPA;

defined('_JEXEC') or die('Restricted access');

class PropTool
{

	public static function transfer($destObj, $sourceArray, $propList)
	{
		foreach ($propList as $propName)
		{
			if (isset($sourceArray[$propName]))
			{
				$destObj->$propName = $sourceArray[$propName];
			}
		}
	}
}


class Creditor
{
	private static $propList = array('name', 'iban', 'bic', 'identifier');

	public $name = '';

	public $iban = '';

	public $bic = '';

	public $identifier = '';       // Creditor Identifier (Glaeubiger_Identifikationsnummer)

	function __construct($initData = '')
	{
		if (is_array($initData))
		{
			$this->setInfo($initData);
		}
	}

	public function setInfo(array $data)
	{
		PropTool::transfer($this, $data, self::$propList);
	}

	public function addXML(\SimpleXMLElement &$xml)     // Pass by ref!
	{
		$xml->addChild('Cdtr')->addChild('Nm', $this->name);
		$xml->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $this->iban);
		$xml->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$xml->addChild('ChrgBr', 'SLEV');
		$schme = $xml->addChild('CdtrSchmeId');
		$schme->addChild('Nm', $this->name);
		$Othr = $schme->addChild('Id')->addChild('PrvtId')->addChild('Othr');
		$Othr->addChild('Id', $this->identifier);
		$Othr->addChild('SchmeNm')->addChild('Prtry', 'SEPA');
	}
}

;


class Debtor
{
	private static $propList = array('mandateID', 'name', 'iban', 'bic', 'amount', 'currency', 'info', 'ultimateDebtor', 'mandateDateOfSignature', 'transferID');

	public $transferID = '';                 // End2End ID

	public $mandateID = '';                  // Muss Mandaten vorher mitgeteilt werden und dann immer gleich

	public $mandateDateOfSignature = '';     // Wann Mandat unterzeichnet hat ISODATE

	public $name = '';

	public $iban = '';

	public $bic = '';

	public $amount = 0.0;

	public $currency = 'EUR';

	public $info = '';                 // Remittance Information (max 140 Chars.!!!!)

	public $ultimateDebtor = '';       // Zahlungspflichtiger sofern abweichend vom Kontoinhaber, z.B. Kind des Kontoinhabers


	function __construct($initData = '')
	{
		if (is_array($initData))
		{
			$this->setInfo($initData);
		}
	}

	public function setInfo(array $data)
	{
		PropTool::transfer($this, $data, self::$propList);
	}

	public function addXML(\SimpleXMLElement &$xml)     // Pass by ref!
	{
		$inf = $xml->addChild('DrctDbtTxInf');
		$inf->addChild('PmtId')->addChild('EndToEndId', $this->transferID);

		$amt = $inf->addChild('InstdAmt', $this->amount);
		$amt->addAttribute('Ccy', $this->currency);

		$mnd = $inf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
		$mnd->addChild('MndtId', $this->mandateID);

		if ($this->mandateDateOfSignature != '')
		{
			$mnd->addChild('DtOfSgntr', $this->mandateDateOfSignature);
		}

		$mnd->addChild('AmdmntInd', 'false');

		$inf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->bic);
		$inf->addChild('Dbtr')->addChild('Nm', $this->name);
		$inf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', $this->iban);

		if ($this->ultimateDebtor != '')
		{
			$inf->addChild('UltmtDbtr')->addChild('Nm', $this->ultimateDebtor);
		}

		$inf->addChild('RmtInf')->addChild('Ustrd', $this->info);
	}

}


class SepaDirectDebit
{
	private static $propList = array('messageID', 'paymentID', 'initiator', 'sequenceType', 'creationDateTime', 'requestedCollectionDate');

	private static $seqTypes = array('FNAL', 'FRST', 'OOFF', 'RCUR');

	public $messageID = '';

	public $paymentID = '';

	public $initiator = '';

	public $creditor = null;

	public $debtorList = array();

	public $sequenceType = 'OOFF';     // Einmallastschrift

	public $creationDateTime;                // DateTime

	public $requestedCollectionDate;         // DateTime

	function __construct($initData = '')
	{
		$ti = new \DateInterval("P5D");   // 5 Days from now

		$this->creationDateTime        = new \DateTime;
		$this->requestedCollectionDate = new \DateTime;
		$this->requestedCollectionDate->add($ti);

		if (is_array($initData))
		{
			$this->setInfo($initData);
		}
	}

	public function setInfo(array $data)
	{
		PropTool::transfer($this, $data, self::$propList);
	}

	public function setCreditor(Creditor $creditor, $updateInitiator = true)
	{
		$this->creditor = $creditor;

		if ($updateInitiator == true)
		{
			$this->initiator = $this->creditor->name;
		}
	}

	public function addDebtor(Debtor $debtor)
	{
		$this->debtorList[] = $debtor;
	}

	public function toXML()
	{
		libxml_use_internal_errors(true);
		$sxml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-http://www.w3.org/2001/XMLSchema-instance"></Document>');

		if ($this->messageID == '')
		{
			$this->messageID = time();
		}

		$grpHdr = $sxml->addChild('CstmrDrctDbtInitn')->addChild('GrpHdr');
		$grpHdr->addChild('MsgId', $this->messageID);
		$grpHdr->addChild('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s'));
		$grpHdr->addChild('NbOfTxs', count($this->debtorList));
		$grpHdr->addChild('CtrlSum', $this->calcCtrlSum());
		$grpHdr->addChild('InitgPty');
		$grpHdr->InitgPty->addChild('Nm', $this->initiator);

		$pmt = $sxml->CstmrDrctDbtInitn->addChild('PmtInf');

		if ($this->paymentID != '')
		{
			$pmt->addChild('PmtInfId', $this->paymentID);
		}

		$pmt->addChild('PmtMtd', 'DD');

		// DirectDebit
		// $pmt->addChild( 'BtchBookg', 'true' );    // not set => use default value of the bank
		$pmt->addChild('NbOfTxs', count($this->debtorList));
		$pmt->addChild('CtrlSum', $this->calcCtrlSum());

		$pmtpi = $pmt->addChild('PmtTpInf');
		$pmtpi->addChild('SvcLvl')->addChild('Cd', 'SEPA');
		$pmtpi->addChild('LclInstrm')->addChild('Cd', 'CORE');
		$pmtpi->addChild('SeqTp', $this->sequenceType);

		$pmt->addChild('ReqdColltnDt', $this->requestedCollectionDate->format('Y-m-d'));
		$this->creditor->addXML($pmt);

		if (count($this->debtorList) > 0)
		{
			foreach ($this->debtorList as $debtorObj)
			{
				$debtorObj->addXML($pmt);
			}
		}

		return $sxml->asXML();
	}

	private function calcCtrlSum()
	{
		$back = 0.0;

		foreach ($this->debtorList as $debtorObj)
		{
			$back += $debtorObj->amount;
		}

		return $back;
	}
}
