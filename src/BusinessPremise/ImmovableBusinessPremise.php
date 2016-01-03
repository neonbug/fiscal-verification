<?php namespace Neonbug\FiscalVerification\BusinessPremise;

/**
 * Represents an immovable business premise.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
class ImmovableBusinessPremise extends BusinessPremise
{
    public $cadastral_number;
    public $building_number;
    public $building_section_number;
    public $street;
    public $house_number;
    public $house_number_additional = null;
    public $community;
    public $city;
    public $postal_code;
    
    /**
     * Create a new ImmovableBusinessPremise instance
     *
     * @param int    $cadastral_number        Number of the cadastral community
     * @param int    $building_number         Number of the building
     * @param int    $building_section_number Number of the part of the building
     * @param string $street                  Street
     * @param string $house_number            House number
     * @param string $community               Town
     * @param string $city                    Post office
     * @param string $postal_code             Postcode
     * @param string $house_number_additional Optional; Addition to the house number
     */
    public function __construct(
        $cadastral_number,
        $building_number,
        $building_section_number,
        $street,
        $house_number,
        $community,
        $city,
        $postal_code,
        $house_number_additional = null
    ) {
        $this->cadastral_number        = $cadastral_number;
        $this->building_number         = $building_number;
        $this->building_section_number = $building_section_number;
        $this->street                  = $street;
        $this->house_number            = $house_number;
        $this->community               = $community;
        $this->city                    = $city;
        $this->postal_code             = $postal_code;
        $this->house_number_additional = $house_number_additional;
    }
}
