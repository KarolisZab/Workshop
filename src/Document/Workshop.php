<?php

namespace App\Document;

use App\Repository\WorkshopRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(repositoryClass: WorkshopRepository::class)]
class Workshop
{
    #[MongoDB\Id(type: "string", strategy: "INCREMENT")]
    protected ?string $id = null;
    #[MongoDB\Field(type: "string")]
    protected string $title;
    #[MongoDB\Field(type: "string")]
    protected string $category;

    public function getId(): ?string 
    {
        return $this->id;
    }

    public function getTitle(): string 
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getCategory(): string 
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }
}
