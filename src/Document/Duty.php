<?php

namespace App\Document;

use App\Repository\DutyRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(repositoryClass: DutyRepository::class)]
class Duty
{
    #[MongoDB\Id(type: "string", strategy: "INCREMENT")]
    protected ?string $id = null;

    #[Assert\NotNull()]
    #[Assert\Length(['min' => 4, 'max' => 50])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected string $duty;

    #[Assert\NotNull()]
    #[Assert\Length(['min' => 10, 'max' => 155])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected ?string $description;

    #[MongoDB\Field(type: "string")]
    protected string $workerId;

    #[MongoDB\Field(type: "string")]
    protected string $workshopId;
    

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDuty(): ?string
    {
        return $this->duty;
    }

    public function setDuty(?string $duty): self
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

    public function getWorkerId(): string
    {
        return $this->workerId;
    }

    public function setWorkerId(string $workerId): self
    {
        $this->workerId = $workerId;
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
