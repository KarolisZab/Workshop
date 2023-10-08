<?php

namespace App\Document;

use App\Repository\DutyRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(repositoryClass: DutyRepository::class)]
class Duty
{
    #[MongoDB\Id(type: "string", strategy: "UUID")]
    protected ?string $id = null;
    #[MongoDB\Field(type: "string")]
    protected string $duty;
    #[MongoDB\Field(type: "string")]
    protected ?string $description;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDuty(): string
    {
        return $this->duty;
    }

    public function setDuty(string $duty): self
    {
        $this->duty = $duty;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
