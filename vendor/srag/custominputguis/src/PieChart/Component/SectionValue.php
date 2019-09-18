<?php

namespace srag\CustomInputGUIs\H5P\PieChart\Component;

/**
 * Interface SectionValue
 *
 * https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/src/UI/Component/Chart/PieChart/SectionValue.php
 *
 * @package srag\CustomInputGUIs\H5P\PieChart\Component
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface SectionValue {

	/**
	 * Get the actual value
	 *
	 * @return float
	 */
	public function getValue();


	/**
	 * Get the x percentage this value will be displayed at. (On top of the pie chart section)
	 *
	 * @return float
	 */
	public function getXPercentage();


	/**
	 * Get the x percentage this value will be displayed at. (On top of the pie chart section)
	 *
	 * @return float
	 */
	public function getYPercentage();


	/**
	 * Get the size of the value text (On to pof the pie chart section)
	 *
	 * @return int
	 */
	public function getTextSize();
}
