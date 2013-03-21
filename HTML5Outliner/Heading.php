<?php
namespace HTML5Outliner;

class Heading {

	/**
	 * If this is an implied heading
	 * @var boolean
	 */
	public $implied = false;


	/**
	 * The node that represents this heading
	 * @var DOMElement
	 */
	public $node;


	/**
	 * Text content of this heading
	 * @var string
	 */
	public $text;


	/**
	 * The rank of this heading (h1 through h6)
	 * @var integer
	 */
	public $rank;


	/**
	 * Create a new Heading based on a given node. If no node is supplied, 
	 * create an implied heading.
	 * 
	 * @param DOMElement $node
	 */
	public function __construct($node = null) {
		$this->node = $node;

		if(is_null($node)) {
			$this->implied = true;
		} else {
			$this->text = $node->textContent;
			$this->rank = $node->nodeName{1};
		}
	}
}