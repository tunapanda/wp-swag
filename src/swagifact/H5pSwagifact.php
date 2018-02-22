<?php

require_once __DIR__ . "/../model/SwagPostItem.php";
require_once __DIR__ . "/../utils/H5pUtil.php";

/**
 * A H5P swagifact.
 */
class H5pSwagifact extends SwagPostItem
{

    /**
     * Override the completion check.
     */
    public function isCompleted($swagUser)
    {
        $slug = $this->parameters["slug"];
        $h5pId = H5pUtil::getH5pIdBy("slug", $slug);
        $h5p = H5pUtil::getH5pById($h5pId);
        $h5pParameters = json_decode($h5p["parameters"], true);

        $scoreElements = array(
            "H5P.MultiChoice",
            "H5P.SingleChoiceSet",
        );

        $requireComplete = false;

        if (!$h5pParameters) {
            throw new Exception("h5p not found or no parameters!");
        }

        $numSlides = 0;
        if (isset($h5pParameters["presentation"]["slides"])) {
            $numSlides = sizeof($h5pParameters["presentation"]["slides"]);

            foreach ($h5pParameters["presentation"]["slides"] as $slide) {
                foreach ($slide["elements"] as $element) {
                    if (isset($element["action"])) {
                        $library = $element["action"]["library"];
                        $parts = explode(" ", $library);
                        $libraryName = $parts[0];
                        if (in_array($libraryName, $scoreElements)) {
                            $requireComplete = true;
                        }

                    }
                }
            }
        }

        $objectUrl = $this->getObjectUrl();

        foreach ($this->swagPost->getRelatedStatements($swagUser) as $statement) {
            if ($statement["object"]["id"] == $objectUrl) {
                if ($statement["verb"]["id"] == "http://adlnet.gov/expapi/verbs/completed") {
                    if ($statement["result"]["score"]["scaled"] == 1) {
                        return true;
                    }

                }

                if ($statement["verb"]["id"] == "http://adlnet.gov/expapi/verbs/progressed"
                    && !$requireComplete) {
                    $ext = $statement["object"]["definition"]["extensions"];
                    $endingPoint = $ext["http://id.tincanapi.com/extension/ending-point"];

                    //error_log("slug: ".$slug." ep: ".$endingPoint." ns: ".$numSlides);

                    if ($endingPoint && $numSlides && $endingPoint >= $numSlides) {
                        return true;
                    }

                }
            }
        }

        return false;
    }
}
