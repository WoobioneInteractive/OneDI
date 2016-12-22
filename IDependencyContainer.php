<?php

interface IDependencyContainer
{
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
}