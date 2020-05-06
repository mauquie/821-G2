<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $beginAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date_creation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $last_modification;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $creator;

    /**
     * @ORM\Column(type="json")
     */
    private $editors = [];
    
    /**
     * @ORM\Column(type="text")
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg" })
     */
    private $photo; 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeInterface $beginAt): self
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(string $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getLastModification(): ?string
    {
        return $this->last_modification;
    }

    public function setLastModification(string $last_modification): self
    {
        $this->last_modification = $last_modification;

        return $this;
    }

    public function getCreator(): ?string
    {
        return $this->creator;
    }

    public function setCreator(string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getEditors(): ?array
    {
        return $this->editors;
    }

    public function setEditors(array $editors): self
    {
        if(empty($this->editors)){
            $this->editors = $editors;
        }
        else{
            foreach($this->editors as $editor){
                if($editor==$editors[0]){
                    return $this;
                }
            }
            array_push($this->editors,$editors[0]);
        }
        
        return $this;
    }
    
    public function getPhoto()
    {
        return $this->photo;
    }
    public function setPhoto($photo) 
    {
        $this->photo = $photo;
        return $this;
    } 
}
