<?php

/**
 * Interface IDependencyCollectionDelegate
 */
interface IDependencyCollectionDelegate
{
    /**
     * See if collection contains definition for $identifier
     * @param string $identifier
     * @return bool
     */
    public function HasDefinition($identifier);

    /**
     * Get definition for $identifier
     * @param string $identifier
     * @return IDependencyDefinition
     */
    public function GetDefinition($identifier);
}