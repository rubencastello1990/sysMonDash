<?php

namespace Exts\Zabbix\V720;

use Exception;

/**
 * Zabbix API client for Zabbix 7.x
 *
 * Lightweight client implementing only the methods used by sysMonDash.
 * Supports both API token (Bearer) and user.login authentication.
 */
abstract class ZabbixApiAbstract
{
    /**
     * API methods that don't require authentication.
     */
    private static $anonymousFunctions = array(
        'apiinfo.version'
    );

    private $printCommunication = false;
    private $apiUrl = '';
    private $defaultParams = array();
    private $auth = '';
    private $id = 0;
    private $request = array();
    private $requestEncoded = '';
    private $response = '';
    private $responseDecoded = null;
    private $extraHeaders = '';
    private $sslContext = array();

    /**
     * Whether authentication is via Bearer token (true) or session auth (false).
     */
    private $useBearerAuth = false;

    /**
     * @param string $apiUrl
     * @param string $user
     * @param string $password
     * @param string $httpUser
     * @param string $httpPassword
     * @param string $authToken  API token for Bearer authentication
     * @param array  $sslContext
     */
    public function __construct($apiUrl = '', $user = '', $password = '', $httpUser = '', $httpPassword = '', $authToken = '', $sslContext = null)
    {
        if ($apiUrl)
            $this->setApiUrl($apiUrl);

        if ($httpUser && $httpPassword)
            $this->setBasicAuthorization($httpUser, $httpPassword);

        if ($sslContext)
            $this->setSslContext($sslContext);

        if ($authToken)
            $this->setBearerAuth($authToken);
        elseif ($user && $password)
            $this->userLogin(array('username' => $user, 'password' => $password));
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     * @return $this
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    /**
     * Set authentication via API token (Bearer header).
     * The token is sent as an HTTP Authorization header, NOT in the JSON-RPC body.
     *
     * @param string $token
     * @return $this
     */
    public function setBearerAuth($token)
    {
        $this->auth = '';
        $this->useBearerAuth = true;
        $this->extraHeaders = 'Authorization: Bearer ' . $token;
        return $this;
    }

    /**
     * Set session auth token (for user.login flow).
     *
     * @param string $authToken
     * @return $this
     */
    public function setAuthToken($authToken)
    {
        $this->auth = $authToken;
        $this->useBearerAuth = false;
        return $this;
    }

    /**
     * @param string $user
     * @param string $password
     * @return $this
     */
    public function setBasicAuthorization($user, $password)
    {
        if ($user && $password)
            $this->extraHeaders = 'Authorization: Basic ' . base64_encode($user . ':' . $password);
        else
            $this->extraHeaders = '';

        return $this;
    }

    /**
     * @param array $context
     * @return $this
     */
    public function setSslContext($context)
    {
        $this->sslContext = $context;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultParams()
    {
        return $this->defaultParams;
    }

    /**
     * @param array $defaultParams
     * @return $this
     * @throws Exception
     */
    public function setDefaultParams($defaultParams)
    {
        if (is_array($defaultParams))
            $this->defaultParams = $defaultParams;
        else
            throw new Exception('The argument defaultParams on setDefaultParams() has to be an array.');

        return $this;
    }

    /**
     * @param bool $print
     * @return $this
     */
    public function printCommunication($print = true)
    {
        $this->printCommunication = (bool)$print;
        return $this;
    }

    /**
     * Send a JSON-RPC request to the Zabbix API.
     *
     * For Bearer token auth: token is sent via HTTP header, 'auth' field is omitted from JSON-RPC body.
     * For session auth: token is sent in JSON-RPC 'auth' field.
     *
     * @param string $method
     * @param array  $params
     * @param string $resultArrayKey
     * @param bool   $auth
     * @return mixed
     * @throws Exception
     */
    public function request($method, $params = null, $resultArrayKey = '', $auth = true)
    {
        if (!$params) $params = array();
        elseif (!is_array($params)) $params = array($params);

        $this->id = number_format(microtime(true), 4, '', '');

        $this->request = array(
            'jsonrpc' => '2.0',
            'method'  => $method,
            'params'  => $params,
            'id'      => $this->id
        );

        // For Bearer auth: no 'auth' in body. For session auth: add 'auth' field.
        if ($auth && !$this->useBearerAuth) {
            $this->request['auth'] = ($this->auth ? $this->auth : null);
        }

        $this->requestEncoded = json_encode($this->request);

        if ($this->printCommunication)
            echo 'API request: ' . $this->requestEncoded;

        $context = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/json-rpc' . "\r\n" . $this->extraHeaders,
                'content' => $this->requestEncoded
            )
        );
        if ($this->sslContext)
            $context['ssl'] = $this->sslContext;

        $streamContext = stream_context_create($context);

        $fileHandler = @fopen($this->getApiUrl(), 'rb', false, $streamContext);
        if (!$fileHandler)
            throw new Exception('Could not connect to "' . $this->getApiUrl() . '"');

        $this->response = @stream_get_contents($fileHandler);

        if ($this->printCommunication)
            echo $this->response . "\n";

        if ($this->response === false)
            throw new Exception('Could not read data from "' . $this->getApiUrl() . '"');

        $this->responseDecoded = json_decode($this->response);

        if (!is_object($this->responseDecoded) && !is_array($this->responseDecoded))
            throw new Exception('Could not decode JSON response.');
        if (property_exists($this->responseDecoded, 'error'))
            throw new Exception('API error ' . $this->responseDecoded->error->code . ': ' . $this->responseDecoded->error->data);

        if ($resultArrayKey && is_array($this->responseDecoded->result))
            return $this->convertToAssociativeArray($this->responseDecoded->result, $resultArrayKey);
        else
            return $this->responseDecoded->result;
    }

    // ---------------------------------------------------------------
    // API method wrappers (only those used by sysMonDash)
    // ---------------------------------------------------------------

    /**
     * Login to Zabbix API. Zabbix 7.x uses 'username' instead of 'user'.
     *
     * @param array $params  Must contain 'username' (or 'user') and 'password'
     * @return string  Auth token
     * @throws Exception
     */
    public function userLogin($params = array())
    {
        // Normalize: accept both 'user' and 'username' keys
        if (isset($params['user']) && !isset($params['username'])) {
            $params['username'] = $params['user'];
            unset($params['user']);
        }

        $this->auth = $this->request('user.login', $params, '', false);
        return $this->auth;
    }

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function userGet($params = array())
    {
        return $this->request('user.get', $params);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function triggerGet($params = array())
    {
        return $this->request('trigger.get', $params);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function maintenanceGet($params = array())
    {
        return $this->request('maintenance.get', $params);
    }

    /**
     * @return string  Zabbix API version
     * @throws Exception
     */
    public function apiinfoVersion()
    {
        return $this->request('apiinfo.version', array(), '', false);
    }

    /**
     * Get active problems (modern alternative to trigger.get for Zabbix 5.x+).
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function problemGet($params = array())
    {
        return $this->request('problem.get', $params);
    }

    /**
     * Convert an array of objects to an associative array indexed by the given key.
     *
     * @param array  $objectArray
     * @param string $key
     * @return array
     */
    private function convertToAssociativeArray($objectArray, $key)
    {
        $result = array();
        foreach ($objectArray as $object) {
            if (is_object($object) && property_exists($object, $key)) {
                $result[$object->$key] = $object;
            }
        }
        return $result;
    }
}
