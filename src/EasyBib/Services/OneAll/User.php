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
    protected $data;

    /**
     * CTOR
     *
     * @param \stdClass $data
     */
    public function __construct(\stdClass $data)
    {
        $this->data = $data;
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
        if (false === isset($this->data->identity->emails)) {
            return array();
        }

        $emails = $this->data->identity->emails;
        if (true !== $confirmed) {
            return $emails;
        }

        $keep = array();

        foreach ($emails as $email) {
            if (true !== $email->is_verified) {
                continue;
            }
            $keep[] = $email;
        }
        return $keep;
    }
}
