<?php

class DependencyMapping
{
    const Type = 'type';
    const InstanceReference = 'instanceReference';

    const Type_Transient = 'DependencyTypeTransient';
    const Type_Singleton = 'DependencyTypeSingleton';
    const Type_Instance = 'DependencyTypeInstance';

    /**
     * @var string (self::Type_xxx)
     */
    private $type;

    /**
     * DependencyMapping constructor.
     * @param array $fromArray Array to parse mapping from
     * @throws DependencyMappingException
     */
    public function __construct(Array $fromArray)
    {
        if (!array_key_exists(self::InstanceReference, $fromArray))
            throw new DependencyMappingException("Invalid dependency mapping found - no InstanceReference specified");

        $instanceReference = $fromArray[self::InstanceReference];

        // No type was specified - tr
        if (!array_key_exists(self::Type, $fromArray)) {
            if (is_string($instanceReference) || is_callable($instanceReference))
                $this->type = self::Type_Transient;

            if (is_object($instanceReference))
                $this->type = self::Type_Instance;
        }
    }

    /**
     * @return IDependencyDefintion
     */
    public function GetDefinition()
    {
        switch ($this->type) {
            case self::Type_Singleton:
                return new SingletonDependency();
            case self::Type_Instance:
                return new InstanceDependency();

            default:
                return new TransientDependency();
        }
    }
}

class DependencyMappingException extends Exception
{
}