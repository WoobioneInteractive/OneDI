<?php

interface IDependencyDefinition
{
    /**
     * Get constructed instance of defined dependency
     * @param IDependencyContainer $container
     * @return mixed
     */
    public function GetInstance(IDependencyContainer $container);

    /**
     * See if instance should be stored as a shared instance
     * @return bool
     */
    public function IsSharedInstance();
}