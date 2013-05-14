<?php
/**
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2012-2013, Till Klampaeckel
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Web Services
 * @package   EasyBib\Services\OneAll
 * @author    Till Klampaeckel <till@php.net>
 * @copyright 2012-2013, Till Klampaeckel
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   GIT: <git_id>
 * @link      http://github.com/easybib/services_oneall
 */
namespace EasyBib\Services;

use EasyBib\Services\OneAll\User;

/**
 * EasyBib\Services\OneAll
 *
 * @category  Web Services
 * @package   EasyBib\Services\OneAll
 * @author    Till Klampaeckel <till@php.net>
 * @copyright 2012-2013, Till Klampaeckel
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/easybib/services_oneall
 */
class OneAll
{
    /**
     * @var \HTTP_Request2
     */
    protected $client;

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
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
     * @throws \DomainException When no 'user' is found.
     */
    public function getConnection($token)
    {
        $response = $this->makeRequest(
            sprintf('/connections/%s.%s', $token, $this->format)
        );
        $connection = $this->parseResponse($response);
        if (!isset($connection->user)) {
            throw new \DomainException("No 'user' found.");
        }
        return $connection;
    }

    /**
     * Retrieve a user.
     *
     * @param string $token The user's token, e.g. from {@link self::getUsers()}.
     *
     * @return User
     */
    public function getUser($token)
    {
        $response = $this->makeRequest(
            sprintf('/users/%s.%s', $token, $this->format)
        );
        $data = $this->parseResponse($response);
        if (!isset($data->user)) {
            throw new \DomainException("No 'user' found.");
        }
        return new User($data->user);
    }

    /**
     * Returns all users who have previously connected through OneAll.
     *
     * OneAll returns a max of 500 records with a single request. Paginate for more.
     *
     * @param int $page Optional page parameter.
     *
     * @return \stdClass
     * @throws \InvalidArgumentException When page parameter is of wrong type.
     * @throws \DomainException          When the response doesn't contain 'users'.
     */
    public function getUsers($page = 1)
    {
        if (!is_int($page)) {
            throw new \InvalidArgumentException("Page parameter must be an integer.");
        }

        $response = $this->makeRequest(
            sprintf('/users.%s?page=%d', $this->format, $page)
        );

        $answer = $this->parseResponse($response);
        if (false === isset($answer->users)) {
            throw new \DomainException("Could not fetch users from response.");
        }
        return $answer->users;
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

        $endpoint = sprintf('%s%s', $this->getDomain(), $url);

        try {
            $client->setUrl($endpoint);
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
     * OneAll returns two different stati:
     *  - request status (can be successful, but response can still contain an error)
     *  - response status
     *
     * @param \HTTP_Request2_Response $response The response object from the client.
     *
     * @return \stdClass
     * @throws \RuntimeException When the HTTP status code is not 200.
     * @throws \DomainException  When we cannot parse/evaluate the status of the response.
     * @throw  \RuntimeException When the user did not authenticate. (or something else)
     */
    protected function parseResponse(\HTTP_Request2_Response $response)
    {
        if ($response->getStatus() != 200) {
            throw new \RuntimeException("OneAll API error: " . $response->getBody());
        }

        $json   = $response->getBody();
        $answer = json_decode($json);

        if (false === ($answer instanceof \stdClass)) {
            throw new \DomainException("Could not decode/parse response from OneAll: {$json}");
        }

        $requestStatus = $answer->response->request->status;
        if ($requestStatus->flag == 'error') {
            throw new \RuntimeException(
                "The request failed: {$requestStatus->info}",
                $requestStatus->code
            );
        }

        // when new accounts are linked - this is set
        if (isset($answer->response->result->status)) {
            $status = $answer->response->result->status;
            if ($status->flag == 'error') {
                throw new \RuntimeException($status->info, $status->code);
            }
        }

        return $answer->response->result->data;
    }
}
