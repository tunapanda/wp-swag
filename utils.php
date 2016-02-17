<?php

	/**
	 * Render a template.
	 */
    function render_tpl($filename, $vars) {
        foreach ($vars as $key=>$value)
            $$key=$value;

        ob_start();
        require $filename;
        return ob_get_clean();
    }
