<?php namespace Neonbug\FiscalVerification\Invoice;

/**
 * Represents an electronic invoice.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
class ElectronicInvoice extends Invoice
{
    /**
     * Mark of business premises
     * @var int
     */
    public $business_premise_id;

    /**
     * Mark of the electronic device
     * @var int
     */
    public $electronic_device_id;

    /**
     * Date and time of issuing the invoice (unix timestamp)
     * @var int
     */
    public $issue_date_time;

    /**
     * Tax number of the person (operator) at the electronic device
     * @var int
     */
    public $operator_tax_number;

    /**
     * Protective mark of the invoice issuer
     * @var string
     */
    public $protected_id;

    /**
     * Method for assigning the invoice
     *     Can be either C or B:
     *     C (centrally at the level of business premises)
     *     B (per individual electronic device (cash register))
     * @var string
     */
    public $numbering_structure;

    /**
     * You enter "true" if the individual (operator), who issues the invoice with
     *     the usage of the electronic device, has no Slovene tax number,
     *     otherwise "false" (1 – true, 0 – false)
     * @var boolean
     */
    public $foreign_operator;

    /**
     * Subsequently submitted invoices are invoices, which have been issued without
     *     the unique identification invoice mark – EOR (e.g. due to disconnections
     *     of electronic connections with the tax authority). If the invoice is
     *     subsequently submitted to the tax authority, "true" is entered,
     *     otherwise "false" (1 – true, 0 – false).
     * @var boolean
     */
    public $subsequent_submit;

    /**
     * Create a new Invoice instance
     *
     * @param  Guid    $message_id            Unique message id (should be different every time)
     * @param  string  $invoice_number        Number of the invoice (sequence number of the invoice)
     * @param  float   $invoice_amount        Value of the invoice (with VAT and discounts)
     * @param  float   $payment_amount        Value for payment
     * @param  int     $tax_number            Tax number of the person liable
     * @param  int     $business_premise_id   Mark of business premises
     * @param  int     $electronic_device_id  Mark of the electronic device
     * @param  int     $issue_date_time       Date and time of issuing the invoice (unix timestamp)
     * @param  int     $operator_tax_number   Tax number of the person (operator) at the electronic device
     * @param  string  $protected_id          Protective mark of the invoice issuer
     * @param  string  $numbering_structure   Method for assigning the invoice
     *                                        Can be either C or B:
     *                                        C (centrally at the level of business premises)
     *                                        B (per individual electronic device (cash register))
     */
    public function __construct(
        $message_id,
        $invoice_number,
        $invoice_amount,
        $payment_amount,
        $tax_number,
        $business_premise_id,
        $electronic_device_id,
        $issue_date_time,
        $operator_tax_number,
        $protected_id,
        $numbering_structure
    ) {
        parent::__construct(
            $message_id,
            $invoice_number,
            $invoice_amount,
            $payment_amount,
            $tax_number
        );

        $this->business_premise_id  = $business_premise_id;
        $this->electronic_device_id = $electronic_device_id;
        $this->issue_date_time      = $issue_date_time;
        $this->operator_tax_number  = $operator_tax_number;
        $this->protected_id         = $protected_id;
        $this->numbering_structure  = $numbering_structure;
    }
}
