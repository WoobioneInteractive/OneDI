<?php

interface IDependencyCollection
{
    /**
     * @param string $identifier
     * @return bool
     */
    public function Has($identifier);

    /**
     * @param string $identifier
     * @return DependencyMapping
     */
    public function Get($identifier);
}