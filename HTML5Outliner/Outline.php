<?php
namespace HTML5Outliner;

class Outline {

	public static $heading_content = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hgroup');
	public static $sectioning_content = array('article', 'aside', 'nav', 'section');
	public static $sectioning_roots = array('blockquote', 'body', 'details', 'dialogue', 'fieldset', 'figure', 'td');


	/**
	 * An array of the root sections of this outline
	 * @var array
	 */
	public $sections = array();


	/**
	 * Create a new Outline for a section
	 * @param string $section The root section for this outline
	 */
	private function __construct($section) {
		$this->sections[] = $section;
	}


	/**
	 * Wrapper function for Outline::build that accepts HTML as a string and 
	 * loads it into a DOMDocument.
	 * @param  string $html The HTML document or sub-document
	 * @return Outline
	 */
	public static function loadHTML($html) {
		// Prevent invalid HTML from triggering PHP's error handler
		libxml_use_internal_errors(true);

		$document = new \DOMDocument();
		$document->loadHTML($_POST['html']);

		libxml_use_internal_errors(false);

		return Outline::build($document->documentElement);
	}

	

	/**
	 * Generate an outline for a section or document
	 * @param  \DOMElement $root Root node for the outline
	 * @return Outline
	 */
	public static function build(\DOMElement $root) {

		/*
		 * Steps 1 and 2:
		 * Initialize current element being outlined and current section pointer.
		 */
		$current_outline_target = null;
		$current_section = null;
		$current_outline = null;
		$outline_stack = array();

		/*
		 * Step 3:
		 * Create a stack to hold elements, used to handle nesting.
		 */
		$stack = array();


		/*
		 * Step 4:
		 * DOM walk
		 */
		$node = $root;
		while($node) {

			if($top = end($stack) && !empty($top) && self::isHeadingContent($top)) {
				// If the top of the stack is a heading content element or an
				// element with a hidden attribute, do nothing
				continue;
			}

			/*
			 * ENTER the node
			 */
			if($node->hasAttributes() && $node->getAttribute('hidden')) {
				// If the element has a 'hidden' attribute, push it onto the stack
				$stack[] = $node;
			} elseif(self::isSectioningContent($node) || self::isSectioningRoot($node)) {
				// Element is a sectioning root or sectioning content element

				if(!is_null($current_outline_target)) {
					if(is_null($current_section->heading)) {
						// If current section has no heading, create an implied heading
						$current_section->heading = new Heading();
					}
					$stack[] = $current_outline_target;
				}

				// Let current outline target be the element that is being entered.
				$current_outline_target = $node;
				$current_section = new Section();

				// Associate current outline target with current section
				$current_section->associated_nodes[] = $node;

				// Start a new outline from this section
				$outline_stack[] = $current_outline;
				$current_outline = new Outline($current_section);

			} elseif(self::isHeadingContent($node)) {
				// Element is a heading content element

				// Get the highest-ranking heading element
				$heading = null;
				if($node->nodeName == 'hgroup') {
					// If this is an hgroup, grab the first heading element and 
					// ignore the rest.
					$remove = array();
					foreach($node->childNodes as $child) {
						if(is_null($heading) && strlen($child->nodeName) == 2 && $child->nodeName{0} == 'h') {
							$heading = new Heading($child);
						}
						$remove[] = $child;
					}

					foreach($remove as $n) {
						$n->parentNode->removeChild($n);
					}
				} else {
					$heading = new Heading($node);
				}


				if(is_null($current_section->heading)) {
					$current_section->heading = $heading;
				} elseif(is_null($current_outline->lastSection()->heading) || $current_outline->lastSection()->heading->implied || $heading->rank <= $current_outline->lastSection()->heading->rank) {
					$current_section = new Section();
					$current_section->heading = $heading;
					$current_outline->sections[] = $current_section;
				} else {
					$candidate = $current_section;
					while(true) {
						if($heading->rank > $candidate->heading->rank) {
							$current_section = new Section();
							$current_section->heading = $heading;
							$candidate->appendChild($current_section);
							break;
						}
						$candidate = $candidate->parent_section;
					}
				}

				// Push heading content onto the stack
				$stack[] = $node;
			}

			/*
			 * Walk all children of this node
			 */
			if(!is_null($node->firstChild)) {
				$node = $node->firstChild;
				continue;
			}

			while($node) {
				/*
				 * EXIT the node
				 */

				// When exiting an element, if that element is at the top of the 
				// stack, pop it from the stack.

				if(end($stack) === $node) {
					array_pop($stack);
				} elseif(!empty($stack) && (self::isSectioningContent($node) || self::isSectioningRoot($node))) {
					$current_outline_target = array_pop($stack);

					// Let current section be the last section in the outline of the
					// current outline target element.
					$previous_outline = array_pop($outline_stack);
					$current_section = $previous_outline->lastSection();

					if(self::isSectioningContent($node)) {
						// Append the outline of the sectioning content element being
						// exited to the current section.
						foreach($current_outline->sections as $s) {
							$current_section->appendChild($s);
						}

						$current_outline = $previous_outline;
					} elseif(self::isSectioningRoot($node)) {
						// Find deepest child
						while(!empty($current_section->children)) {
							$current_section = end($current_section->children);
						}
					}
				}

				if(!is_null($current_section)) {
					$current_section->associated_nodes[] = $node;
				}

				if($node === $root) {
					$node = null;
				} elseif(!is_null($node->nextSibling)) {
					$node = $node->nextSibling;
					break;
				} else {
					$node = $node->parentNode;
				}
			}
		}

		return $current_outline;
	}


