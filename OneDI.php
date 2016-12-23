<?php

/**
 * Class OneDI
 * @uses OnePHP
 */
class OneDI implements IDependencyContainer, IDependencyCollectionDelegate
{
    /**
     * @var IDependencyDefinition[]
     */
    private $dependencyCollection = [];

    /**
     * @var IDependencyCollectionDelegate[]
     */
    private $collectionDelegates = [];

    /**
     * Shared instances declared by defintions
     * @var array
     */
    private $sharedInstances = [];

	/**
	 * Specifies whether dependencies without definitions should still be resolved
	 * - i.e. interface IStuff would be resolved to Stuff even without definition
	 * @var bool
	 */
	public $AllowUnknownDependencies = true;

	/**
	 * Interface prefix - used for unknown dependency resolving
	 * @var string
	 */
	public $InterfacePrefix = 'I';

	/**
	 * Interface suffix - used for unknown dependency resolving
	 * @var string
	 */
	public $InterfaceSuffix = '';

	/**
	 * OneDI constructor.
	 * @param IDependencyCollectionDelegate[] $collectionDelegates
	 */
    public function __construct(Array $collectionDelegates = [])
    {
		$this->addSharedInstance(IDependencyContainer::class, $this);
		foreach($collectionDelegates as $delegate) {
			$this->AddCollectionDelegate($delegate);
		}
    }

	/**
	 * @param ReflectionMethod|callable $method
	 * @param array $customParameters Extra parameters as assoc array to pass
	 * @return array Parameters
	 * @throws OneDIException
	 */
    private function resolveParameters($method, Array $customParameters = []) {
		$isAlreadyReflection = is_a($method, ReflectionMethod::class);
		$reflection = $isAlreadyReflection ? $method : new ReflectionFunction($method);
		$ownerName = $isAlreadyReflection ? $reflection->getDeclaringClass()->getName() : $reflection->getName();
		$parameters = $reflection->getParameters();

		/* @var $parameter ReflectionParameter */
		foreach ($parameters as &$parameter) {
			$parameterReflection = $parameter->getClass();
			$parameterType = $parameterReflection ? $parameterReflection->getName() : null;
			$parameterName = $parameter->getName();

			if (array_key_exists($parameterName, $customParameters)) {
				$parameter = $customParameters[$parameterName];
			} else if (interface_exists($parameterType) || class_exists($parameterType)) {
				$parameter = $this->Resolve($parameterType);
			} else if ($parameter->isDefaultValueAvailable()) {
				unset($parameter);
			} else if ($parameter->allowsNull()) {
				$parameter = null;
			} else {
				throw new OneDIException("Failed to autowire class '$ownerName' - unable to resolve property '{$parameter->getName()}'");
			}
		}

		return $parameters;
	}

    /**
     * Add shared instance
     * @param string $identifier
     * @param object $instance
     */
    private function addSharedInstance($identifier, $instance)
    {
        $this->sharedInstances[$identifier] = $instance;
    }

    /**
     * See if shared instance exists
     * @param string $identifier
     * @return bool
     */
    private function hasSharedInstance($identifier)
    {
        return array_key_exists($identifier, $this->sharedInstances);
    }

    /**
     * Get shared instance
     * @param string $identifier
     * @return object|null
     */
    private function getSharedInstance($identifier)
    {
        return $this->hasSharedInstance($identifier) ? $this->sharedInstances[$identifier] : null;
    }

    /**
     * See if collection contains definition for $identifier
     * @param string $identifier
     * @return bool
     */
    public function HasDefinition($identifier)
    {
        return array_key_exists($identifier, $this->dependencyCollection);
    }

    /**
     * Get definition for $identifier
     * @param string $identifier
     * @return IDependencyDefinition
     */
    public function GetDefinition($identifier)
    {
        return $this->dependencyCollection[$identifier];
    }

