<?php

/**
 * Represents a SwagTrack
 */
class SwagTrack {

	const DEFAULT_COLOR="#339933";

	private $term;

	/**
	 * Construct.
	 */
	private function __construct($term) {
		$this->term=$term;
	}

	/**
	 * Get id.
	 */
	public function getId() {
		return $this->term->term_id;
	}

	/**
	 * Get underlying taxonomy term.
	 */
	public function getTerm() {
		return $this->term;
	}

	/**
	 * Get display color.
	 */
	public function getDisplayColor() {
		if ($this->getColor())
			return $this->getColor();

		$parent=$this->getParent();
		if ($parent && $parent->getColor())
			return $parent->getColor();

		return SwagTrack::DEFAULT_COLOR;
	}

	/**
	 * Get color.
	 */
	public function getColor() {
		if (!isset($this->color))
			$this->color=get_term_meta($this->getId(),"color",TRUE);

		return $this->color;
	}

	/**
	 * Get parent.
	 */
	public function getParent() {
		return SwagTrack::getById($this->getTerm()->parent);
	}

	/**
	 * Get children for a specified parent.
	 */
	public static function getByParentId($parentTrackId) {
		$terms=get_terms(array(
			"taxonomy"=>"swagtrack",
			"hide_empty"=>FALSE,
			"parent"=>$parentTrackId,
		));

		$res=array();
		foreach ($terms as $term)
			$res[]=new SwagTrack($term);

		return $res;
	}

	/**
	 * Get by slug.
	 * Should we use term_id or term_taxonomy_id?
	 * What is term_taxonomy_id and where is it used?
	 */
	public static function getBySlug($trackSlug) {
		if (!$trackSlug)
			return NULL;

		$t=get_terms(array(
			"taxonomy"=>"swagtrack",
			"slug"=>$trackSlug,
			"hide_empty"=>FALSE,
		));

		if (!$t)
			return NULL;

		return new SwagTrack($t[0]);
	}

	/**
	 * Get by id.
	 */
	public static function getById($trackId) {
		if (!$trackId)
			return NULL;

		$t=get_term($trackId);

		if (!$t)
			return NULL;

		return new SwagTrack($t);
	}
}