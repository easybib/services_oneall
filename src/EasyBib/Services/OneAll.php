<?php
namespace EasyBib\Services;

class OneAll
{
    protected $client;

    protected $format = 'json';

    protected $privateKey;

    protected $publicKey;

    protected $subDomain;

    /**
     * CTOR
     *
     * @param string $publicKey  The public key.
     * @param string $privateKey The private key.
     * @param string $subDmain   The sub domain.
     */
    public function __construct($publicKey, $privateKey, $subDomain)
    {
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;
        $this->subDomain  = $subDomain;
    }

    /**
     * Acceptor pattern to inject objects.
     *
     * @param mixed $var
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function accept($var)
    {
        if ($var instanceof \HTTP_Request2) {
            $this->client = $var;
            return $this;
        }
        throw new \InvalidArgumentException("Unknown variable: " . var_export($var, true));
    }

    /**
     * Creates a HTTP client if none is set.
     *
     * @return \HTTP_Request2
     */
    public function getClient()
    {
        if (false === ($this->client instanceof \HTTP_Request2)) {
            $this->client = new \HTTP_Request2;
            $this->client->setConfig(array(
                'adapter'       => 'HTTP_Request2_Adapter_Curl',
                'timeout'       => 3,
                'max_redirects' => 1,
            ));
        }
        $this->client->setAuth($this->publicKey, $this->privateKey);
        return $this->client;
    }

    /**
     * Get connection by token.
     *
     * @param string $token
     *
     * @return \stdClass
     * @throws \RuntimeException On transport error.
     */
    public function getConnection($token)
    {
        $response = $this->makeRequest(
            sprintf('%s/connections/%s.%s', $this->getDomain(), $token, $this->format)
        );
        return $this->parseResponse($response);
    }

    /**
     * @param string $url    The URI to request against.
     * @param string $method The request method.
     *
     * @return \HTTP_Request2_Response
     * @throws \RuntimeException On transport error.
     */
    protected function makeRequest($url, $method = \HTTP_Request2::METHOD_GET)
    {
        $client = $this->getClient();

        try {
            $client->setUrl($url);
            $client->setMethod(\HTTP_Request2::METHOD_GET);

            return $client->send();

        } catch (\HTTP_Request2_Exception $e) {
            throw new \RuntimeException("Failed contacting OneAll: " . $e->getMessage(), null, $e);
        }
    }

    /**
     * Get the domain to run requests against.
     *
     * @return string
     */
    protected function getDomain()
    {
        return sprintf('https://%s.api.oneall.com', $this->subDomain);
    }

    /**
     * Evaluate the response and return the data portion.
     *
     * @return \stdClass
     * @throws \RuntimeException When the HTTP status code is not 200.
     * @throws \DomainException  When we cannot parse/evaluate the status of the response.
     * @throw  \RuntimeException When the user did not authenticate.
     */
    protected function parseResponse(\HTTP_Request2_Response $response)
    {
        if ($response->getStatus() != 200) {
            throw new \RuntimeException("OneAll API error: " . $response->getBody());
        }

        $json   = $response->getBody();
        $answer = json_decode($json);

        if (!isset($answer->response->result->status->flag) || (false === ($answer instanceof \stdClass))) {
            throw new \DomainException("Could not decode/parse response from OneAll: {$json}");
        }

        $status = $answer->response->result->status;
        if ($status->flag == 'error') {
            throw new \RuntimeException($status->info, $status->code);
        }
        return $answer->response->result->data;
    }
}
