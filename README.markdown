# Weightbot PHP API #

## Description ##

A simple PHP interface to export your CSV data from the weightbot.com site. The API is reasonably network-intensive (4 remote calls to log in and fetch data), so you'll probably want to cache the CSV data in some way (memcache, etc).

## Usage ##
    require_once 'Weightbot.php';
    $wb = new Weightbot('user@email.com', 'password');
    if($wb->login()) {
        echo $wb->get_csv();
    }

It is important to note that _you will need a file, writable by the web-server to store the cURL cookies_. This file is defined in the Weightbot class as $\_cookies\_file (default: 'cookies').