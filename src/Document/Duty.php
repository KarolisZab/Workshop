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
    #[Assert\Length(['min' => 4, 'max' => 20])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected string $duty;

    #[Assert\NotNull()]
    #[Assert\Length(['min' => 10, 'max' => 155])]
    #[Assert\NotBlank()]
    #[MongoDB\Field(type: "string")]
    protected ?string $description;

    #[MongoDB\ReferenceOne(targetDocument: Worker::class)]
    protected string $workerId;
    

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
}
