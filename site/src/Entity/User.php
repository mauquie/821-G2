<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=250,maxMessage="Please less 250 characters")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=250,maxMessage="Please less 250 characters")
     * @Assert\NotBlank()
     */
    private $username;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=8,minMessage="Please over 8 characters")
     */
    private $password;
    
    /**
     *
     * @ORM\Column(type="json")
     *
     */
    private $roles = [];
    
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $reset_token;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $activation_token;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function setEmail(string $email)
    {
        $this->email = $email;
    }
    
    public function getUsername(): ?string
    {
        return $this->username;
    }
    
    public function setUsername(string $username)
    {
        $this->username = $username;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    public function setPassword(string $password)
    {
        $this->password = $password;
        
    }
    
    public function getSalt()
    {
        
    }
    
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = null;
        $this->roles = array();
        foreach($roles as $role ){
            $this->roles += $roles;
            echo $role;
        }                
        return $this;
    }
    
    public function eraseCredentials()
    {
    }
    
    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }
    
    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;
        
        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activation_token;
    }

    public function setActivationToken(?string $activation_token): self
    {
        $this->activation_token = $activation_token;

        return $this;
    }
    
}