<?php

/**
 * GeoFrStreetBaseTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class GeoFrStreetBaseTable extends PluginGeoFrStreetBaseTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object GeoFrStreetBaseTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('GeoFrStreetBase');
    }
}