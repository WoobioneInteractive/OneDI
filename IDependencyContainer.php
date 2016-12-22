<?php

interface IDependencyContainer
{
    /**
     * Resolve dependency by identifier (usually and interface)
     * @param string $identifier
     * @return $identifier::class
     */
    public function Resolve($identifier);

    /**
     * Autowire class by name
     * @param string $class
     * @return object Instance of $class
     */
    public function Autowire($class);

    /**
     * @param callable $callable
     * @param array $parameters
     * @return mixed
     */
    public function Call(callable $callable, Array $parameters = []);

    /**
     * Add transient dependency
     * - a new instance is created for every depending class
     * @param string $className Class name for class to create (made by SomeClass::class)
     * @param string $identifier Identifier for dependency - normally an interface (i.e. ISomeInterface::class)
     */
    public function AddTransient($className, $identifier);

    /**
     * Add singleton instance dependency
     * - instance is created once and is then returned to every depending class
     * @param $className
     * @param $identifier
     */
    public function AddSingleton($className, $identifier);

    /**
     * @param $instance
     * @param $identifier
     * @return mixed
     */
    public function AddInstance($instance, $identifier);
}