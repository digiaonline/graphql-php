<?php

namespace Digia\GraphQL\Test\Functional\Execution;

class Human
{

    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class Person
{

    public $name;

    public $pets;

    public $friends;

    public function __construct(
        string $name,
        array $pets = [],
        array $friends = []
    ) {
        $this->name = $name;
        $this->friends = $friends;
        $this->pets = $pets;
    }
}

class Dog
{

    public $name;

    public $woofs;

    public function __construct(string $name, bool $woofs)
    {
        $this->name = $name;
        $this->woofs = $woofs;
    }
}

class Cat
{

    public $name;

    public $meows;

    public function __construct(string $name, bool $meows)
    {
        $this->name = $name;
        $this->meows = $meows;
    }
}