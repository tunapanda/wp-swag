<?php

require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
require_once __DIR__."/src/Template.php";
require_once __DIR__."/src/WpCrudFieldSpec.php";
require_once __DIR__."/src/WpCrudBox.php";

use wpcrud\Template;
use wpcrud\WpCrudFieldSpec;
use wpcrud\WpCrudBox;

/**
 * Generic CRUD interface for Wordpress.
 * Implemented using following this example:
 *
 * http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
 *
 * Implementing classes should implement these functions:
 *
 * - getFieldValue
 * - setFieldValue
 * - createItem
 * - getItem
 * - saveItem
 * - deleteItem
 * - getAllItems
 */
abstract class WpCrud extends WP_List_Table {

	private static $scriptsEnqueued;

	private $defaultBox;
	private $listFields;
	private $parentMenuSlug;
	private $typeId;
	private $boxes=array();
	private $config;
	private $flags;

	/**
	 * Constructor.
	 */
	public final function __construct() {
		$this->typeId=strtolower(get_called_class());

		parent::__construct(array(
			"screen"=>$this->typeId
		));

		$this->config=array(
			"typeName"=>get_called_class(),
			"description"=>"",
			"enableCreate"=>TRUE,
			"enableDelete"=>TRUE,
		);

		$this->defaultBox=new WpCrudBox($this->typeId);
		$this->defaultBox->setWpCrud($this);

		$this->init();
	}

	/**
	 * Add a box.
	 */
	public function addBox($title) {
		$box=new WpCrudBox($title);
		$box->setWpCrud($this);
		$this->boxes[]=$box;

		return $box;
	}

	/**
	 * Initialize fields.
	 * Override in subclass.
	 */
	protected function init() {}

	/**
	 * Set parent menu slug.
	 */
	protected function setParentMenuSlug($slug) {
		$this->parentMenuSlug=$slug;
	}

	/**
	 * Add a field to be managed. This function returns a
	 * WpCrudFieldSpec object, it is intended to be used something
	 * like this in init function:
	 *
	 *     $this->addField("myfield")->label("My Field")->...
	 */
	protected function addField($fieldId) {
		return $this->defaultBox->addField($fieldId);
	}

	/**
	 * Which fields should be listable?
	 */
	public function setListFields($fieldNames) {
		$this->listFields=$fieldNames;
	}

	/**
	 * Get field spec.
	 * Internal.
	 */
	private function getFieldSpec($fieldId) {
		$fieldSpec=$this->defaultBox->getFieldSpec($fieldId);
		if ($fieldSpec)
			return $fieldSpec;

		foreach ($this->boxes as $box) {
			$fieldSpec=$box->getFieldSpec($fieldId);
			if ($fieldSpec)
				return $fieldSpec;
		}

		throw new Exception("No such field: ".$fieldId);
	}

	/**
	 * Get edit fields.
	 */
	private function getAllFields() {
		$fieldIds=$this->defaultBox->getFieldIds();

		foreach ($this->boxes as $box) {
			$boxFieldIds=$box->getFieldIds();

			foreach ($boxFieldIds as $boxFieldId)
				$fieldIds[]=$boxFieldId;
		}

		return $fieldIds;
	}

	/**
	 * Get list fields.
	 */
	private function getListFields() {
		if ($this->listFields)
			return $this->listFields;

		return $this->defaultBox->getFieldIds();
	}

	/**
	 * Get columns.
	 * Internal.
	 */
	public function get_columns() {
		$a=array();
		$a["cb"]='<input type="checkbox" />';

		foreach ($this->getListFields() as $fieldId) {
			$fieldspec=$this->getFieldSpec($fieldId);
			$a[$fieldId]=$fieldspec->label;
		}

		return $a;
	}

