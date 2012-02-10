<?php

class WikiAuthException extends Exception{};

/**
 * This object authenticates with the wiki api using a shared curl
 * object.  Cookies generated with this class are used by other
 * classes because the curl object is passed by reference.
 * @author	Robert McLeod
 * @since	Febuary 10th 2012
 * @licence	http://creativecommons.org/licenses/by-nc/3.0/nz/
 */
class WikiAuth {
	
	private $api_url;
	private $user;
	private $pass;
	private $curl;
	
	function __construct( $api_url, $user, $pass, Curl &$curl ) {
		
		$this->curl = &$curl;
		$this->api_url = $api_url;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	function login() {
		
		$data = array(
			'action' => 'login',
			'lgname' => $this->user,
			'lgpassword' => $this->pass,
			'format' => 'json'
		);

		WikiMate::print_debug("Using login details:", $data );

		// Send the login request
		$response = $this->curl->post( $this->api, $data )->body;
		$this->check_response( $response );
	}
	
	private function check_response( $response ) {
		
		// Check if we got an API result or the API doc page (invalid request)
		if ( strstr( $response, "This is an auto-generated MediaWiki API documentation page" ) )
		        throw new WikiAuthException("The API could not understand the first login request");
		        
		// Now we know its not an error we can decode the json
		$response = json_decode( $response );
		
		WikiMate::print_debug("Login response", $response );
		
		if ( $response->login->result != "Success" ) {
			// Some more comprehensive error checking
			switch ($response->login->result) {
		            case 'NotExists':
		                throw new WikiAuthException("The username does not exist");
		                break;
		            default:
		                throw new WikiAuthException('The API result was: '. $response->login->result);
		                break;
			}
		}
	}
	
	function get_api_url() {
		return $this->api_url;
	}
}
