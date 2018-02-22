<?php

require_once __DIR__ . "/../utils/Singleton.php";

use swag\Singleton;

/**
 * Manage the swag taxonomy.
 */
class SwagMapController extends Singleton
{

    /**
     * Init.
     */
    public function init()
    {
    }

    /**
     * Get data for rendering swagmap.
     */
    public function swagMapData($mode)
    {
        $nodes = array();
        $links = array();
        $swagpaths = Swagpath::findAll();
        $nodeIndexByPostId = array();

        foreach ($swagpaths as $swagpath) {
            $nodeData = null;

            if ($swagpath->isCurrentUserPrepared() || $mode == "full") {
                $nodeData = array(
                    "name" => $swagpath->getPost()->post_title,
                    "type" => "swag",
                    "completed" => $swagpath->isCompletedByCurrentUser(),
                    "color" => $swagpath->getDisplayColor(),
                    "url" => get_permalink($swagpath->getPost()->ID),
                );
            } else if ($swagpath->isCurrentUserPreparedForPrerequisites()) {
                $nodeData = array(
                    "name" => "?",
                    "type" => "swag",
                    "completed" => false,
                    "color" => "#999999",
                    "url" => null,
                );
            }

            if ($nodeData) {
                $nodeIndexByPostId[$swagpath->getPost()->ID] = sizeof($nodes);
                $nodes[] = $nodeData;
            }
        }

        foreach ($swagpaths as $swagpath) {
            $pres = $swagpath->getPrerequisites();
            foreach ($pres as $pre) {
                if (isset($nodeIndexByPostId[$pre->getPost()->ID]) &&
                    isset($nodeIndexByPostId[$swagpath->getPost()->ID])) {
                    $link = array(
                        "source" => $nodeIndexByPostId[$pre->getPost()->ID],
                        "target" => $nodeIndexByPostId[$swagpath->getPost()->ID],
                    );

                    $links[] = $link;
                }
            }
        }

        return array(
            "nodes" => $nodes,
            "links" => $links,
        );
    }
}
