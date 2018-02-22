<?php

/**
 * Per user swag.
 */
class SwagUser
{

    private static $swagUserById = array();
    private $user;
    private $email;

    /**
     * Construct.
     */
    private function __construct($user)
    {
        if ($user && $user->ID) {
            $this->user = $user;
            SwagUser::$swagUserById[$user->ID] = $this;
        }

        $this->xapi = SwagPlugin::instance()->getXapi();

        $this->completedSwagFetched = null;
        $this->completedSwag = null;
    }

    /**
     * Set email.
     */
    private function setEmail($email)
    {
        if ($this->user) {
            throw new Exception("This is a real user, can't set email");
        }

        $this->email = $email;
    }

    /**
     * Get user id.
     */
    public function getId()
    {
        if (!$this->user) {
            throw new Exception("User is not logged in.");
        }

        return $this->user->ID;
    }

    /**
     * Get user.
     */
    public function getUser()
    {
        if (!$this->user) {
            throw new Exception("User is not logged in.");
        }

        return $this->user;
    }

    /**
     * Get display name.
     */
    public function getDisplayName()
    {
        if ($this->user) {
            return $this->user->display_name;
        }

        return "Temporary User";
    }

    /**
     * Get email.
     */
    public function getEmail()
    {
        if ($this->user) {
            if (!$this->user->user_email) {
                throw new Exception("We have a user, but no email");
            }

            return $this->user->user_email;
        }

        if (!$this->email) {
            throw new Exception("User email not set");
        }

        return $this->email;
    }

    /**
     * Get completed for top level track.
     */
    public function getCompletedByTopLevelTrack($trackSlug)
    {
        $swagpaths = $this->getCompletedSwagpaths();
        $res = array();

        foreach ($swagpaths as $swagpath) {
            if ($swagpath->getTopLevelTrack() == $trackSlug) {
                $res[] = $swagpath;
            }

        }

        return $res;
    }

    /**
     * Clear fetched statements.
     */
    public function clearFetchedStatements()
    {
        $this->completedSwagFetched = null;
        $this->completedSwag = null;
    }

    /**
     * Get collected swag.
     */
    public function getCompletedSwagpaths()
    {
        if ($this->completedSwagFetched) {
            return $this->completedSwag;
        }

        if ($this->xapi) {
            $statements = $this->xapi->getStatements(array(
                "agentEmail" => $this->getEmail(),
                "activity" => "http://swag.tunapanda.org/",
                "verb" => "http://adlnet.gov/expapi/verbs/completed",
                "related_activities" => "true",
            ));
        } else {
            $statements = array();
        }

        $this->completedSwag = array();
        foreach ($statements as $statement) {
            $slug = str_replace("http://swag.tunapanda.org/", "", $statement["object"]["id"]);
            $swagpath = Swagpath::getBySlug($slug);

            if ($swagpath) {
                $swagpath->completedStatement = $statement;
            }

            $this->completedSwag[] = $swagpath;
        }

        $this->completedSwagFetched = true;

        return $this->completedSwag;
    }

    /**
     * Is this swag completed by the user?
     */
    public function isSwagpathCompleted($swagpath)
    {
        $completed = $this->getCompletedSwagpaths();
        foreach ($completed as $c) {
            if ($c->getXapiObjectId() == $swagpath->getXapiObjectId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Logged in?
     */
    public function isLoggedIn()
    {
        if ($this->user && $this->user->ID) {
            return true;
        }

        return false;
    }

    /**
     * Get current SwagUser.
     */
    public static function getCurrent()
    {
        static $current;

        if (!$current) {
            $u = wp_get_current_user();

            if ($u && $u->ID) {
                $current = new SwagUser($u);
            } else {
                if (!session_id()) {
                    session_start();
                }

                $sessionId = session_id();
                //error_log("creating pseudo user: ".$sessionId);

                $current = new SwagUser(null);
                $current->setEmail($sessionId . "@example.com");
            }
        }

        return $current;
    }

    /**
     * Get by id.
     */
    public static function getById($id)
    {
        if (isset(SwagUser::$swagUserById[$id])) {
            return SwagUser::$swagUserById[$id];
        }

        $user = get_user_by("ID", $id);
        if (!$user) {
            return null;
        }

        return new SwagUser($user);
    }

    /**
     * Get by email.
     */
    public static function getByEmail($email)
    {
        $email = str_replace("mailto:", "", $email);

        $current = SwagUser::getCurrent();
        if ($current->getEmail() == $email) {
            return $current;
        }

        $user = get_user_by("email", $email);
        if (!$user || !$user->ID) {
            throw new Exception("user not found: " . $email);
        }

        return new SwagUser($user);
    }
}
