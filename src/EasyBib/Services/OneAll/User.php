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
namespace EasyBib\Services\OneAll;

/**
 * EasyBib\Services\OneAll\User
 *
 * Small entity to represent the user data we retrieve from OneAll.
 *
 * @category  Web Services
 * @package   EasyBib\Services\OneAll
 * @author    Till Klampaeckel <till@php.net>
 * @copyright 2012-2013, Till Klampaeckel
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/easybib/services_oneall
 */
class User
{
    /**
     * @var \stdClass
     */
    protected $user;

    /**
     * CTOR
     *
     * @param \stdClass $data
     *
     * @throws \InvalidArgumentException When no 'user' data is contained.
     */
    public function __construct(\stdClass $data)
    {
        $this->setUser($data);
    }

    /**
     * Get all (confirmed) emails of the user.
     *
     * @param boolean $confirmed 'true' by default, set to false and get all.
     *
     * @return array
     */
    public function getEmails($confirmed = true)
    {
        if (false === isset($this->user->identity->emails)) {
            return array();
        }

        $emails = $this->user->identity->emails;
        if (true !== $confirmed) {
            return $emails;
        }

        $keep = array();

        foreach ($emails as $email) {
            if (false === property_exists($email, 'is_verified')) {
                continue;
            }
            if (true !== $email->is_verified) {
                continue;
            }
            $keep[] = $email;
        }
        return $keep;
    }

    /**
     * Return the first name of a user (givenName).
     *
     * @return string
     */
    public function getFirst()
    {
        if (!isset($this->user->identity->name)) {
            return '';
        }
        $name = $this->user->identity->name;
        if (!isset($name->givenName)) {
            return '';
        }
        return $name->givenName;
    }

    /**
     * Most likely a URL identifying the user.
     *
     * @return string
     */
    public function getId()
    {
        return $this->user->identity->id;
    }

    /**
     * Return the last name of a user (familyName)
     *
     * @return string
     */
    public function getLast()
    {
        if (!isset($this->user->identity->name)) {
            return '';
        }
        $name = $this->user->identity->name;
        if (!isset($name->familyName)) {
            return '';
        }
        return $name->familyName;
    }

    /**
     * @param bool $source
     *
     * @return string|\stdClass
     */
    public function getProvider($source = false)
    {
        if (true !== $source) {
            return $this->user->identity->provider;
        }
        if (!property_exists($this->user->identity, 'source')) {
            throw new \DomainException("No source.");
        }
        return $this->user->identity->source;
    }

    /**
     * @return \stdClass
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \stdClass $user
     *
     * @return User
     */
    public function setUser(\stdClass $user)
    {
        if (false === property_exists($user, 'identity')) {
            throw new \InvalidArgumentException("Object must have 'identity' property.");
        }
        $this->user = $user;
        return $this;
    }
}
