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
	 * @var mixed
	 */
	private $instanceReference;

	/**
	 * @var string
	 */
	private $conformsTo;

    /**
     * DependencyMapping constructor.
     * @param array $fromArray Array to parse mapping from
	 * @param string $conformsTo interface/class to validate $instance against
     * @throws DependencyMappingException
     */
    public function __construct(Array $fromArray, $conformsTo)
    {
        if (!array_key_exists(self::InstanceReference, $fromArray))
            throw new DependencyMappingException("Invalid dependency mapping found - no InstanceReference specified");

		$this->conformsTo = $conformsTo;
        $this->instanceReference = $fromArray[self::InstanceReference];

        // Figure out type
        if (!array_key_exists(self::Type, $fromArray)) {
            if (is_string($this->instanceReference) || is_callable($this->instanceReference))
                $this->type = self::Type_Transient;

            if (is_object($this->instanceReference))
                $this->type = self::Type_Instance;
        } else {
			$this->type = $fromArray[self::Type];
		}
    }

    /**
     * @return IDependencyDefintion
     */
    public function GetDefinition()
    {
        switch ($this->type) {
            case self::Type_Singleton:
                return new SingletonDependency($this->instanceReference);
            case self::Type_Instance:
                return new InstanceDependency($this->instanceReference);

            default:
                return new TransientDependency($this->instanceReference);
        }
    }
}

class DependencyMappingException extends Exception
{
}