<?php

namespace srag\Plugins\H5P\Utils;

use H5PActionGUI;
use H5PContentValidator;
use H5PCore;
use H5peditor;
use H5PFileStorage;
use H5PStorage;
use H5PValidator;
use ilDatePresentation;
use ilDateTime;
use ilH5PPlugin;
use srag\DIC\H5P\DICTrait;
use srag\Plugins\H5P\Content\Editor\EditorAjax;
use srag\Plugins\H5P\Content\Editor\EditorStorage;
use srag\Plugins\H5P\Content\Editor\ShowEditor;
use srag\Plugins\H5P\Content\ShowContent;
use srag\Plugins\H5P\Framework\Framework;
use srag\Plugins\H5P\Hub\ShowHub;

/**
 * Class H5P
 *
 * @package srag\Plugins\H5P\Utils
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class H5P {

	use DICTrait;
	use H5PTrait;
	const PLUGIN_CLASS_NAME = ilH5PPlugin::class;
	/**
	 * @var self
	 */
	protected static $instance = null;


	/**
	 * @return self
	 */
	public static function getInstance()/*: self*/ {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	const CSV_SEPARATOR = ", ";
    // fau: h5pDataBackup - tweak data folder
	const DATA_FOLDER = "h5p-backup";
	// fau.
	/**
	 * @var H5PActionGUI
	 */
	protected $action = null;
	/**
	 * @var H5PContentValidator
	 */
	protected $content_validator = null;
	/**
	 * @var H5PCore
	 */
	protected $core = null;
	/**
	 * @var H5peditor
	 */
	protected $editor = null;
	/**
	 * @var EditorAjax
	 */
	protected $editor_ajax = null;
	/**
	 * @var EditorStorage
	 */
	protected $editor_storage = null;
	/**
	 * @var H5PFileStorage
	 */
	protected $filesystem = null;
	/**
	 * @var Framework
	 */
	protected $framework = null;
	/**
	 * @var ShowContent
	 */
	protected $show_content = null;
	/**
	 * @var ShowEditor
	 */
	protected $show_editor = null;
	/**
	 * @var ShowHub
	 */
	protected $show_hub = null;
	/**
	 * @var H5PStorage
	 */
	protected $storage = null;
	/**
	 * @var H5PValidator
	 */
	protected $validator = null;


	/**
	 * H5P constructor
	 */
	protected function __construct() {

	}


	/**
	 * @return string
	 */
	public function getH5PFolder() {
		return ILIAS_WEB_DIR . "/" . CLIENT_ID . "/" . self::DATA_FOLDER;
	}


	/**
	 * @return string
	 */
	public function getCorePath() {
		return substr(self::plugin()->directory(), 2) . "/vendor/h5p/h5p-core";
	}


	/**
	 * @return string
	 */
	public function getEditorPath() {
		return substr(self::plugin()->directory(), 2) . "/vendor/h5p/h5p-editor";
	}


	/**
	 * @param string $csvp
	 *
	 * @return string[]
	 */
	public function splitCsv($csv) {
		return explode(self::CSV_SEPARATOR, $csv);
	}


	/**
	 * @param string[] $array
	 *
	 * @return string
	 */
	public function joinCsv(array $array) {
		return implode(self::CSV_SEPARATOR, $array);
	}


	/**
	 * @param int $timestamp
	 *
	 * @return string
	 */
	public function timestampToDbDate($timestamp) {
		$date_time = new ilDateTime($timestamp, IL_CAL_UNIX);

		$formated = $date_time->get(IL_CAL_DATETIME);

		return $formated;
	}


	/**
	 * @param string $formated
	 *
	 * @return int
	 */
	public function dbDateToTimestamp($formated) {
		$date_time = new ilDateTime($formated, IL_CAL_DATETIME);

		$timestamp = $date_time->getUnixTime();

		return $timestamp;
	}


	/**
	 * @param int $time
	 *
	 * @return string
	 */
	public function formatTime($time) {
		$formated_time = ilDatePresentation::formatDate(new ilDateTime($time, IL_CAL_UNIX));

		return $formated_time;
	}


	/**
	 * @return H5PActionGUI
	 */
	public function action() {
		if ($this->action === null) {
			$this->action = new H5PActionGUI();
		}

		return $this->action;
	}


	/**
	 * @return H5PContentValidator
	 */
	public function content_validator() {
		if ($this->content_validator === null) {
			$this->content_validator = new H5PContentValidator($this->framework(), $this->core());
		}

		return $this->content_validator;
	}


	/**
	 * @return H5PCore
	 */
	public function core() {
		if ($this->core === null) {
			$this->core = new H5PCore($this->framework(), $this->getH5PFolder(), ILIAS_HTTP_PATH . "/" . $this->getH5PFolder(), self::dic()->user()
				->getLanguage(), true);
		}

		return $this->core;
	}


	/**
	 * @return H5peditor
	 */
	public function editor() {
		if ($this->editor === null) {
			$this->editor = new H5peditor($this->core(), $this->editor_storage(), $this->editor_ajax());
		}

		return $this->editor;
	}


	/**
	 * @return EditorAjax
	 */
	public function editor_ajax() {
		if ($this->editor_ajax === null) {
			$this->editor_ajax = new EditorAjax();
		}

		return $this->editor_ajax;
	}


	/**
	 * @return EditorStorage
	 */
	public function editor_storage() {
		if ($this->editor_storage === null) {
			$this->editor_storage = new EditorStorage();
		}

		return $this->editor_storage;
	}


	/**
	 * @return H5PFileStorage
	 */
	public function filesystem() {
		if ($this->filesystem === null) {
			$this->filesystem = $this->core()->fs;
		}

		return $this->filesystem;
	}


	/**
	 * @return Framework
	 */
	public function framework() {
		if ($this->framework === null) {
			$this->framework = new Framework();
		}

		return $this->framework;
	}


	/**
	 * @return ShowContent
	 */
	public function show_content() {
		if ($this->show_content === null) {
			$this->show_content = new ShowContent();
		}

		return $this->show_content;
	}


	/**
	 * @return ShowEditor
	 */
	public function show_editor() {
		if ($this->show_editor === null) {
			$this->show_editor = new ShowEditor();
		}

		return $this->show_editor;
	}


	/**
	 * @return ShowHub
	 */
	public function show_hub() {
		if ($this->show_hub === null) {
			$this->show_hub = new ShowHub();
		}

		return $this->show_hub;
	}


	/**
	 * @return H5PStorage
	 */
	public function storage() {
		if ($this->storage === null) {
			$this->storage = new H5PStorage($this->framework(), $this->core());
		}

		return $this->storage;
	}


	/**
	 * @return H5PValidator
	 */
	public function validator() {
		if ($this->validator === null) {
			$this->validator = new H5PValidator($this->framework(), $this->core());
		}

		return $this->validator;
	}
}
