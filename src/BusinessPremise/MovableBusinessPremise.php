<?php namespace Neonbug\FiscalVerification\BusinessPremise;

/**
 * Represents a movable business premise.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
class MovableBusinessPremise extends BusinessPremise
{
    /**
     * Can be one of:
     * - A (movable object (e.g. vehicle, movable stand)),
     * - B (object at a permanent location (e.g. market stand, newsstand))
     * - C (individual electronic device for issuing invoices or pre-numbered invoice book in cases
     *      when the person liable doesn't use other business premises)
     *
     * @var string
     */
    public $premise_type;
    
    /**
     * Create a new MovableBusinessPremise instance
     *
     * @param string $premise_type Premise type, can be one of: A, B, C
     */
    public function __construct($premise_type)
    {
        $this->premise_type = $premise_type;
    }
}
