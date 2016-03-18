<?php

/* 
    http client
*/
class HttpClient
{
    protected $_url;
    protected $_fields;
    
    function __construct($url = null)
    {
        $this->clear_fields();
        $this->set_url($url);
    }   
    
    function __destruct() { }
    
    function set_url($url)
    {
        $this->_url = $url;
    }
    
    function set_field($k, $v)
    {
        $this->_fields[] = $url;
    }
    
    function set_fields($data = array())
    {
        $this->_fields = $data;
    }
    
    function clear_fields()
    {
        $this->_fields[] = array();
    }
    
    function make_query($fields = array())
    {
        $query = null;
        if (!is_array($fields)) return null; 
        $pairs = array();
        foreach ($fields as $k => $v)
            $pairs[] = $k . '=' . urlencode($v);
        if (!empty($pairs))
            $query = implode('&', $pairs);
        return $query;
    }
    
    function get($url = null, $data = null) 
    {
        $url = (!empty($url)) ? $url : $this->_url;
        if (empty($url)) return false;
        $query = null;
        if (!empty($data)){
            if (is_array($data)) // build query string
                $query = $this->make_query($data);
            else            
                $query = $data;
        } else if (!empty($this->_fields))
            $query = $this->make_query($this->_fields);
        if ($query != null) $url .= '?' . $query;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
  
    function post($url = null, $fields = null)
    {
        $url = (!empty($url)) ? $url : $this->_url;
        if (empty($url)) return false;
        $fields = (!empty($fields)) ? $fields : $this->_fields;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (!empty($fields))
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

?>