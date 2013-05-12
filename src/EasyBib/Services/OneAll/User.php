<?php
namespace EasyBib\Services\OneAll;

/**
 * Small entity to represent the user data we retrieve from OneAll.
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
        $this->user = $data;
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
}
