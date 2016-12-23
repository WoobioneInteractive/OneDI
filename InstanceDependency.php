<?php

/**
 * Class InstanceDependency
 */
class InstanceDependency implements IDependencyDefinition
{
    /**
     * @var mixed
     */
    private $instance;

    /**
     * InstanceDependency constructor.
     * @param mixed $instance
     * @param string $conformsTo interface/class to validate $instance against
     * @throws InstanceDependencyException
     */
    public function __construct($instance, $conformsTo)
    {
        if (interface_exists($conformsTo) && !OnePHP::ClassImplements($instance, $conformsTo))
            throw new InstanceDependencyException("Instance of '" . get_class($instance) . "' does not conform to interface '$conformsTo'");

        if (class_exists($conformsTo) && !is_a($instance, $conformsTo))
            throw new InstanceDependencyException("Instance of '" . get_class($instance) . "' is not of type '$conformsTo'");

        $this->instance = $instance;
    }

    /**
     * Get constructed instance of defined dependency
     * @param IDependencyContainer $container
     * @return mixed
     */
    public function GetInstance(IDependencyContainer $container)
    {
        return $this->instance;
    }

    /**
     * See if instance should be stored as a shared instance
     * @return bool
     */
    public function IsSharedInstance()
    {
        return true;
    }
}

/**
 * Class InstanceDependencyException
 */
class InstanceDependencyException extends Exception
{
}