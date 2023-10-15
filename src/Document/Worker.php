<?php

namespace App\Document;

use App\Repository\WorkerRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotNull;


#[MongoDB\Document(repositoryClass: WorkerRepository::class)]
class Worker
{
    #[MongoDB\Id(type: "string", strategy: "INCREMENT")]
    protected ?string $id = null;

    #[Assert\NotNull()]
    #[Assert\Length(['min' => 5, 'max' => 20])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected string $name;

    #[Assert\NotNull()]
    #[Assert\Length(['min' => 4, 'max' => 20])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected string $surname;

    #[MongoDB\Field(type: "string")]
    protected string $workshopId;


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    public function getWorkshopId(): string
    {
        return $this->workshopId;
    }

    public function setWorkshopId(string $workshopId): self
    {
        $this->workshopId = $workshopId;
        return $this;
    }
}
