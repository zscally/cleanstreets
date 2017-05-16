<?php

/*************************************************
 * @param int $lenght
 * @return string
 * @throws Exception
 *
 * Gets a real unique ID
 *
 */
function uniqidReal($lenght = 13) {
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    return substr(bin2hex($bytes), 0, $lenght);
}


/*************************
 * grabs the users IP address regardless from where they are coming from!
 * return $ip from remote_addr or http_x_forwarded
 */
function get_user_ip()
{
    if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) )//check ip from share internet
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )//to check ip is pass from proxy
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else //standard remote ip address
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}


function get_user_host()
{
    if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
    {
        $host = @gethostbyaddr( $_SERVER['HTTP_X_FORWARDED_FOR'] );
    }
    else
    {
        $host = @gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
    }
    return $host;
}

/****************************************
 * @param bool $datetime
 * @param bool $unix_format
 * @param string $format
 * @return false|string
 *
 * date time function to return date how you like
 */
function datetimeformat($datetime = false, $unix_format = false, $format = 'Y-m-d H:i:s')
{
    $date = ( $datetime ? $datetime : date($format) );
    $time = ( $unix_format ? $date : strtotime($date) );
    return date($format, $time);
}


function stripslashes_deep($value)
{
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}

function getqrcodebase64($data)
{
    if( $data && !empty($data) ) {
        $image_data = file_get_contents('http://chart.apis.google.com/chart?cht=qr&chs=125x125&chl='.$data.'&chld=H|0');
        return base64_encode($image_data);
    }
}

function formatPhone($phone)
{
    if(  preg_match( '/^\+\d(\d{3})(\d{3})(\d{4})$/', $phone,  $matches ) )
    {
        $result = $matches[1] . ' ' .$matches[2] . ' ' . $matches[3];
        return $result;
    } else {
        return $phone;
    }
}
