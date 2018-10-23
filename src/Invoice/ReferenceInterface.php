<?php namespace Neonbug\FiscalVerification\Invoice;

/**
 * Base class for reference invoices.
 *
 * @author Marko Zagar <marko@chipolo.net>
 */
abstract class ReferenceInterface
{
    /**
     * Date format that will be used for this reference
     *
     * @var string
     */
    public $date_time_format;

    /**
     * Create array representation of the reference
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Set date format that will be used
     *
     * @param string $format
     */
    public function setDateTimeFormat($format)
    {
        $this->date_time_format = $format;
    }

    /**
     * Get date format that we will use
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->date_time_format;
    }
}
