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
     * Array of reference invoices
     * @var array
     */
    public $reference_invoices = array();

    /**
     * Array of reference sales books
     * @var array
     */
    public $reference_sales_books = array();

    /**
     * Date format that will be used for this reference
     * @var string
     */
    public $date_time_format;

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

    /**
     * Set special notes
     *
     * @param string $notes
     */
    public function setSpecialNotes($notes)
    {
        $this->special_notes = $notes;
    }

    /**
     * Add reference invoice to current invoice
     *
     * @param ReferenceInvoice $invoice
     */
    public function addReferenceInvoice(ReferenceInvoice $invoice)
    {
        array_push($this->reference_invoices, $invoice);
    }

    /**
     * Get all the reference invoices that were added to this invoice
     *
     * @return array
     */
    public function getReferenceInvoices()
    {
        return $this->getArrayRepresentation($this->reference_invoices);
    }

    /**
     * Set reference salaes book to current invoice
     *
     * @param ReferenceSalesBook $book
     */
    public function addReferenceSalesBook(ReferenceSalesBook $book)
    {
        array_push($this->reference_sales_books, $book);
    }

    /**
     * Get all the reference books that were added to this invoice
     *
     * @return array
     */
    public function getReferenceSalesBooks()
    {
        return $this->getArrayRepresentation($this->reference_sales_books);
    }

    /**
     * Get array representation of our reference items
     *
     * @param  array $data
     * @return array
     */
    private function getArrayRepresentation($data)
    {
        return array_map(function (ReferenceInterface $item) {
            $item->setDateTimeFormat($this->getDateTimeFormat());

            return $item->toArray();
        }, $data);
    }
}
