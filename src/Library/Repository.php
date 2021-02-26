<?php

namespace srag\Plugins\H5P\Library;

use ilDBConstants;
use ilH5PPlugin;
use srag\DIC\H5P\DICTrait;
use srag\Plugins\H5P\Content\Content;
use srag\Plugins\H5P\Content\ContentLibrary;
use srag\Plugins\H5P\Utils\H5PTrait;
use stdClass;

/**
 * Class Repository
 *
 * @package srag\Plugins\H5P\Library
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class Repository
{

    use DICTrait;
    use H5PTrait;

    const PLUGIN_CLASS_NAME = ilH5PPlugin::class;
    /**
     * @var self|null
     */
    protected static $instance = null;


    /**
     * Repository constructor
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
     * @param Counter $counter
     */
    public function deleteCounter(Counter $counter)/* : void*/
    {
        $counter->delete();
    }


    /**
     * @param Library $library
     */
    public function deleteLibrary(Library $library)/* : void*/
    {
        $library->delete();
    }


    /**
     * @param LibraryCachedAsset $library_cached_asset
     */
    public function deleteLibraryCachedAsset(LibraryCachedAsset $library_cached_asset)/* : void*/
    {
        $library_cached_asset->delete();
    }


    /**
     * @param LibraryDependencies $library_dependencies
     */
    public function deleteLibraryDependencies(LibraryDependencies $library_dependencies)/* : void*/
    {
        $library_dependencies->delete();
    }


    /**
     * @param LibraryHubCache $library_hub_cache
     */
    public function deleteLibraryHubCache(LibraryHubCache $library_hub_cache)/* : void*/
    {
        $library_hub_cache->delete();
    }


    /**
     * @param LibraryLanguage $library_language
     */
    public function deleteLibraryLanguage(LibraryLanguage $library_language)/* : void*/
    {
        $library_language->delete();
    }


    /**
     * @internal
     */
    public function dropTables()/* : void*/
    {
        self::dic()->database()->dropTable(Library::TABLE_NAME, false);
        self::dic()->database()->dropTable(LibraryCachedAsset::TABLE_NAME, false);
        self::dic()->database()->dropTable(LibraryDependencies::TABLE_NAME, false);
        self::dic()->database()->dropTable(LibraryHubCache::TABLE_NAME, false);
        self::dic()->database()->dropTable(LibraryLanguage::TABLE_NAME, false);
    }


    /**
     * @return Factory
     */
    public function factory() : Factory
    {
        return Factory::getInstance();
    }


    /**
     * @return Library[]
     */
    public function getAddonsLibraries() : array
    {
        /**
         * @var Library[] $h5p_libraries
         */

        $h5p_libraries = Library::where([
            "add_to" => null
        ], "IS NOT")->orderBy("major_version", "asc")->orderBy("minor_version", "asc")->get();

        return $h5p_libraries;
    }


    /**
     * @param string $name
     * @param int    $major_version
     * @param int    $minor_version
     *
     * @return array
     */
    public function getAvailableLanguages(string $name, int $major_version, int $minor_version) : array
    {
        $h5p_library_languages = LibraryLanguage::innerjoin(Library::TABLE_NAME, "library_id", "library_id")->where([
            "name"          => $name,
            "major_version" => $major_version,
            "minor_version" => $minor_version
        ])->getArray();

        $languages = [];

        foreach ($h5p_library_languages as $h5p_library_language) {
            $languages[] = $h5p_library_language["language_code"];
        }

        return $languages;
    }


    /**
     * @param int $library_id
     *
     * @return LibraryCachedAsset[]
     */
    public function getCachedAssetsByLibrary(int $library_id) : array
    {
        /**
         * @var LibraryCachedAsset[] $h5p_cached_assets
         */

        $h5p_cached_assets = LibraryCachedAsset::where([
            "library_id" => $library_id
        ])->get();

        return $h5p_cached_assets;
    }


    /**
     * @param string|null $name
     *
     * @return object|array|null
     */
    public function getContentTypeCache(/*?string*/ $name = null)
    {
        if ($name != null) {
            $library_hub_cache = LibraryHubCache::where([
                "machine_name" => $name
            ])->getArray(null, ["id", "is_recommended"])[0];

            if ($library_hub_cache != null) {
                return (object) $library_hub_cache;
            } else {
                return null;
            }
        } else {
            return array_map(function (array $library_hub_cache) : stdClass {
                return (object) $library_hub_cache;
            }, LibraryHubCache::getArray());
        }
    }


    /**
     * @param string $type
     * @param string $library_name
     * @param string $library_version
     *
     * @return Counter|null
     */
    public function getCounterByLibrary(string $type, string $library_name, string $library_version)/* : ?Counter*/
    {
        /**
         * @var Counter|null $h5p_counter
         */

        $h5p_counter = Counter::where([
            "type"            => $type,
            "library_name"    => $library_name,
            "library_version" => $library_version
        ])->first();

        return $h5p_counter;
    }


    /**
     * @param string $type
     *
     * @return Counter[]
     */
    public function getCountersByType(string $type) : array
    {
        /**
         * @var Counter[] $h5p_counters
         */

        $h5p_counters = Counter::where([
            "type" => $type
        ])->get();

        return $h5p_counters;
    }


    /**
     * @return Library|null
     */
    public function getCurrentLibrary()/* : ?Library*/
    {
        /**
         * @var Library|null $xhfp_library
         */

        $library_id = filter_input(INPUT_GET, "xhfp_library", FILTER_SANITIZE_NUMBER_INT);

        $xhfp_library = $this->getLibraryById($library_id);

        return $xhfp_library;
    }


    /**
     * @param int $library_id
     *
     * @return LibraryDependencies[]
     */
    public function getDependencies(int $library_id) : array
    {
        /**
         * @var LibraryDependencies[] $h5p_library_dependencies
         */

        $h5p_library_dependencies = LibraryDependencies::where([
            "library_id" => $library_id
        ])->get();

        return $h5p_library_dependencies;
    }


    /**
     * @param int $library_id
     *
     * @return array[]
     */
    public function getDependenciesJoin(int $library_id) : array
    {
        /**
         * @var array[] $h5p_library_dependencies
         */

        $h5p_library_dependencies = LibraryDependencies::innerjoin(Library::TABLE_NAME, "required_library_id", "library_id")->where([
            LibraryDependencies::TABLE_NAME . ".library_id" => $library_id
        ])->getArray();

        return $h5p_library_dependencies;
    }


    /**
     * @return LibraryHubCache[]
     */
    public function getHubLibraries() : array
    {
        /**
         * @var LibraryHubCache[] $h5p_hub_libraries
         */

        $h5p_hub_libraries = LibraryHubCache::get();

        return $h5p_hub_libraries;
    }


    /**
     * @param int $library_id
     *
     * @return LibraryLanguage[]
     */
    public function getLanguagesByLibrary(int $library_id) : array
    {
        /**
         * @var LibraryLanguage[] $h5p_languages
         */

        $h5p_languages = LibraryLanguage::where([
            "library_id" => $library_id
        ])->get();

        return $h5p_languages;
    }


    /**
     * @return Library[]
     */
    public function getLatestLibraryVersions() : array
    {
        /**
         * @var Library[] $h5p_libraries
         */

        $h5p_libraries = Library::where([
            "runnable" => true
        ])->orderBy("title", "asc")->orderBy("major_version", "asc")->orderBy("minor_version", "asc")->get();

        return $h5p_libraries;
    }


    /**
     * @return Library[]
     */
    public function getLibraries() : array
    {
        /**
         * @var Library[] $h5p_libraries
         */

        $h5p_libraries = Library::orderBy("title", "asc")->orderBy("major_version", "asc")->orderBy("minor_version", "asc")->get();

        return $h5p_libraries;
    }


    /**
     * @param string $name
     *
     * @return Library[]
     */
    public function getLibraryAllVersions(string $name) : array
    {
        /**
         * @var Library[] $h5p_libraries
         */

        $h5p_libraries = Library::where([
            "name" => $name
        ])->orderBy("major_version", "asc")->orderBy("minor_version", "asc")->get();

        return $h5p_libraries;
    }


    /**
     * @param int $library_id
     *
     * @return Library|null
     */
    public function getLibraryById(int $library_id)/* : ?Library*/
    {
        /**
         * @var Library|null $h5p_library
         */

        $h5p_library = Library::where([
            "library_id" => $library_id
        ])->first();

        return $h5p_library;
    }


    /**
     * @param string $name
     *
     * @return LibraryHubCache|null
     */
    public function getLibraryByName(string $name)/* : ?LibraryHubCache*/
    {
        /**
         * @var LibraryHubCache|null $h5p_hub_library
         */

        $h5p_hub_library = LibraryHubCache::where([
            "machine_name" => $name
        ])->first();

        return $h5p_hub_library;
    }


    /**
     * @param string   $name
     * @param int|null $major_version
     * @param int|null $minor_version
     *
     * @return Library|null
     */
    public function getLibraryByVersion(string $name, /*?int*/ $major_version = null, /*?int*/ $minor_version = null)/* : ?Library*/
    {
        /**
         * @var Library|null $h5p_library
         */

        $where = [
            "name" => $name
        ];

        if ($major_version !== null) {
            $where["major_version"] = $major_version;
        }

        if ($minor_version !== null) {
            $where["minor_version"] = $minor_version;
        }

        $h5p_library = Library::where($where)->orderBy("major_version", "desc")->orderBy("minor_version", "desc")->orderBy("patch_version", "desc")
            ->first(); // Order desc version for the case no version specification to get latest version

        return $h5p_library;
    }


    /**
     * @param int $library_id
     *
     * @return int
     */
    public function getLibraryDependenciesUsage(int $library_id) : int
    {
        /**
         * @var LibraryDependencies[] $h5p_library_dependencies
         */

        $h5p_library_dependencies = LibraryDependencies::where([
            "required_library_id" => $library_id
        ])->get();

        return count($h5p_library_dependencies);
    }


    /**
     * @param int $library_id
     *
     * @return int
     */
    public function getLibraryUsage(int $library_id) : int
    {
        $result = self::dic()->database()->queryF("SELECT COUNT(DISTINCT c.content_id) AS count
          FROM " . Library::TABLE_NAME . " AS l
          JOIN " . ContentLibrary::TABLE_NAME . " AS cl ON l.library_id = cl.library_id
          JOIN " . Content::TABLE_NAME . " AS c ON cl.content_id = c.content_id
          WHERE l.library_id = %s", [ilDBConstants::T_INTEGER], [$library_id]);

        $count = intval($result->fetchAssoc()["count"]);

        return $count;
    }


    /**
     * @param string $name
     * @param int    $major_version
     * @param int    $minor_version
     * @param string $language
     *
     * @return string|false
     */
    public function getTranslationJson(string $name, int $major_version, int $minor_version, string $language)
    {
        /**
         * @var LibraryLanguage $h5p_library_language
         */
        $h5p_library_language = LibraryLanguage::innerjoin(Library::TABLE_NAME, "library_id", "library_id")->where([
            Library::TABLE_NAME . ".name"                  => $name,
            Library::TABLE_NAME . ".major_version"         => $major_version,
            Library::TABLE_NAME . ".minor_version"         => $minor_version,
            LibraryLanguage::TABLE_NAME . ".language_code" => $language
        ])->first();

        if ($h5p_library_language !== null) {
            return $h5p_library_language->getTranslation();
        } else {
            return false;
        }
    }


    /**
     * @param array  $libraries
     * @param string $language_code
     *
     * @return array
     */
    public function getTranslations(array $libraries, string $language_code) : array
    {
        $h5p_library_languages = self::dic()->database()
            ->queryF("SELECT translation, CONCAT(hl.name, ' ', hl.major_version, '.', hl.minor_version) AS lib FROM " . Library::TABLE_NAME
                . " INNER JOIN " . LibraryLanguage::TABLE_NAME . " ON " . Library::TABLE_NAME . ".library_id = " . LibraryLanguage::TABLE_NAME
                . ".library_id WHERE language_code=%s AND " . self::dic()->database()
                    ->in("CONCAT(hl.name, ' ', hl.major_version, '.', hl.minor_version)", $libraries, false, ilDBConstants::T_TEXT), [ilDBConstants::T_TEXT], [$language_code]);

        $languages = [];

        foreach ($h5p_library_languages as $h5p_library_language) {
            $languages[$h5p_library_language["lib"]] = $h5p_library_language["translation"];
        }

        return $languages;
    }


    /**
     * @param int $library_id
     *
     * @return array
     */
    public function getUsageJoin(int $library_id) : array
    {
        /**
         * @var LibraryDependencies[] $h5p_library_usages
         */

        $h5p_library_usages = LibraryDependencies::innerjoin(Library::TABLE_NAME, "library_id", "library_id")->where([
            LibraryDependencies::TABLE_NAME . ".required_library_id" => $library_id
        ])->getArray();

        return $h5p_library_usages;
    }


    /**
     * @internal
     */
    public function installTables()/* : void*/
    {
        Counter::updateDB();
        Library::updateDB();
        LibraryCachedAsset::updateDB();
        LibraryDependencies::updateDB();
        LibraryHubCache::updateDB();
        LibraryLanguage::updateDB();
    }


    /**
     * @param string $name
     * @param int    $major_version
     * @param int    $minor_version
     *
     * @return bool
     */
    public function libraryHasUpgrade(string $name, int $major_version, int $minor_version) : bool
    {
        $result = self::dic()->database()->queryF("SELECT id FROM " . Library::TABLE_NAME
            . " WHERE name=%s AND (major_version>%s OR (major_version=%s AND minor_version>%s))", [
            ilDBConstants::T_TEXT,
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_INTEGER
        ], [$name, $major_version, $major_version, $minor_version]);

        return ($result->fetchAssoc() !== false);
    }


    /**
     * @param Counter $counter
     */
    public function storeCounter(Counter $counter)/* : void*/
    {
        $counter->store();
    }


    /**
     * @param Library $library
     */
    public function storeLibrary(Library $library)/* : void*/
    {
        $time = time();

        if (empty($library->getLibraryId())) {
            $library->setCreatedAt($time);
        }

        $library->setUpdatedAt($time);

        $library->store();
    }


    /**
     * @param LibraryCachedAsset $library_cached_asset
     */
    public function storeLibraryCachedAsset(LibraryCachedAsset $library_cached_asset)/* : void*/
    {
        $library_cached_asset->store();
    }


    /**
     * @param LibraryDependencies $library_dependencies
     */
    public function storeLibraryDependencies(LibraryDependencies $library_dependencies)/* : void*/
    {
        $library_dependencies->store();
    }


    /**
     * @param LibraryHubCache $library_hub_cache
     */
    public function storeLibraryHubCache(LibraryHubCache $library_hub_cache)/* : void*/
    {
        $library_hub_cache->store();
    }


    /**
     * @param LibraryLanguage $library_language
     */
    public function storeLibraryLanguage(LibraryLanguage $library_language)/* : void*/
    {
        $library_language->store();
    }


    /**
     *
     */
    public function truncateLibraryHubCaches()/* : void*/
    {
        LibraryHubCache::truncateDB();
    }
}
