<?php

namespace srag\Plugins\H5P\Content\Editor;

use H5PCore;
use H5peditorFile;
use H5peditorStorage as H5peditorStorageInterface;
use ilH5PPlugin;
use srag\DIC\H5P\DICTrait;
use srag\Plugins\H5P\Utils\H5PTrait;

/**
 * Class EditorStorage
 *
 * @package srag\Plugins\H5P\Content\Editor
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class EditorStorage implements H5peditorStorageInterface
{

    use DICTrait;
    use H5PTrait;

    const PLUGIN_CLASS_NAME = ilH5PPlugin::class;
    /**
     * @var self|null
     */
    protected static $instance = null;


    /**
     * EditorStorage constructor
     */
    private function __construct()
    {

    }


    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param H5peditorFile $file
     * @param int|null      $content_id
     */
    public static function markFileForCleanup($file, $content_id = null)
    {
        $path = self::h5p()->objectSettings()->getH5PFolder();

        if (empty($content_id)) {
            $path .= "/editor/";
        } else {
            $path .= "/content/" . $content_id . "/";
        }
        $path .= $file->getType() . "s/" . $file->getName();

        $h5p_tmp_file = self::h5p()->contents()->editor()->factory()->newTmpFileInstance();

        $h5p_tmp_file->setPath($path);

        self::h5p()->contents()->editor()->storeTmpFile($h5p_tmp_file);
    }


    /**
     * Clean up temporary files
     *
     * @param string $file_path Path to file or directory
     */
    public static function removeTemporarilySavedFiles($file_path)
    {
        if (file_exists($file_path)) {
            if (is_dir($file_path) && !is_link($file_path)) {
                H5PCore::deleteFileTree($file_path);
            } else {
                unlink($file_path);
            }
        }
    }


    /**
     * Saves a file or moves it temporarily. This is often necessary in order to
     * validate and store uploaded or fetched H5Ps.
     *
     * @param string  $data      Uri of data that should be saved as a temporary file
     * @param boolean $move_file Can be set to TRUE to move the data instead of saving it
     *
     * @return bool|object Returns false if saving failed or the path to the file
     *  if saving succeeded
     */
    public static function saveFileTemporarily($data, $move_file)
    {
        $path = self::h5p()->contents()->framework()->getUploadedH5pPath();

        if ($move_file) {
            rename($data, $path);
        } else {
            file_put_contents($path, $data);
        }

        return (object) [
            "dir"      => dirname($path),
            "fileName" => basename($path)
        ];
    }


    /**
     * Alter styles and scripts
     *
     * @param array $files
     *  List of files as objects with path and version as properties
     * @param array $libraries
     *  List of libraries indexed by machineName with objects as values. The objects
     *  have majorVersion and minorVersion as properties.
     */
    public function alterLibraryFiles(&$files, $libraries)
    {

    }


    /**
     * Load a list of available language codes from the database.
     *
     * @param string $machineName  The machine readable name of the library(content type)
     * @param int    $majorVersion Major part of version number
     * @param int    $minorVersion Minor part of version number
     *
     * @return array List of possible language codes
     */
    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion)
    {
        return self::h5p()->libraries()->getAvailableLanguages($machineName, $majorVersion, $minorVersion);
    }


    /**
     * Load language file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $machine_name  The machine readable name of the library(content type)
     * @param int    $major_version Major part of version number
     * @param int    $minor_version Minor part of version number
     * @param string $language      Language code
     *
     * @return string|false Translation in JSON format
     */
    public function getLanguage($machine_name, $major_version, $minor_version, $language)
    {
        return self::h5p()->libraries()->getTranslationJson($machine_name, $major_version, $minor_version, $language);
    }


    /**
     * Decides which content types the editor should have.
     *
     * Two usecases:
     * 1. No input, will list all the available content types.
     * 2. Libraries supported are specified, load additional data and verify
     * that the content types are available. Used by e.g. the Presentation Tool
     * Editor that already knows which content types are supported in its
     * slides.
     *
     * @param array|null $libraries List of library names + version to load info for
     *
     * @return array List of all libraries loaded
     */
    public function getLibraries($libraries = null)
    {
        $super_user = self::h5p()->contents()->framework()->hasPermission("manage_h5p_libraries");

        if ($libraries !== null) {
            $librariesWithDetails = [];

            foreach ($libraries as $library) {
                $h5p_library = self::h5p()->libraries()->getLibraryByVersion($library->name, $library->majorVersion, $library->minorVersion);

                if ($h5p_library !== null) {
                    $library->tutorialUrl = $h5p_library->getTutorialUrl();
                    $library->title = $h5p_library->getTitle();
                    $library->runnable = $h5p_library->canRunnable();
                    $library->restricted = ($super_user ? false : $h5p_library->isRestricted());
                    $library->metadataSettings = $h5p_library->getMetadataSettings();
                    $librariesWithDetails[] = $library;
                }
            }

            return $librariesWithDetails;
        } else {
            $h5p_libraries = self::h5p()->libraries()->getLatestLibraryVersions();

            $libraries = [];

            foreach ($h5p_libraries as $h5p_library) {
                $library = (object) [
                    "name"             => $h5p_library->getName(),
                    "title"            => $h5p_library->getTitle(),
                    "majorVersion"     => $h5p_library->getMajorVersion(),
                    "minorVersion"     => $h5p_library->getMinorVersion(),
                    "tutorialUrl"      => $h5p_library->getTutorialUrl(),
                    "restricted"       => ($super_user ? false : $h5p_library->isRestricted()),
                    "metadataSettings" => $h5p_library->getMetadataSettings()
                ];

                foreach ($libraries as $key => $existingLibrary) {
                    if ($library->name === $existingLibrary->name) {
                        if (($library->majorVersion === $existingLibrary->majorVersion && $library->minorVersion > $existingLibrary->minorVersion)
                            || ($library->majorVersion > $existingLibrary->majorVersion)
                        ) {
                            $existingLibrary->isOld = true;
                        } else {
                            $library->isOld = true;
                        }
                    }
                }

                $libraries[] = $library;
            }

            return $libraries;
        }
    }


    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param int $file_id
     */
    public function keepFile($file_id)
    {
        $h5p_tmp_files = self::h5p()->contents()->editor()->getFilesByPath($file_id);

        foreach ($h5p_tmp_files as $h5p_tmp_file) {
            self::h5p()->contents()->editor()->deleteTmpFile($h5p_tmp_file);
        }
    }
}
