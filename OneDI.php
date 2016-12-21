<?php

/**
 * Class OneDI
 * @uses OnePHP
 */
class OneDI implements IDependencyContainer
{
    /**
     * @var IWritableDependencyCollection
     */
    private $writablePrimaryCollection;

    /**
     * @var IDependencyCollection[]
     */
    private $collections;

    /**
     * OneDI constructor.
     * @param $collection
     */
    public function __construct($collection)
    {

    }

    public function AddCollection(IDependencyCollection $collection)
    {
        array_push($this->collections, $collection);
    }

    /**
     * Autowire class by name
     * @param string $class
     * @return object Instance of $class
     */
    public function Autowire($class)
    {
        // TODO: Implement Autowire() method.
    }

    /**
     * @param $callable
     * @return mixed
     */
    public function Call(callable $callable, Array $parameters = [])
    {
        // TODO: Implement Call() method.
    }
}