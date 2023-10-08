<?php

namespace App\Document;

use App\Repository\WorkerRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;


#[MongoDB\Document(repositoryClass: WorkerRepository::class)]
class Worker
{
    #[MongoDB\Id(type: "string", strategy: "INCREMENT")]
    protected ?string $id = null;
    #[MongoDB\Field(type: "string")]
    protected string $name;
    #[MongoDB\Field(type: "string")]
    protected string $surname;
    #[MongoDB\ReferenceOne(targetDocument: Workshop::class, inversedBy: "id")]
    protected string $workshopId;


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }
}