	/**
	 * Checks if a node is a sectioning content element
	 * @param  DOMNode $node
	 * @return boolean
	 */
	public static function isSectioningContent(\DOMNode $node) {
		if($node instanceOf \DOMElement) {
			return in_array($node->nodeName, self::$sectioning_content);
		}
		return false;
	}


	/**
	 * Checks if a node is a sectioning root element
	 * @param  DOMNode  $node
	 * @return boolean       
	 */
	public static function isSectioningRoot(\DOMNode $node) {
		if($node instanceOf \DOMElement) {
			return in_array($node->nodeName, self::$sectioning_roots);
		}
		return false;
	}


	/**
	 * Checks if a node is a heading content element
	 * @param  DOMNode  $node
	 * @return boolean       
	 */
	public static function isHeadingContent(\DOMNode $node) {
		if($node instanceOf \DOMElement) {
			return in_array($node->nodeName, self::$heading_content);
		}
		return false;
	}


	/**
	 * Gets the last section of an outline
	 * @return Section
	 */
	public function lastSection() {
		return end($this->sections);
	}


	/**
	 * Helper function that returns a table of contents based on an outline's 
	 * headings.
	 *
	 * @param  array $sections  The sections to get headings for
	 * @param  array $options 	Array of options. Valid options include:
	 *  - 'implied': The label for an implied heading
	 *  - 'container': The HTML element wrapped around a section
	 *  - 'element': The HTML element wrapped around a heading
	 * @return string            An output string
	 */
	public function getHeadings(
		$sections = null, 
		$options = array('implied' => 'Untitled Section', 'container' => 'ol', 'element' => 'li')
	) {
		if(empty($sections)) {
			$sections = $this->sections;
		}

		extract($options);

		$output = null;
		
		foreach($sections as $s) {
			$this_section = (is_null($s->heading) || $s->heading->implied) ? $implied : $s->heading->text;

			if(!empty($s->children)) {
				$this_section .= $this->getHeadings($s->children, $options);
			}
			
			if(!empty($element)) {
				$this_section = "<$element>" . $this_section . "</$element>\n";
			}

			$output .= $this_section;
		}
		
		if(!empty($container)) {
			$output = "<$container>\n" . $output . "</$container>\n";
		}

		return $output;
	}
}