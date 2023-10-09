<?php

namespace App\Document;

use App\Repository\WorkshopRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotNull;

#[MongoDB\Document(repositoryClass: WorkshopRepository::class)]
class Workshop
{
    #[MongoDB\Id(type: "string", strategy: "INCREMENT")]
    protected ?string $id = null;

    #[Assert\NotNull()]
    #[Assert\Length(['min' => 4, 'max' => 20])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected ?string $title;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Length(['min' => 4, 'max' => 20])]
    #[MongoDB\Field(type: "string")]
    protected ?string $category;

    public function getId(): ?string 
    {
        return $this->id;
    }

    public function getTitle(): ?string 
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getCategory(): ?string 
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }
}
