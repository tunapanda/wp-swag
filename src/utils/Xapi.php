<?php

if (!class_exists("Xapi")) {

	/**
	 * Xapi.
	 */
	class Xapi {

		/**
		 * Constructor.
		 */
		public function __construct($endpoint, $username, $password) {
			$this->endpoint=$endpoint;
			$this->username=$username;
			$this->password=$password;
		}

		/**
		 * Get statements.
		 *
		 * Spec for params:
		 *
		 * https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#stmtapiget
		 */
		public function getStatements($params) {
			$url=$this->endpoint;
			if (substr($url,-1)!="/")
				$url.="/";
			$url.="statements";

			if (isset($params["agentEmail"])) {
				$params["agent"]=json_encode(array(
					"mbox"=>"mailto:".$params["agentEmail"]
				));

				unset($params["agentEmail"]);
			}

			$query=http_build_query($params);

			/*print_r($query);
			exit;*/

			$url.="?".$query;

			$headers=array(
				"Content-Type: application/json",
				"X-Experience-API-Version: 1.0.1",
			);

			$userpwd=$this->username.":".$this->password;

			$curl=curl_init();
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($curl,CURLOPT_URL,$url);
			curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
			curl_setopt($curl,CURLOPT_USERPWD,$userpwd);
			$res=curl_exec($curl);

			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if ($code!=200) {
				echo $res;
				throw new Exception("Unable to connect to xapi",$code);
			}

			$decoded=json_decode($res,TRUE);

			if (!$decoded || !array_key_exists("statements",$decoded))
				throw new Exception("Bad response from xapi endpoint.");

			return $decoded["statements"];
		}

		/**
		 * Put statement.
		 */
		public function putStatement($statement) {
			$content=json_encode($statement);

			$url=$this->endpoint;
			if (substr($url,-1)!="/")
				$url.="/";
			$url.="statements";

			$headers=array(
				"Content-Type: application/json",
				"X-Experience-API-Version: 1.0.1",
			);

			$userpwd=$this->username.":".$this->password;

			$curl=curl_init();
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
			curl_setopt($curl,CURLOPT_USERPWD,$userpwd);
			curl_setopt($curl,CURLOPT_URL,$url);
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$content);

			$res=curl_exec($curl);
			$decoded=json_decode($res,TRUE);
			$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);

			if ($code!=200 || sizeof($decoded)!=1 || strlen($decoded[0])!=36) {
				//throw new Exception($res);

				if (in_array("message",$decoded) && $decoded["message"])
					throw new Exception($decoded["message"]);

				if (is_string($res))
					throw new Exception($res);

				throw new Exception("Unknown error");
			}
		}
	}
}