<?php

namespace srag\Plugins\H5P\Hub;

use ilCheckboxInputGUI;
use ilH5PConfigGUI;
use ilH5PPlugin;
use ilSelectInputGUI;
use ilTextInputGUI;
use srag\CustomInputGUIs\H5P\PropertyFormGUI\PropertyFormGUI;
use srag\CustomInputGUIs\H5P\TableGUI\TableGUI;
use srag\Plugins\H5P\Utils\H5PTrait;

/**
 * Class HubTableGUI
 *
 * @package srag\Plugins\H5P\Hub
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class HubTableGUI extends TableGUI
{

    use H5PTrait;

    const LANG_MODULE = "";
    const PLUGIN_CLASS_NAME = ilH5PPlugin::class;
    const ROW_TEMPLATE = "hub_table_row.html";


    /**
     * HubTableGUI constructor
     *
     * @param ilH5PConfigGUI $parent
     * @param string         $parent_cmd
     */
    public function __construct(ilH5PConfigGUI $parent, string $parent_cmd)
    {
        parent::__construct($parent, $parent_cmd);
    }


    /**
     * @return string
     */
    public function getHTML() : string
    {
        $form = self::h5p()->hub()->factory()->newUploadLibraryFormInstance($this->parent_obj);

        $hub = self::h5p()->hub()->show()->getHub($form, $this->parent_obj, parent::getHTML());

        return $hub;
    }


    /**
     * @inheritDoc
     */
    public function getSelectableColumns2() : array
    {
        $columns = [];

        return $columns;
    }


    /**
     * @param array $row
     */
    protected function fillRow(/*array*/ $row)/* : void*/
    {
        // Links
        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library_name", $row["name"]);
        $install_link = self::dic()->ctrl()->getLinkTarget($this->parent_obj, ilH5PConfigGUI::CMD_INSTALL_LIBRARY);
        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library_name", null);

        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library_key", $row["key"]);
        $details_link = self::dic()->ctrl()->getLinkTarget($this->parent_obj, ilH5PConfigGUI::CMD_LIBRARY_DETAILS);
        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library_key", null);

        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library", $row["installed_id"]);
        $delete_link = self::dic()->ctrl()->getLinkTarget($this->parent_obj, ilH5PConfigGUI::CMD_DELETE_LIBRARY_CONFIRM);
        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library", null);

        if ($row["icon"] !== "") {
            $this->tpl->setVariableEscaped("ICON", $row["icon"]);
        } else {
            $this->tpl->setVariableEscaped("ICON", self::plugin()->directory() . "/templates/images/h5p_placeholder.svg");
        }

        $this->tpl->setVariableEscaped("LIBRARY", $row["title"]);

        if (isset($row["latest_version"])) {
            $this->tpl->setVariableEscaped("LATEST_VERSION", $row["latest_version"]);
        } else {
            // Library is not available on the hub
            $this->tpl->setVariableEscaped("LATEST_VERSION", self::plugin()->translate("not_available"));
        }

        $actions = [];

        switch ($row["status"]) {
            case ShowHub::STATUS_INSTALLED:
                $this->tpl->setVariableEscaped("STATUS", self::plugin()->translate("installed"));

                $this->tpl->setVariableEscaped("INSTALLED_VERSION", $row["installed_version"]);

                $actions[] = self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("delete"), $delete_link);
                break;

            case ShowHub::STATUS_UPGRADE_AVAILABLE:
                $this->tpl->setVariableEscaped("STATUS", self::plugin()->translate("upgrade_available"));

                $this->tpl->setVariableEscaped("INSTALLED_VERSION", $row["installed_version"]);

                $actions[] = self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("upgrade"), $install_link);

                $actions[] = self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("delete"), $delete_link);
                break;

            case ShowHub::STATUS_NOT_INSTALLED:
                $this->tpl->setVariableEscaped("STATUS", self::plugin()->translate("not_installed"));

                $this->tpl->setVariableEscaped("INSTALLED_VERSION", "-");

                $actions[] = self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("install"), $install_link);
                break;

            default:
                break;
        }

        $this->tpl->setVariableEscaped("RUNNABLE", self::plugin()->translate($row["runnable"] ? "yes" : "no"));

        $this->tpl->setVariableEscaped("CONTENTS", ($row["contents_count"] != 0 ? $row["contents_count"] : ""));
        $this->tpl->setVariableEscaped("USAGE_CONTENTS", ($row["usage_contents"] != 0 ? $row["usage_contents"] : ""));
        $this->tpl->setVariableEscaped("USAGE_LIBRARIES", ($row["usage_libraries"] != 0 ? $row["usage_libraries"] : ""));

        $this->tpl->setVariable("DETAILS_LINK", $details_link);
        $actions[] = self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("details"), $details_link);

        $this->tpl->setVariable("ACTIONS", self::output()->getHTML(self::dic()->ui()->factory()->dropdown()->standard($actions)
            ->withLabel($this->txt("actions"))));

        self::dic()->ctrl()->setParameter($this->parent_obj, "xhfp_library", null);
    }


    /**
     * @inheritDoc
     */
    protected function getColumnValue(string $column, /*array*/ $row, int $format = self::DEFAULT_FORMAT) : string
    {
        switch ($column) {
            default:
                $column = htmlspecialchars($row[$column]);
                break;
        }

        return strval($column);
    }


    /**
     * @inheritDoc
     */
    protected function initColumns()/* : void*/
    {
        $this->addColumn("");
        $this->addColumn(self::plugin()->translate("library"), "title");
        $this->addColumn(self::plugin()->translate("status"), "status");
        $this->addColumn(self::plugin()->translate("installed_version"));
        $this->addColumn(self::plugin()->translate("latest_version"));
        $this->addColumn(self::plugin()->translate("runnable"), "runnable");
        $this->addColumn(self::plugin()->translate("contents"));
        $this->addColumn(self::plugin()->translate("usage_contents"));
        $this->addColumn(self::plugin()->translate("usage_libraries"));
        $this->addColumn(self::plugin()->translate("actions"));
    }


    /**
     * @inheritDoc
     */
    protected function initData()/* : void*/
    {
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $filter = $this->getFilterValues();

        $title = $filter["title"];
        $status = $filter["status"];
        $runnable = ($filter["only_runnable"] ? true : null);
        $not_used = ($filter["only_not_used"] ? true : null);

        $libraries = self::h5p()->hub()->show()->getLibraries($title, $status, $runnable, $not_used);

        $this->setData($libraries);
    }


    /**
     * @inheritDoc
     */
    protected function initFilterFields()/* : void*/
    {
        $this->filter_fields = [
            "title"         => [
                PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
            ],
            "status"        => [
                PropertyFormGUI::PROPERTY_CLASS   => ilSelectInputGUI::class,
                PropertyFormGUI::PROPERTY_OPTIONS => [
                    ShowHub::STATUS_ALL               => self::plugin()->translate("all"),
                    ShowHub::STATUS_INSTALLED         => self::plugin()->translate("installed"),
                    ShowHub::STATUS_UPGRADE_AVAILABLE => self::plugin()->translate("upgrade_available"),
                    ShowHub::STATUS_NOT_INSTALLED     => self::plugin()->translate("not_installed")
                ]
            ],
            "only_runnable" => [
                PropertyFormGUI::PROPERTY_CLASS => ilCheckboxInputGUI::class
            ],
            "only_not_used" => [
                PropertyFormGUI::PROPERTY_CLASS => ilCheckboxInputGUI::class
            ]
        ];

        if (!$this->hasSessionValue("only_runnable")) { // Stupid checkbox
            $this->filter_fields["only_runnable"][PropertyFormGUI::PROPERTY_VALUE] = true;
        }
    }


    /**
     * @inheritDoc
     */
    protected function initId()/* : void*/
    {

    }


    /**
     * @inheritDoc
     */
    protected function initTitle()/* : void*/
    {
        $this->setTitle(self::plugin()->translate("installed_libraries"));
    }
}
