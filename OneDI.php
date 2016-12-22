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
     * OneDI constructor.
     */
    public function __construct()
    {

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
                $this->addSharedInstance($instance);

            return $instance;
        }
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