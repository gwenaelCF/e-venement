<?php

/**
 * GeoFrRegionTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class GeoFrRegionTable extends PluginGeoFrRegionTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object GeoFrRegionTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('GeoFrRegion');
    }
}
