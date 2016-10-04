<?php

require_once __DIR__."/../../ext/wpcrud/WpCrud.php";

class SwagController extends WpCrud {
	function init() {
		$this->addField("title")
            ->type("label");

		$this->addField("color")
            ->description("If no color is specified, the color will be inherited from the parent");

		$this->addField("description")
            ->description("This description will appear in the category listing")
            ->type("textarea");

		$this->setListFields(array("title","color"));

        $this->setConfig("parentMenuSlug","options-general.php");
        $this->setConfig("enableCreate",FALSE);
        $this->setConfig("typeName","Swag Badges");
        $this->setConfig("description",
            "Use this page to set colors and decription for swag categories.");



//        $this->setParentMenuSlug("options-general.php");
	}

    function createItem() {
        return "new";
    }

    function getItem($id) {
    	return Swag::findByString($id);
    }

    function deleteItem($item) {

    }

    function saveItem($item) {

    }

    function getAllItems() {
    	$implied=Swag::findAllImplied();
    	$all=array();

    	foreach ($implied as $swag) {
    		if ($swag->getTitle())
    			$all[]=$swag;
    	}

    	return $all;
    }

    function getFieldValue($item, $field) {
        if ($item=="new")
            return "bleee";

    	switch ($field) {
    		case "title":
    			return $item->getString();
    			break;

    		case "color":
    			return $item->getDefinedColor();
    			break;

    		case "description":
    			return $item->getDescription();
    			break;

    		case "id":
    			return $item->getString();
    			break;

    		default:
    			throw new Exception("Unknown field: ".$field);
    	}
    }
}