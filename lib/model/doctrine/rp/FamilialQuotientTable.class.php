<?php

/**
 * FamilialQuotientTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class FamilialQuotientTable extends PluginFamilialQuotientTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object FamilialQuotientTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('FamilialQuotient');
    }
}