<?php namespace Neonbug\FiscalVerification\Invoice;

/**
 * Base class for invoices.
 *
 * @author Marko Zagar <marko@chipolo.net>
 */
class ReferenceSalesBook extends ReferenceInterface
{
    /**
     *     Number of the invoice (sequence number of the invoice)
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var string
     */
    public $invoice_number;

    /**
     *     Mark of business premises
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var string
     */
    public $business_premise_id;

    /**
     *     Mark of the electronic device
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var string
     */
    public $electronic_device_id;

    /**
     *      Date and time of issuing the invoice (unix timestamp)
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    public $issue_date_time;

    /**
     * Create a new Reference Sales Book instance
     *
     * @param string $invoice_number
     * @param string $business_premise_id
     * @param string $electronic_device_id
     * @param int    $issue_date_time
     */
    public function __construct(
        $invoice_number,
        $business_premise_id,
        $electronic_device_id,
        $issue_date_time
    ) {
        $this->invoice_number       = $invoice_number;
        $this->business_premise_id  = $business_premise_id;
        $this->electronic_device_id = $electronic_device_id;
        $this->issue_date_time      = $issue_date_time;
    }

    /**
     * Create array representation of the reference
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'ReferenceSalesBookIdentifier'    => array(
                'InvoiceNumber'      => $this->invoice_number,
                'BusinessPremiseID'  => $this->business_premise_id,
                'ElectronicDeviceID' => $this->electronic_device_id,
            ),
            'ReferenceSalesBookIssueDateTime' => date($this->getDateTimeFormat(), $this->issue_date_time),
        );
    }
}
