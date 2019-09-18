<?php

namespace srag\DIC\H5P\Database;

use ilDBPdo;
use ilDBPdoInterface;
use PDO;
use srag\DIC\H5P\Exception\DICException;

/**
 * Class PdoContextHelper
 *
 * @package srag\DIC\H5P\Database
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @internal
 */
final class PdoContextHelper extends ilDBPdo {

	/**
	 * @param ilDBPdoInterface $db
	 *
	 * @return PDO
	 *
	 * @throws DICException PdoContextHelper only supports ilDBPdo!
	 *
	 * @internal
	 */
	public static function getPdo(ilDBPdoInterface $db) {
		if (!($db instanceof ilDBPdo)) {
			throw new DICException("PdoContextHelper only supports ilDBPdo!");
		}

		return $db->pdo;
	}


	/**
	 * PdoContextHelper constructor
	 */
	private function __construct() {

	}


	/**
	 * @inheritdoc
	 */
	public function initHelpers() {

	}
}
