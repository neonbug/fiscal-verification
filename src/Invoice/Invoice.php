<?php namespace Neonbug\FiscalVerification\Invoice;

/**
 * Base class for invoices.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
abstract class Invoice
{
    /**
     * Unique message id (should be different every time)
     * @var Guid
     */
    public $message_id;
    
    /**
     * Number of the invoice (sequence number of the invoice)
     * @var string
     */
    public $invoice_number;
    
    /**
     * Mark of business premises
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    //public $reference_invoice_invoice_number;
    
    /**
     * Mark of the electronic device
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    //public $reference_invoice_business_premise_id;
    
    /**
     * Number of the invoice (sequence number of the invoice)
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var string
     */
    //public $reference_invoice_electronic_device_id;
    
    /**
     * Date and time of issuing the invoice (unix timestamp)
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    //public $reference_invoice_issue_date_time;
    
    /**
     * Mark of business premises
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    //public $reference_sales_book_invoice_number;
    
    /**
     * Mark of the electronic device
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    //public $reference_sales_book_business_premise_id;
    
    /**
     * Number of the invoice (sequence number of the invoice)
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var string
     */
    //public $reference_sales_book_electronic_device_id;
    
    /**
     * Date and time of issuing the invoice (unix timestamp)
     *     The number of the original invoice is entered in cases
     *     of subsequent changes of data on the original invoice
     *     if the original invoice has been issued via the
     *     electronic device.
     * @var int
     */
    //public $reference_sales_book_issue_date_time;
    
    /**
     * Value of the invoice (with VAT and discounts)
     * @var float
     */
    public $invoice_amount;
    
    /**
     * Tax number of the person liable
     * @var int
     */
    public $tax_number;
    
    /**
     * Value for payment
     * @var float
     */
    public $payment_amount;
    
    /**
     * Amount of refunds
     * @var float
     */
    public $returns_amount;
    
    /**
     * Array of TaxesPerSeller objects
     * @var array
     */
    public $taxes_per_seller = array();
    
    /**
     * Tax number or identification mark for VAT purposes of the buyer
     * @var string
     */
    public $customer_vat_number;
    
    /**
     * Potential other marks are entered, which explain in detail the records in
     *     connection with the content of invoices issued and their changes.
     * @var string
     */
    public $special_notes;
    
    /**
     * Create a new Invoice instance
     *
     * @param  Guid    $message_id      Unique message id (should be different every time)
     * @param  string  $invoice_number  Number of the invoice (sequence number of the invoice)
     * @param  float   $invoice_amount  Value of the invoice (with VAT and discounts)
     * @param  float   $payment_amount  Value for payment
     * @param  int     $tax_number      Tax number of the person liable
     */
    public function __construct(
        $message_id,
        $invoice_number,
        $invoice_amount,
        $payment_amount,
        $tax_number
    ) {
        $this->message_id     = $message_id;
        $this->invoice_number = $invoice_number;
        $this->invoice_amount = $invoice_amount;
        $this->payment_amount = $payment_amount;
        $this->tax_number     = $tax_number;
    }
}
