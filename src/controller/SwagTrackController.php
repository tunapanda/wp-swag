<?php

require_once __DIR__ . "/../utils/Singleton.php";
require_once __DIR__ . "/../utils/Template.php";

use swag\Singleton;

/**
 * Manage the swag taxonomy.
 */
class SwagTrackController extends Singleton
{

    /**
     * Init.
     */
    public function init()
    {
        register_taxonomy("swagtrack", "swagpath", array(
            "label" => "Swagtracks",
            "public" => true,
            "hierarchical" => true,
            "show_in_rest" => true,
        ));

        add_action("swagtrack_add_form_fields", array($this, "addFormFields"));
        add_action("swagtrack_edit_form_fields", array($this, "editFormFields"));
        add_action("create_swagtrack", array($this, "onSave"));
        add_action("edited_swagtrack", array($this, "onSave"));
    }

    /**
     * The add_form_fields action.
     */
    public function addFormFields()
    {
        $t = new Template(__DIR__ . "/../../tpl/swagtrackadminfields.php");
        $t->set("swagtrackColor", "");
        $t->show();
    }

    /**
     * The edit_form_fields action.
     */
    public function editFormFields($term)
    {
        $t = new Template(__DIR__ . "/../../tpl/swagtrackadminfields.php");
        $t->set("swagtrackColor", get_term_meta($term->term_id, "color", true));
        $t->show();
    }

    /**
     * A swagtrack was saved from the admin.
     */
    public function onSave($termId)
    {
        update_term_meta($termId, "color", $_REQUEST["swagtrackColor"]);
    }
}