	/**
	 * Get checkbox column.
	 * Internal.
	 */
	public function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="_bulkid[]" value="%s" />', 
			$this->getFieldValue($item,"id")
		);
	}

	/**
	 * Column value.
	 * This function returns the value shown when listing stuff.
	 */
	public function column_default($item, $column_name) {
		$listFields=$this->getListFields();

		if ($column_name==$listFields[0]) {
			$actions = array(
				'edit' => 
					sprintf('<a href="?page=%s_form&id=%s">%s</a>', 
						$this->typeId, 
						$this->getFieldValue($item,"id"), 
						__('Edit', $this->typeId)
					),
				'delete' => 
					sprintf('<a href="?page=%s&action=delete&id=%s" onclick="return confirm(\'Are you sure? This operation cannot be undone!\');">%s</a>',
						$_REQUEST['page'], 
						$this->getFieldValue($item,"id"),
						__('Delete', $this->typeId)
					),
			);

			return sprintf('%s %s',
				$this->getFieldValue($item,$column_name),
				$this->row_actions($actions)
			);
		}

		$fieldspec=$this->getFieldSpec($column_name);

		if ($fieldspec->type=="select") {
			return $fieldspec->options[$this->getFieldValue($item,$column_name)];
		}

		else if ($fieldspec->type=="timestamp") {
			$v=$this->getFieldValue($item,$column_name);
			if (!$v)
				return "";

			return date('Y-m-d H:i',intval($v));
		}

		else if ($fieldspec->type=="media-image") {
			return sprintf('<img src="%s" class="wpcrud-list-media-image" style="max-width: 50px; max-height: 50px">',
				esc_attr($this->getFieldValue($item,$column_name))
			);
		}

		return $this->getFieldValue($item,$column_name);
	}

	/**
	 * Render the page.
	 */
	public function list_handler() {
		wp_enqueue_script("wpcrud");
		wp_enqueue_style("wpcrud");
		wp_enqueue_script("jquery-datetimepicker");
		wp_enqueue_style("jquery-datetimepicker");

		$template=new Template(__DIR__."/tpl/itemlist.php");
		$template->set("enableCreate",$this->config["enableCreate"]);

		if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="delete") {
			$item=$this->getItem($_REQUEST["id"]);

			if ($item) {
				$this->deleteItem($item);
				$template->set("message","Item deleted.");
			}
		}

		if ($this->current_action()=="delete" && !empty($_REQUEST["_bulkid"])) {
			$numitems=0;

			foreach ($_REQUEST["_bulkid"] as $id) {
				$item=$this->getItem($id);

				if ($item) {
					$this->deleteItem($item);
					$numitems++;
				}
			}

			$template->set("message",$numitems." item(s) deleted.");
		}

		$this->items=$this->getAllItems();

		$template->set("description",$this->getConfig("description"));
		$template->set("title",$this->getConfig("typeName"));
		$template->set("typeId",$this->typeId);
		$template->set("listTable",$this);
		$template->set("addlink",get_admin_url(get_current_blog_id(),'admin.php?page='.$this->typeId.'_form'));
		$template->show();
	}

	/**
	 * Override this in subclass to provide a customized name, description
	 * and other user interface elements.
	 */
	final public function getConfig($config) {
		return $this->config[$config];
	}

	/**
	 * Set literal.
	 */
	public function setConfig($config, $value) {
		if (!isset($this->config[$config]))
			throw new Exception("Unknown literal: ".$config);

		$this->config[$config]=$value;
	}

	/**
	 * Form handler.
	 * Internal.
	 */
	public function form_handler() {
		wp_enqueue_script("wpcrud");
		wp_enqueue_style("wpcrud");
		wp_enqueue_script("jquery-datetimepicker");
		wp_enqueue_style("jquery-datetimepicker");

		$template=new Template(__DIR__."/tpl/itemformpage.php");

		if (wp_verify_nonce($_REQUEST["nonce"],basename(__FILE__))) {
			if ($_REQUEST["id"])
				$item=$this->getItem($_REQUEST["id"]);

			else
				$item=$this->createItem();

			foreach ($this->getAllFields() as $field) {
				$fieldspec=$this->getFieldSpec($field);
				$v=$_REQUEST[$field];

				// post process field.
				switch ($fieldspec->type) {
					case "timestamp":
						if ($v) {
							$oldTz=date_default_timezone_get();
							date_default_timezone_set(get_option('timezone_string'));
							$v=strtotime($v);
							date_default_timezone_set($oldTz);
						}

						else {
							$v=0;
						}

						break;
				}

				$this->setFieldValue($item,$field,$v);
			}

			$message=$this->validateItem($item);

			if ($message) {
				$template->set("notice",$message);
			}

			else {
				$this->saveItem($item);
				$template->set("message",$this->getConfig("typeName")." saved.");
			}
		}

		else if (isset($_REQUEST["id"]))
			$item=$this->getItem($_REQUEST["id"]);

		else
			$item=$this->createItem();

		add_meta_box(
			$this->typeId."_meta_box",
			$this->getConfig("typeName"),
			array($this,"meta_box_handler"),
			$this->typeId,
			'normal_'.$this->typeId,
			'default',
			$this->defaultBox);

		foreach ($this->boxes as $box) {
			add_meta_box(
				$this->typeId."_meta_box_2",
				$box->getTitle(),
				array($this,"meta_box_handler"),
				$this->typeId,
				'normal_'.$this->typeId,
				'default',
				$box);
		}

		$template->set("title",$this->getConfig("typeName"));
		$template->set("nonce",wp_create_nonce(basename(__FILE__)));
		$template->set("backlink",get_admin_url(get_current_blog_id(),'admin.php?page='.$this->typeId));
		$template->set("metaboxPage",$this->typeId);
		$template->set("metaboxContext","normal_".$this->typeId);
		$template->set("item",$item);
		$template->show();
	}

	/**
	 * Meta box handler.
	 * Internal.
	 */
	public function meta_box_handler($item, $cbArg) {
		$box=$cbArg["args"];
		$box->renderItemFormBox($item);
	}

	/**
	 * Return array of bulk actions if any.
	 */
	protected function get_bulk_actions() {
		$actions = array(
		    'delete' => 'Delete'
		);
		return $actions;
	}

	/**
	 * Validate item, return error message if
	 * not valid.
	 * Override in sub-class.
	 */
	protected function validateItem($item) {
	}

	/**
	 * Create a new item.
	 * Implement in sub-class.
	 */
	protected function createItem() {
		return new stdClass;
	}

	/**
	 * Get specified value from an item.
	 * Implement in sub-class.
	 */
	public function getFieldValue($item, $field) {
		if (is_array($item))
			return $item[$field];

		else if (is_object($item))
			return $item->$field;

		else
			throw new Exception("Expected item to be an object or an array");
	}

	/**
	 * Set field value.
	 * Implement in sub-class.
	 */
	protected function setFieldValue(&$item, $field, $value) {
		if (is_array($item))
			return $item[$field]=$value;

		else if (is_object($item))
			return $item->$field=$value;

		else
			throw new Exception("Expected item to be an object or an array");
	}

	/**
	 * Save item.
	 * Implement in sub-class.
	 */
	protected abstract function saveItem($item);

	/**
	 * Delete item.
	 * Implement in sub-class.
	 */
	protected abstract function deleteItem($item);

	/**
	 * Get item by id.
	 * Implement in sub-class.
	 */
	protected abstract function getItem($id);

	/**
	 * Get all items for list.
	 * Implement in sub-class.
	 */
	protected abstract function getAllItems();

	/**
	 * Serve frontend resource.
	 */
	public static function res() {
		$resFiles=array(
			"res/jquery.datetimepicker.js",
			"res/jquery.datetimepicker.css",
			"js/wpcrud.js",
			"css/wpcrud.css",
			"img/delete-icon.png"
		);

		$mimeTypes=array(
			"js"=>"application/javascript",
			"css"=>"text/css",
			"png"=>"image/png"
		);

		$resFile=$_REQUEST["res"];

		if (!in_array($resFile,$resFiles))
			return;

		$resFilePath=__DIR__."/".$resFile;
		$ext=pathinfo($resFilePath,PATHINFO_EXTENSION);
		header("Content-Type: ".$mimeTypes[$ext]);
		readfile($resFilePath);
		exit;
	}

	/**
	 * Stuff.
	 */
	public static function admin_enqueue_scripts() {
		if (WpCrud::$scriptsEnqueued)
			return;

		WpCrud::$scriptsEnqueued=TRUE;

		wp_register_script("jquery-datetimepicker",admin_url('admin-ajax.php')."?action=wpcrud_res&res=res/jquery.datetimepicker.js");
		wp_register_style("jquery-datetimepicker",admin_url('admin-ajax.php')."?action=wpcrud_res&res=res/jquery.datetimepicker.css");

		wp_register_script("wpcrud",admin_url('admin-ajax.php')."?action=wpcrud_res&res=js/wpcrud.js");
		wp_register_style("wpcrud",admin_url('admin-ajax.php')."?action=wpcrud_res&res=css/wpcrud.css");

		wp_enqueue_media();

		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
	}

	/**
	 * Main entry point.
	 */
	public static function admin_menu() {
		$instance=new static();

		if ($instance->parentMenuSlug)
			$screenId=add_submenu_page(
				$instance->parentMenuSlug,
				"Manage ".$instance->getConfig("typeName"),
				"Manage ".$instance->getConfig("typeName"),
				"manage_options",
				$instance->typeId,
				array($instance,"list_handler")
			);

		else
			$screenId=add_menu_page(
				$instance->getConfig("typeName"),
				$instance->getConfig("typeName"),
				"manage_options",
				$instance->typeId,
				array($instance,"list_handler")
			);

	    add_submenu_page(
	    	NULL,
	    	"Edit ".$instance->getConfig("typeName"),
	    	"Edit ".$instance->getConfig("typeName"),
	    	'manage_options',
	    	$instance->typeId.'_form',
	    	array($instance,"form_handler")
	    );
	}

	/**
	 * Setup.
	 */
	public static function setup() {
		add_action("admin_menu",get_called_class()."::admin_menu",11);
		add_action("admin_enqueue_scripts","WpCrud::admin_enqueue_scripts");
		add_action("wp_ajax_wpcrud_res","WpCrud::res");
	}
}
