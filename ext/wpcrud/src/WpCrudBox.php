<?php

namespace wpcrud;

/**
 * Contains info about one of the categories or "boxes".
 */
class WpCrudBox {

	private $title;
	private $fields=array();
	private $wpCrud;

	/**
	 * Constructor.
	 */
	public function __construct($title=NULL) {
		$this->title=$title;
	}

	/**
	 * Get title.
	 */
	public function getTitle() {
		if (!$this->title)
			throw new Exception("This box doesn't have any title, why do you ask?");

		return $this->title;
	}

	/**
	 * Set reference back to parent.
	 */
	public function setWpCrud($wpCrud) {
		$this->wpCrud=$wpCrud;
	}

	/**
	 * Add field to this box.
	 */
	public function addField($fieldId) {
		if ($this->fields[$fieldId])
			throw new \Exception("Field already added.");

		$this->fields[$fieldId]=new WpCrudFieldSpec($fieldId);

		return $this->fields[$fieldId];
	}

	/**
	 * Get field ids.
	 */
	public function getFieldIds() {
		return array_keys($this->fields);
	}

	/**
	 * Get field spec.
	 */
	public function getFieldSpec($fieldId) {
		return $this->fields[$fieldId];
	}

	/**
	 * Render item form box.
	 */
	public function renderItemFormBox($item) {
		$template=new Template(__DIR__."/../tpl/itemformbox.php");
		$fields=array();

		foreach ($this->getFieldIds() as $fieldId) {
			$fieldspec=$this->getFieldSpec($fieldId);

			$field=array(
				"spec"=>$fieldspec,
				"field"=>$fieldspec->field,
				"label"=>$fieldspec->label,
				"description"=>$fieldspec->description,
				"value"=>$this->wpCrud->getFieldValue($item,$fieldId)
			);

			// pre process fields.
			switch ($fieldspec->type) {
				case "timestamp":
					if ($field["value"]) {
						$oldTz=date_default_timezone_get();
						date_default_timezone_set(get_option('timezone_string'));
						$field["value"]=date("Y-m-d H:i",$field["value"]);
						date_default_timezone_set($oldTz);
					}

					else {
						$field["value"]="";
					}

					break;

				case "media-image":
					$field["src"]=$field["value"];

					if (!$field["src"])
						$field["src"]=site_url()."/wp-includes/images/media/default.png";
					break;
			}

			$fields[]=$field;
		}

		$template->set("fields",$fields);
		$template->set("deleteIconUrl",admin_url('admin-ajax.php')."?action=wpcrud_res&res=img/delete-icon.png");
		$template->set("emptyImageUrl",site_url()."/wp-includes/images/media/default.png");
		$template->show();
	}
}
