<?php

namespace AppBundle\Entity;

/**
 * Chercher
 */
class Chercher
{
    /**
     *
     * @var string 
     */
    private $email;
   
    /**
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * 
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

}