	/**
	 * Add definition for $identifier
	 * @param IDependencyDefinition $definition
	 * @param string $identifier
	 * @throws OneDIException
	 */
    public function AddDefinition(IDependencyDefinition $definition, $identifier)
	{
		if ($this->HasDefinition($identifier))
			throw new OneDIException("Trying to add dependency '$identifier' multiple times");
		$this->dependencyCollection[$identifier] = $definition;
	}

    /**
     * Add collection delegate
     * @param IDependencyCollectionDelegate $collection
     */
    public function AddCollectionDelegate(IDependencyCollectionDelegate $collection)
    {
        array_push($this->collectionDelegates, $collection);
    }

    /**
     * Resolve dependency by identifier (usually and interface)
     * @param string $identifier
     * @return object
     */
    public function Resolve($identifier)
    {
        // Resolve from shared instances
        $sharedInstance = $this->getSharedInstance($identifier);
        if (!is_null($sharedInstance))
            return $sharedInstance;

        // Resolve from primary collection
        if ($this->HasDefinition($identifier)) {
            $definition = $this->GetDefinition($identifier);
            $instance = $definition->GetInstance($this);
            if ($definition->IsSharedInstance())
                $this->addSharedInstance($identifier, $instance);

            return $instance;
        }

        // Resolve from delegate collections

		// Resolve unknown dependencies
		if ($this->AllowUnknownDependencies) {
			if (interface_exists($identifier)) {
				$className = OnePHP::StringReplaceEnd(OnePHP::StringReplaceBeginning($identifier, $this->InterfacePrefix), $this->InterfaceSuffix);
				return $this->Autowire($className);
			}

			if (class_exists($identifier)) {
				return $this->Autowire($identifier);
			}
		}
    }

    /**
     * Autowire class by name
     * @param string $class
     * @return object Instance of $class
	 * @throws OneDIException
     */
    public function Autowire($class)
    {
		if (!class_exists($class))
			throw new OneDIException("Failed to find class '$class' when autowiring");

		$class = new ReflectionClass($class);
		$constructor = $class->getConstructor();
		$parameters = $constructor ? $this->resolveParameters($constructor) : [];

		return $class->newInstanceArgs($parameters);
    }

	/**
	 * Call function with automatic parameter resolving
	 * @param callable $callable
	 * @param array $parameters
	 * @return mixed
	 */
    public function Call(callable $callable, Array $parameters = [])
    {
		$parameters = $this->resolveParameters($callable, $parameters);
		return call_user_func_array($callable, $parameters);
    }

	/**
	 * Add transient dependency
	 * - a new instance is created for every depending class
	 * @param string|callable $classNameOrCallable Class name for class to create (made by SomeClass::class) OR callable instance constructor
	 * @param string $identifier Identifier for dependency - normally an interface (i.e. ISomeInterface::class)
	 */
	public function AddTransient($classNameOrCallable, $identifier)
	{
		$this->AddDefinition(new TransientDependency($classNameOrCallable, $identifier), $identifier);
	}

	/**
	 * Add singleton instance dependency
	 * - instance is created once and is then returned to every depending class
	 * @param string|callable $classNameOrCallable Class name for class to create (made by SomeClass::class) OR callable instance constructor
	 * @param string $identifier Identifier for dependency - normally an interface (i.e. ISomeInterface::class)
	 */
	public function AddSingleton($classNameOrCallable, $identifier)
	{
		$this->AddDefinition(new SingletonDependency($classNameOrCallable, $identifier), $identifier);
	}

	/**
	 * @param $instance
	 * @param $identifier
	 * @return mixed
	 */
	public function AddInstance($instance, $identifier)
	{
		$this->AddDefinition(new InstanceDependency($instance, $identifier), $identifier);
	}

	/**
	 * Add another identifier that resolves equally to $resolvesToIdentifier
	 * @param string $identifier
	 * @param string $resolvesToIdentifier
	 */
	public function AddIdentifier($identifier, $resolvesToIdentifier)
	{
		$this->AddDefinition($this->GetDefinition($resolvesToIdentifier), $identifier);
	}
}

class OneDIException extends Exception
{
}