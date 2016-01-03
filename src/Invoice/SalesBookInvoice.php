<?php namespace Neonbug\FiscalVerification\Invoice;

/**
 * Represents a sales book invoice.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
class SalesBookInvoice extends Invoice
{
    /**
     * Number of the invoice set (original invoice + its two copies)
     *     from the pre-numbered invoice book
     * @var string
     */
    public $set_number;
    
    /**
     * Serial number of the pre-numbered invoice book
     * @var string
     */
    public $serial_number;
    
    /**
     * Date and time of issuing the invoice (unix timestamp)
     * @var int
     */
    public $issue_date;
    
    /**
     * Mark of business premises
     * @var string
     */
    public $business_premise_id;
    
    /**
     * Create a new Invoice instance
     *
     * @param  Guid    $message_id           Unique message id (should be different every time)
     * @param  string  $invoice_number       Number of the invoice (sequence number of the invoice)
     * @param  float   $invoice_amount       Value of the invoice (with VAT and discounts)
     * @param  float   $payment_amount       Value for payment
     * @param  int     $tax_number           Tax number of the person liable
     * @param  string  $set_number           Number of the invoice set (original invoice + its two copies)
     *                                       from the pre-numbered invoice book
     * @param  string  $serial_number        Serial number of the pre-numbered invoice book
     * @param  int     $issue_date           Date and time of issuing the invoice (unix timestamp)
     * @param  string  $business_premise_id  Mark of business premises
     */
    public function __construct(
        $message_id,
        $invoice_number,
        $invoice_amount,
        $payment_amount,
        $tax_number,
        $set_number,
        $serial_number,
        $issue_date,
        $business_premise_id
    ) {
        parent::__construct(
            $message_id,
            $invoice_number,
            $invoice_amount,
            $payment_amount,
            $tax_number
        );
        
        $this->set_number          = $set_number;
        $this->serial_number       = $serial_number;
        $this->issue_date          = $issue_date;
        $this->business_premise_id = $business_premise_id;
    }
}
