<?php

/**
 * A PHP API for accessing user data from weightbot.com
 *
 **/
class Weightbot {

    private $_cookie_file = 'cookies';
    private $_email;
    private $_password;

    /**
     * Creates a Weightbot object for a given user.
     **/
    public function __construct($email, $password) {
        $this->_email = $email;
        $this->_password = $password;
    }

    /**
     * Creates a cURL object for remote requests.
     * @param $url string the URL to connect to.
     * @return cURL object
     **/
    private function _prep_curl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookie_file);
        return $ch;
    }
    
    /**
     * Fetches a remote Weightbot page and retrieves the CSRF token in the
     * page's form.
     * @param $url string the remote Weightbot page
     * @return string the CSRF token, if present
     */
    private function _get_token($url) {
        $ch = $this->_prep_curl($url);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $matches = array();
        preg_match("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $result, $matches);
        return isset($matches[1]) ? $matches[1] : false;
    }
    
    /**
     * Logs the user in and stores details in the COOKIE_FILE.
     * @return boolean login success/failure
     */
    public function login() {
        $email = $this->_email;
        $password = $this->_password;

        $token = $this->_get_token("https://weightbot.com/account/login");      
        $ch = $this->_prep_curl("https://weightbot.com/account/login");

        $post = array(
            'email' => $email,
            'password' => $password,
            'authenticity_token' => $token,
        );
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        $result = curl_exec($ch);
        curl_close($ch);
        // Could be better...
        return (strstr($result, 'You are being') !== false);
    }
    
    /**
     * Gets the Weightbot CSV user data.
     * @return string csv user data
     */
    public function get_csv() {
        $token = $this->_get_token("https://weightbot.com");

        // Now that we have the token, let's finally get the CSV.
        $ch = $this->_prep_curl("https://weightbot.com/export");
        $post = array(
            'authenticity_token' => $token,
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}