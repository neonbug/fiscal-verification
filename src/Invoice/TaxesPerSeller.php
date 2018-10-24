<?php namespace Neonbug\FiscalVerification\Invoice;

/**
 * Represents taxes in an invoice.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
class TaxesPerSeller
{

    /**
     * VAT
     * An array with all taxes
     *     Example:
     *     array(
     *         array(
     *             'TaxAmount' => 5.08,
     *             'TaxRate' => 22.0,
     *             'TaxableAmount' => 23.14
     *         ),
     *         array(
     *             'TaxAmount' => 3.33,
     *             'TaxRate' => 9.5,
     *             'TaxableAmount' => 35.14
     *         )
     *     )
     * @var Array
     */
    public $vat = null;

    /**
     * Tax number of the taxpayer
     * @var string
     */
    public $seller_tax_number;

    /**
     * Flat-rate compensation
     * An array with all rates
     *     Example:
     *     array(
     *         array(
     *             'FlatRateRate' => 10.05,
     *             'FlatRateTaxableAmount' => 100.00,
     *             'FlatRateAmount' => 15.00
     *         )
     *     )
     * @var Array
     */
    public $flat_rate_compensation;

    /**
     * Other taxes/duties
     * @var float
     */
    public $other_taxes_amount;

    /**
     * Value of exempt supplies
     * @var float
     */
    public $exempt_vat_taxable_amount;

    /**
     * Value of supplies on the basis of Article 76.a of the Value Added Tax Act
     * @var float
     */
    public $reverse_vat_taxable_amount;

    /**
     * Value of non-taxable supplies
     * @var float
     */
    public $nontaxable_amount;

    /**
     * Value of supplies, which refers to special arrangements
     * @var float
     */
    public $special_tax_rules_amount;

    /**
     * Create a new TaxesPerSeller instance
     */
    public function __construct()
    {
    }
}
