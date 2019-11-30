<?php

namespace sabri\Extractor\db;

use SleekDB\SleekDB;

class Link
{
    public $link;
    public $hostname;
    public $createdAt;
    public $updatedAt;

    private $store;
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
        $this->init();
    }

    protected function init(): void
    {
        $this->getStore();
    }

    protected function storeName(): string
    {
        return 'links';
    }

    protected function databaseDir(): string
    {
        return $this->database;
    }

    public function getStore()
    {
        if (!$this->store) {
            $this->store = SleekDB::store($this->storeName(), $this->databaseDir());
        }

        return $this->store;
    }

    public function serialize(): array
    {
        return [
            'link' => $this->link,
            'hostname' => $this->hostname,
            'createdAt' => $this->createdAt ? $this->createdAt : date("Y-m-d H:i:s"),
            'updatedAt' => $this->updatedAt ? $this->updatedAt : date("Y-m-d H:i:s")
        ];
    }

    public function deserialize(array $link): void
    {
        $reflect = new ReflectionClass(Link::class);
        $props = $reflect->getProperties();
        foreach ($props as $prop) {
            if (isset($link[$prop->getName()])) {
                $this->{$prop->getName()} = $link[$prop->getName()];
            }
        }
    }

    public static function store(string $database)
    {
        $link = new Link($database);

        return $link->getStore();
    }

    public function insert()
    {
        return $this->store->insert($this->serialize());
    }
}
