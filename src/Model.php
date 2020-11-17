<?php


namespace TairoLima\MongodbODM;


abstract class Model
{
    private string $_id;
    private ?string $created = null;
    private ?string $modified = null;

    public function __construct(?string $id = null)
    {
        $this->_id = (string) MongoObjectID::getId($id);
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getCreated(): ?string
    {
        return $this->created;
    }
    public function setCreated(?string $created): void
    {
        $this->created = $created;
    }

    public function getModified(): ?string
    {
        return $this->modified;
    }
    public function setModified(?string $modified): void
    {
        $this->modified = $modified;
    }
}