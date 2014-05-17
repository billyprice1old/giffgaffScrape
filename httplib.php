<?php
/*
Alistair Judson's HTTP Helper Class
alistair.p.judson@gmail.com
The MIT License (MIT)

Copyright (c) 2014 Alistair Judson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
class httplib
{
    public function makeURL($secure=false, $domain="", $get="", $parameters=null)
    {
        $protocol = "http";
        if($secure)
        {
            $protocol .= "s";
        }
        $url = $protocol . "://" . $domain . $get;
        if($parameters != null)
        {
            $url .= parameterise($parameters, "GET");
        }
        return $url;
    }
    public function parameterise($parameters, $type)
    {
        $parametersString = "";
        if($type == "GET")
        {
            $parametersString .= "?";
        }
        foreach($parameters as $key => $value)
        {
            $parametersString .= $key . "=" . urlencode($value) . "&";
        }
        $parametersString = substr($parametersString, 0, -1);
        return $parametersString;
    }
    private function setupCURL($url)
    {
        $ch =  curl_init();
        $options = array(
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER         => true,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_ENCODING       => "",
                    CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36",
                    CURLOPT_AUTOREFERER    => true,
                    CURLOPT_CONNECTTIMEOUT => 120,
                    CURLOPT_TIMEOUT        => 120,
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_VERBOSE        => true,
                    CURLOPT_REFERER        => $url
                );
        curl_setopt_array($ch, $options);
        return $ch;
    }
    private function prepareHeader($header)
    {
        $headerArray = array();
        $headerLines = explode("\n", $header);
        foreach($headerLines as $field)
        {
            if(trim($field) != "" && strpos($field, ":")!== false)
            {
                $fieldSplit = explode(": ", $field, 2);
                $key = $fieldSplit[0];
                $value = $fieldSplit[1];
                if($key == "Set-Cookie")
                {
                    $cookieParts = explode("; ", $value);
                    $cookieArray = array();
                    foreach($cookieParts as $cookiePart)
                    {
                        if(strpos($cookiePart, "=")!== false)
                        {
                            $keyValue = explode("=", $cookiePart, 2);
                            $cookieKey = $keyValue[0];
                            $cookieValue = $keyValue[1];
                            $cookieArray[$cookieKey] = $cookieValue;
                        }
                        else
                        {
                            $cookieArray[] = $cookiePart;
                        }
                    }
                    reset($cookieArray);
                    $first_key = key($cookieArray);
                    $headerArray[$key][$first_key] = $cookieArray;
                }
                else
                {
                    $headerArray[$key] = $value;
                }
            }
            else if(trim($field) != "" && strpos($field, ":") === false)
            {
                $headerArray[] = $field;
            }
        }
        return $headerArray;

    }
    public function requestPage($url, $postData=null, $cookie=null)
    {
        $ch = $this->setupCURL($url);
        if($postData != null)
        {
            $postDataString = $this->parameterise($postData, "POST");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataString);
        }
        if($cookie != null)
        {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        $response = curl_exec($ch);
        $information = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerText = substr($response, 0, $header_size);
        $header = $this->prepareHeader($headerText);
        $body = substr($response, $header_size);
        $result = array(
                "header" => $header,
                "body"   => $body,
                "info"   => $information
            );
        return $result;
    }
    public function getCookieString($header)
    {
        $cookieString = "";
        foreach($header["Set-Cookie"] as $cookie)
        {
            reset($cookie);
            $first_key = key($cookie);
            $cookieString .= $first_key."=".$cookie[$first_key]."; ";
        }
        $cookieString = substr($cookieString, 0, -1);
        return $cookieString;
    }
    public function array_to_xml($array, &$xml_array) {
        foreach($array as $key => $value) {
            if(is_array($value)) 
            {
                if(!is_numeric($key))
                {
                    $subnode = $xml_array->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                }
                else
                {
                    $subnode = $xml_array->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            }
            else
            {
                $key = str_replace(" ", "_", $key);
                $xml_array->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}
?>
