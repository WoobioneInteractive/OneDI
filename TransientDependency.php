<?php

/**
 * Class TransientDependency
 */
class TransientDependency implements IDependencyDefinition
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var callable
     */
    private $callable;

    /**
     * TransientDependency constructor.
     * @param string|callable $resolvableAsInstance
     * @param string $conformsTo interface/class to validate $instance against
     * @throws TransientDependencyException
     */
    public function __construct($resolvableAsInstance, $conformsTo)
    {
        // Callable constructor was supplied
        if (is_callable($resolvableAsInstance)) {
            $this->callable = $resolvableAsInstance;
        }

        // Only class name was supplied
        else if (is_string($resolvableAsInstance)) {
            if (!class_exists($resolvableAsInstance))
                throw new TransientDependencyException("No such class '$resolvableAsInstance'");

            if (interface_exists($conformsTo) && !OnePHP::ClassImplements($resolvableAsInstance, $conformsTo))
                throw new TransientDependencyException("Class '$resolvableAsInstance' does not implement interface '$conformsTo'");

            if (class_exists($conformsTo) && $resolvableAsInstance != $conformsTo)
                throw new TransientDependencyException("Impossible to cast class '$resolvableAsInstance' to '$conformsTo'");

            $this->className = $resolvableAsInstance;
        }

        else {
            throw new TransientDependencyException("Could not build dependency - '$resolvableAsInstance' can not be resolved");
        }
    }

    /**
     * Get constructed instance of defined dependency
     * @param IDependencyContainer $container
     * @return object
     */
    public function GetInstance(IDependencyContainer $container)
    {
        if ($this->callable)
            return $container->Call($this->callable);

        return $container->Autowire($this->className);
    }

    /**
     * See if instance should be stored as a shared instance
     * @return bool
     */
    public function IsSharedInstance()
    {
        return false;
    }
}

/**
 * Class TransientDependencyException
 */
class TransientDependencyException extends Exception
{
}