<?php

/**
 * Wordpress shortcode util.
 */
class ShortcodeUtil {

	public static function strposAny($subject, $tokens) {
		$len=strlen($subject);

		for ($i=0; $i<strlen($tokens); $i++) {
			$p=strpos($subject,$tokens[$i]);
			if ($p!==FALSE && $p<$len)
				$len=$p;
		}

		return $len;
	}

	/**
	 * Tokenize shortcodes.
	 */
	public static function tokenize($text) {
		$tokens=array();
		$text=trim($text);

		while (strlen($text)) {
			$text=trim($text);

			switch ($text[0]) {
				case "[":
				case "]":
				case "=":
					$tokens[]=$text[0];
					$text=substr($text,1);
					break;

				case "'":
				case '"':
					$p=ShortcodeUtil::strposAny(substr($text,1),"\"'");
					$tokens[]=substr($text,1,$p);
					$text=substr($text,$p+2);
					break;

				default:
					$p=ShortcodeUtil::strposAny($text," \n\t\"'[]=");
					$tokens[]=substr($text,0,$p);
					$text=substr($text,$p);
					break;
			}
		}

		return $tokens;
	}

	/**
	 * Extract shortcodes from text.
	 */
	public static function extractShortcodes($text) {
		$res=array();
		preg_match_all("(\[.*?\])",$text,$matches);

		foreach ($matches[0] as $match) {
			$tokens=ShortcodeUtil::tokenize($match);

			if ($tokens[0]!="[")
				throw new Exception("shortcode parse error");

			if ($tokens[sizeof($tokens)-1]!="]")
				throw new Exception("shortcode parse error");

			$tokens=array_slice($tokens,1,sizeof($tokens)-2);
			$attr=array();
			$attr["_"]=$tokens[0];
			$tokens=array_slice($tokens,1);

			$i=0;
			while ($i<sizeof($tokens)) {
				if ($tokens[$i+1]=="=") {
					$attr[$tokens[$i]]=$tokens[$i+2];
					$i+=3;
				}

				else {
					$attr[$tokens[$i]]=TRUE;
					$i++;
				}
			}

			$res[]=$attr;
		}

		return $res;
	}
}