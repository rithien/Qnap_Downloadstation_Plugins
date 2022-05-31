<?php

class ddosguard
{

    private $cookie_file;
    private $cookie;
    private $reglink;
    private $regcloud;
    private $regcheck;
    private $headers;

    function __construct($cookie = false, $cookie_file = "")
    {
        $this->cookie = $cookie;
        if ($this->cookie == true) {
            $this->cookie_file = $cookie_file;
        }
        $this->regcheck = "/new Image\(\).src = '(.+?)';/";
        $this->reglink = "/href='(\/\/dbree.org.+?)' class\=/";
        $this->regcloud = "/(.*)/";
        $this->headers = null;
    }

    function header_func($ch, $header_line)
    {
        foreach (explode("\r\n", $header_line) as $line) {
            $test = explode(":", $line, 2);
            if (is_array($test) && !empty($test[0]) && !empty($test[1])) {
                $key = strtolower($test[0]);
                $this->headers[$key] = trim($test[1]);
            }
        }

        return strlen($header_line);
    }


    function get($url, $headeronly = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, "ddos-guard");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 0);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        if ($this->cookie == true) {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_file);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
        }

        if ($headeronly) {
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, "header_func"));

        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo "Errno" . curl_error($curl);
        }
        curl_close($curl);
        return $tmpInfo;
    }

    function post($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if ($this->cookie == true)
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo "Errno" . curl_error($curl);
        }
        curl_close($curl);
        return $tmpInfo;
    }

    function delcookie($cookie_file)
    {
        @unlink($cookie_file);
    }

    function get_check()
    {
        $response = $this->get("https://check.ddos-guard.net/check.js");
        preg_match($this->regcheck, $response, $image, PREG_UNMATCHED_AS_NULL);
        return empty($image[1]) ? null : $image[1];
    }

    function src_validator($url)
    {
        $domain = parse_url($url)["host"];
        $response = $this->get_check();
        if (!empty($response)) {
            $this->get("https://" . $domain . "/" . $response);
            $response = $this->get($url);
            preg_match($this->reglink, $response, $link, PREG_UNMATCHED_AS_NULL);
            return empty($link[1]) ? null : $link[1];
        }
    }

    public function get_download($url)
    {
        $this->headers = null;
        $link = $this->src_validator($url);
        if (!empty($link)) {
            $this->get("https:" . $link, true);
        }
        $this->delcookie("cookie.txt");
        return empty($this->headers) ? null : $this->headers;
    }

}
