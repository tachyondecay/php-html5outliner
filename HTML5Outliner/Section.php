<?php
namespace HTML5Outliner;

class Section {

	/**
	 * The heading content element associated with this section
	 * @var string
	 */
	public $heading = null;

	/**
	 * Subsections
	 * @var array
	 */
	public $children = array();


	/**
	 * The section that contains this section, if any
	 * @var Section
	 */
	public $parent_section = null;


	/**
	 * An array of nodes associated with this section
	 * @var array
	 */
	public $associated_nodes = array();


	/**
	 * Add a subsection
	 * @param  Section $section
	 * @return void           
	 */
	public function appendChild(Section $section) {
		$section->parent_section = $this;
		$this->children[] = $section;
	}
}