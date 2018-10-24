<?php namespace Neonbug\FiscalVerification;

use Neonbug\FiscalVerification\BusinessPremise\BusinessPremise;
use Neonbug\FiscalVerification\BusinessPremise\ImmovableBusinessPremise;
use Neonbug\FiscalVerification\BusinessPremise\MovableBusinessPremise;
use Neonbug\FiscalVerification\Invoice\Invoice;
use Neonbug\FiscalVerification\Invoice\ElectronicInvoice;
use Neonbug\FiscalVerification\Invoice\SalesBookInvoice;

/**
 * Handles constructing, signing and sending of invoices, premise registrations and other messages to FURS.
 *
 * @author Tadej Kanizar <tadej@ncode.si>
 */
class FiscalVerification
{
    const URL_PATH_ECHO           = '/v1/cash_registers/echo';
    const URL_PATH_INVOICES       = '/v1/cash_registers/invoices';
    const URL_PATH_INVOICES_BATCH = '/v1/cash_registers_batch/invoices';
    const URL_PATH_REGISTER       = '/v1/cash_registers/invoices/register';

    const MESSAGE_HEADER_DATETIME_FORMAT = 'c'; //'Y-m-d\TH:i:sP';

    protected $client_key_filename;
    protected $client_key_password;
    protected $ca_public_key_filename;
    protected $base_url;

    protected $event_emitter;

    /**
     * Create a new FiscalVerification instance
     *
     * @param string $client_key_filename    Client key absolute path (PEM format)
     * @param string $client_key_password    Client key password (for private key)
     * @param string $ca_public_key_filename CA public key absolute path (PEM format)
     * @param string $base_url               Base URL without trailing slash (e.g. https://blagajne-test.fu.gov.si:9002)
     */
    public function __construct(
        $client_key_filename,
        $client_key_password,
        $ca_public_key_filename,
        $base_url
    ) {
        $this->client_key_filename    = $client_key_filename;
        $this->client_key_password    = $client_key_password;
        $this->ca_public_key_filename = $ca_public_key_filename;
        $this->base_url               = $base_url;
    }

    /**
     * Add an external event emitter
     * @param \Evenement\EventEmitterInterface $event_emitter Event emmiter
     */
    public function setEventEmitter(\Evenement\EventEmitterInterface $event_emitter)
    {
        $this->event_emitter = $event_emitter;
    }

    /**
     * Generate invoice structure from provided arguments
     *
     * @param  Invoice $invoice  Either ElectronicInvoice or SalesBookInvoice
     * @param  boolean $encode   If true, returns JSON encoded string; otherwise, returns Array)
     *
     * @return Array|string      JSON encoded string or an array (based on $encode parameter)
     */
    public function generateInvoice(
        Invoice $invoice,
        $encode = true
    ) {
        // set date format for all invoice related stuff
        $invoice->setDateTimeFormat(self::MESSAGE_HEADER_DATETIME_FORMAT);

        $data = array(
            'InvoiceRequest' => array(
                'Header' => array(
                    'MessageID' => $invoice->message_id,
                    'DateTime'  => date(self::MESSAGE_HEADER_DATETIME_FORMAT)
                )
            )
        );

        $key = '';
        if ($invoice instanceof ElectronicInvoice) {
            $key = 'Invoice';

            $data['InvoiceRequest'][$key] = array(
                'InvoiceIdentifier' => array(
                    'InvoiceNumber'      => $invoice->invoice_number,
                    'BusinessPremiseID'  => $invoice->business_premise_id,
                    'ElectronicDeviceID' => $invoice->electronic_device_id
                ),
                'TaxNumber'          => $invoice->tax_number,
                'ProtectedID'        => $invoice->protected_id,
                'PaymentAmount'      => $this->formatNumber($invoice->payment_amount),
                'InvoiceAmount'      => $this->formatNumber($invoice->invoice_amount),
                'NumberingStructure' => $invoice->numbering_structure,
                'OperatorTaxNumber'  => $invoice->operator_tax_number,
                'IssueDateTime'      => date(self::MESSAGE_HEADER_DATETIME_FORMAT, $invoice->issue_date_time),
                'TaxesPerSeller'     => array()
            );

            if ($invoice->foreign_operator != null) {
                $data['InvoiceRequest'][$key]['ForeignOperator'] = $invoice->foreign_operator;
            }

            if ($invoice->subsequent_submit != null) {
                $data['InvoiceRequest'][$key]['SubsequentSubmit'] = $invoice->subsequent_submit;
            }
        } elseif ($invoice instanceof SalesBookInvoice) {
            $key = 'SalesBookInvoice';

            $data['InvoiceRequest'][$key] = array(
                'SalesBookIdentifier' => array(
                    'InvoiceNumber' => $invoice->invoice_number,
                    'SetNumber'     => $invoice->set_number,
                    'SerialNumber'  => $invoice->serial_number
                ),
                'TaxNumber'         => $invoice->tax_number,
                'ProtectedID'       => $invoice->protected_id,
                'PaymentAmount'     => $this->formatNumber($invoice->payment_amount),
                'InvoiceAmount'     => $this->formatNumber($invoice->invoice_amount),
                'BusinessPremiseID' => $invoice->business_premise_id,
                'OperatorTaxNumber' => $invoice->operator_tax_number,
                'IssueDate'         => date(self::MESSAGE_HEADER_DATETIME_FORMAT, $invoice->issue_date),
                'TaxesPerSeller'    => array()
            );
        }

        if ($invoice->customer_vat_number != null) {
            $data['InvoiceRequest'][$key]['CustomerVATNumber'] = $invoice->customer_vat_number;
        }

        if ($invoice->returns_amount != null) {
            $data['InvoiceRequest'][$key]['ReturnsAmount'] = $invoice->returns_amount;
        }

        if ($invoice->special_notes != null) {
            $data['InvoiceRequest'][$key]['SpecialNotes'] = $invoice->special_notes;
        }

        $taxes_per_seller = array();
        foreach ($invoice->taxes_per_seller as $taxes_per_seller_item) {
            $taxes_per_seller_arr = array();

            if ($taxes_per_seller_item->seller_tax_number != null) {
                $taxes_per_seller_arr['SellerTaxNumber'] = $taxes_per_seller_item->seller_tax_number;
            }

            if ($taxes_per_seller_item->other_taxes_amount != null) {
                $taxes_per_seller_arr['OtherTaxesAmount'] = $taxes_per_seller_item->other_taxes_amount;
            }

            if ($taxes_per_seller_item->exempt_vat_taxable_amount != null) {
                $taxes_per_seller_arr['ExemptVATTaxableAmount'] = $taxes_per_seller_item->exempt_vat_taxable_amount;
            }

            if ($taxes_per_seller_item->reverse_vat_taxable_amount != null) {
                $taxes_per_seller_arr['ReverseVATTaxableAmount'] = $taxes_per_seller_item->reverse_vat_taxable_amount;
            }

            if ($taxes_per_seller_item->nontaxable_amount != null) {
                $taxes_per_seller_arr['NontaxableAmount'] = $taxes_per_seller_item->nontaxable_amount;
            }

            if ($taxes_per_seller_item->special_tax_rules_amount != null) {
                $taxes_per_seller_arr['SpecialTaxRulesAmount'] = $taxes_per_seller_item->special_tax_rules_amount;
            }

            if ($taxes_per_seller_item->vat != null) {
                $formatted_arr = array();
                foreach ($taxes_per_seller_item->vat as $tax) {
                    $formatted_arr[] = array(
                        'TaxAmount'     => $this->formatNumber($tax['TaxAmount']),
                        'TaxRate'       => $this->formatNumber($tax['TaxRate']),
                        'TaxableAmount' => $this->formatNumber($tax['TaxableAmount'])
                    );
                }
                $taxes_per_seller_arr['VAT'] = $formatted_arr;
            }

            if ($taxes_per_seller_item->flat_rate_compensation != null) {
                $formatted_arr = array();
                foreach ($taxes_per_seller_item->flat_rate_compensation as $rate) {
                    $formatted_arr[] = array(
                        'FlatRateRate'          => $this->formatNumber($rate['FlatRateRate']),
                        'FlatRateTaxableAmount' => $this->formatNumber($rate['FlatRateTaxableAmount']),
                        'FlatRateAmount'        => $this->formatNumber($rate['FlatRateAmount'])
                    );
                }
                $taxes_per_seller_arr['FlatRateCompensation'] = $formatted_arr;
            }

            $taxes_per_seller[] = $taxes_per_seller_arr;
        }

        $data['InvoiceRequest'][$key]['TaxesPerSeller'] = $taxes_per_seller;

        // if invoice was referenced
        $reference_invoices = $invoice->getReferenceInvoices() ;
        if (! empty($reference_invoices)) {
            $data['InvoiceRequest'][$key]['ReferenceInvoice'] = $reference_invoices;
        }

        // if sales book was referenced
        $reference_sales_books = $invoice->getReferenceSalesBooks() ;
        if (! empty($reference_sales_books)) {
            $data['InvoiceRequest'][$key]['ReferenceSalesBook'] = $reference_sales_books;
        }

        return ($encode ? json_encode($data) : $data);
    }

    /**
     * Generate ZOI
     *
     * @param string $tax_number           Tax number of the person liable
     * @param int    $issue_date_time      Date and time of issuing the invoice (unix timestamp)
     * @param string $business_premise_id  Mark of business premises
     * @param string $electronic_device_id Mark of the electronic device
     * @param string $invoice_number       Number of the invoice (sequence number of the invoice)
     * @param float  $invoice_amount       Value of the invoice (with VAT and discounts)
     *
     * @return string                      Generated ZOI
     */
    public function generateZoi(
        $tax_number,
        $issue_date_time,
        $business_premise_id,
        $electronic_device_id,
        $invoice_number,
        $invoice_amount
    ) {
        return $tax_number . date('d.m.Y H:i:s', $issue_date_time) . $invoice_number .
            $business_premise_id . $electronic_device_id . $this->formatNumber($invoice_amount);
    }

    /**
     * Signs ZOI using private key
     *
     * @param string  $zoi    ZOI
     * @param boolean $encode Hash signature or not
     *
     * @return string         Hashed signature or raw (based on $encode parameter)
     */
    public function signZoi($zoi, $encode = true)
    {
        $private_key = $this->getPrivateKey($this->getClientKeyFilename(), $this->getClientKeyPassword());

        $signature = '';
        if (!openssl_sign($zoi, $signature, $private_key, 'sha256WithRSAEncryption')) {
            throw new \Exception('Error signing with OpenSSL');
        }

        $this->releasePrivateKey($private_key);

        return ($encode ? hash('md5', $signature) : $signature);
    }

    /**
     * Render provided information as QR code
     *
     * @param string $signed_zoi      Signed ZOI
     * @param string $tax_number      Tax number of the person liable
     * @param int    $issue_date_time Date and time of issuing the invoice (unix timestamp)
     * @param int    $size            Size of rendered QR (without padding); used for width and height
     * @param int    $padding         Padding, adds to rendered image's width and height;
     *                                used for left/right/top/bottom padding
     *
     * @return GD resource            Rendered QR code as a GD resource
     */
    public function renderQrCodeAsImage($signed_zoi, $tax_number, $issue_date_time, $size = 300, $padding = 10)
    {
        $value = str_pad($this->bigNumberHexToDecimal($signed_zoi), 39, '0', STR_PAD_LEFT) .
            $tax_number .
            date('ymdHis', $issue_date_time);
        $value .= $this->calculateModulo10($value);

        $qrCode = new \Endroid\QrCode\QrCode();
        return $qrCode
            ->setText($value)
            ->setSize($size)
            ->setPadding($padding)
            ->setErrorCorrection(\Endroid\QrCode\QrCode::LEVEL_MEDIUM)
            ->setForegroundColor(array('r' => 0,   'g' => 0,   'b' => 0,   'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->getImage();
    }

    /**
     * Generate header using public certificate
     *
     * @param boolean $encode JSON encode header or not
     *
     * @return string|Array   JSON encoded string or an array (based on $encode parameter)
     */
    public function generateHeader($encode = true)
    {
        // base64 encoded X.509 (.CER)
        $parsed_cert = openssl_x509_parse(file_get_contents($this->getClientKeyFilename()));

        $subject_name  = $parsed_cert['name'];
        $serial_number = $parsed_cert['serialNumber'];

        $issuer_name = implode(', ', array_map(function ($k, $v) {
            return $k . '=' . $v;
        }, array_keys($parsed_cert['issuer']), $parsed_cert['issuer']));

        $header = array(
            'alg'          => 'RS256',
            'subject_name' => $subject_name,
            'issuer_name'  => $issuer_name,
            'serial'       => $serial_number
        );

        return ($encode ? json_encode($header) : $header);
    }

    /**
     * Sign data using private key
     *
     * @param string  $request_header Request header (JSON encoded)
     * @param string  $request        Request (JSON encoded)
     * @param boolean $encode         JSON encode and pack signed request or not
     *
     * @return string                 JSON encoded packaged token or the generated token raw
     *                                (based on $encode parameter)
     */
    public function signRequest($request_header, $request, $encode = true)
    {
        $request_header_encoded = \Base64Url\Base64Url::encode(
            is_array($request_header) ? json_encode($request_header) : $request_header
        );
        $request_encoded        = \Base64Url\Base64Url::encode(
            is_array($request)        ? json_encode($request)        : $request
        );

        $private_key = $this->getPrivateKey($this->getClientKeyFilename(), $this->getClientKeyPassword());

        $data = implode('.', array($request_header_encoded, $request_encoded));

        $signature = '';
        if (!openssl_sign($data, $signature, $private_key, 'SHA256')) {
            throw new \Exception('Error signing with OpenSSL');
        }

        $this->releasePrivateKey($private_key);

        $token = implode('.', array(
            $request_header_encoded,
            $request_encoded,
            \Base64Url\Base64Url::encode($signature)
        ));

        return ($encode ? json_encode(array('token' => $token)) : $token);
    }

    /**
     * Generate register premise structure from provided arguments
     *
     * @param Guid            $message_id          Unique message id (should be different every time)
     * @param string          $tax_number          Tax number of the person liable, who issues invoices
     * @param string          $business_premise_id The mark is entered of business premises, in which the person
     *                                             liable issues invoices in cash operations. The mark may include
     *                                             the following numbers and letters: 0-9, a-z, A-Z
     * @param int|string      $validity_date       The date when data about business premises, which are submitted,
     *                                             become valid (unix timestamp or string formatted as Y-m-d)
     * @param BusinessPremise $business_premise    An instance of either MovableBusinessPremise or
     *                                             ImmovableBusinessPremise
     * @param Array           $software_supplier   The data is entered about the producer or software maintenance
     *                                             provider for issuing invoices.
     *                                             Should be tax number for Slovenian suppliers or full name for
     *                                             foreign suppliers
     * @param string          $special_notes       Optional; any other potential marks are entered,
     *                                             which explain in detail the records in connection
     *                                             with the content of data about business premises
     * @param boolean         $close_premise       Optional; if true, this business premise will be permanently closed
     * @param boolean         $encode              Optional; if true, returns JSON encoded string;
     *                                             otherwise, returns Array)
     *
     * @return Array|string                        JSON encoded string or an array (based on $encode parameter)
     */
    public function generateRegisterPremise(
        $message_id,
        $tax_number,
        $business_premise_id,
        $validity_date,
        BusinessPremise $business_premise,
        array $software_suppliers,
        $special_notes = '',
        $close_premise = false,
        $encode = true
    ) {
        $validity_date_formatted = (is_string($validity_date) ? $validity_date :
            date(self::MESSAGE_HEADER_DATETIME_FORMAT /*'Y-m-d'*/, $validity_date));

        $software_suppliers_formatted = array();
        foreach ($software_suppliers as $software_supplier) {
            $software_supplier_type = preg_match('/^[0-9]{8}$/', $software_supplier) ?
                'TaxNumber' : // only for Slo companies
                'NameForeign'; // only for foreign companies

            $software_suppliers_formatted[][$software_supplier_type] = $software_supplier;
        }

        $data = array(
            'BusinessPremiseRequest' => array(
                'Header' => array(
                    'MessageID' => $message_id,
                    'DateTime'  => date(self::MESSAGE_HEADER_DATETIME_FORMAT)
                ),
                'BusinessPremise' => array(
                    'TaxNumber'         => $tax_number,
                    'BusinessPremiseID' => $business_premise_id, // max 20, chars 0-9, a-z, A-Z
                    'ValidityDate'      => $validity_date_formatted, // YYYY-MM-DD
                    'SoftwareSupplier'  => $software_suppliers_formatted
                )
            )
        );

        if ($business_premise instanceof MovableBusinessPremise) {
            $data['BusinessPremiseRequest']['BusinessPremise']['BPIdentifier']['PremiseType'] =
                $business_premise->premise_type; // movable business premise; one of: A, B, C
        } elseif ($business_premise instanceof ImmovableBusinessPremise) {
            $data['BusinessPremiseRequest']['BusinessPremise']['BPIdentifier'] = array(
                'RealEstateBP' => array( // immovable business premise
                    'PropertyID' => array(
                        'CadastralNumber'       => $business_premise->cadastral_number, // int
                        'BuildingNumber'        => $business_premise->building_number, // int
                        'BuildingSectionNumber' => $business_premise->building_section_number // int
                    ),
                    'Address' => array(
                        'Street'                => $business_premise->street,
                        'HouseNumber'           => $business_premise->house_number,
                        'HouseNumberAdditional' => $business_premise->house_number_additional, // optional
                        'Community'             => $business_premise->community,
                        'City'                  => $business_premise->city,
                        'PostalCode'            => $business_premise->postal_code
                    )
                )
            );
        }

        if ($close_premise === true) {
            $data['BusinessPremiseRequest']['BusinessPremise']['ClosingTag'] = 'Z'; // optional
        }
        if (mb_strlen($special_notes, 'UTF-8') > 0) {
            $data['BusinessPremiseRequest']['BusinessPremise']['SpecialNotes'] = $special_notes; // optional
        }

        return ($encode ? json_encode($data) : $data);
    }

    /**
     * Send an echo message
     *
     * @param string  $message Message to send
     * @param boolean $decode  JSON decode returned value
     *
     * @return Array|string    Decoded return value as an array or the raw string
     */
    public function sendEcho($message, $decode = true)
    {
        $message_formatted = json_encode(array('EchoRequest' => $message));

        $retval = $this->send(
            $this->getBaseUrl() . self::URL_PATH_ECHO,
            $this->getClientKeyFilename(),
            $this->getClientKeyPassword(),
            $this->getCaPublicKeyFilename(),
            $message_formatted
        );

        return ($decode ? json_decode($retval) : $retval);
    }

    /**
     * Send an invoice
     *
     * @param string  $invoice Invoice to send
     * @param boolean $decode  JSON decode returned value
     *
     * @return Array|string    Decoded return value as an array or the raw string
     */
    public function sendInvoice($invoice, $decode = true)
    {
        $invoice_formatted = (is_array($invoice) ? json_encode($invoice) : $invoice);

        $retval = $this->send(
            $this->getBaseUrl() . self::URL_PATH_INVOICES,
            $this->getClientKeyFilename(),
            $this->getClientKeyPassword(),
            $this->getCaPublicKeyFilename(),
            $invoice_formatted
        );

        return ($decode ? $this->parseResponse($retval) : $retval);
    }

    /**
     * Send premise registration
     *
     * @param string  $premise Premise
     * @param boolean $decode  JSON decode returned value
     *
     * @return Array|string    Decoded return value as an array or the raw string
     */
    public function sendPremise($premise, $decode = true)
    {
        $premise_formatted = (is_array($premise) ? json_encode($premise) : $premise);

        $retval = $this->send(
            $this->getBaseUrl() . self::URL_PATH_REGISTER,
            $this->getClientKeyFilename(),
            $this->getClientKeyPassword(),
            $this->getCaPublicKeyFilename(),
            $premise_formatted
        );

        return ($decode ? $this->parseResponse($retval) : $retval);
    }

    // private/protected methods

    protected function getClientKeyFilename()
    {
        return $this->client_key_filename;
    }

    protected function getClientKeyPassword()
    {
        return $this->client_key_password;
    }

    protected function getCaPublicKeyFilename()
    {
        return $this->ca_public_key_filename;
    }

    protected function getBaseUrl()
    {
        return $this->base_url;
    }

    protected function getPrivateKey($client_key_filename, $client_key_password)
    {
        $cert = openssl_pkey_get_private(file_get_contents($client_key_filename), $client_key_password);
        if ($cert === false) {
            throw new \Exception('Error reading certs: ' . openssl_error_string());
        }
        return $cert;
    }

    protected function releasePrivateKey($private_key)
    {
        openssl_pkey_free($private_key);
    }

    // from https://stackoverflow.com/questions/16965915
    protected function bigNumberHexToDecimal($hex)
    {
        $dec = array(0);
        $hexLen = strlen($hex);
        for ($h=0; $h<$hexLen; $h++) {
            $carry = hexdec($hex[$h]);

            for ($i = 0; $i < sizeof($dec); $i++) {
                $val = $dec[$i] * 16 + $carry;
                $dec[$i] = $val % 10;
                $carry = (int) ($val / 10);
            }

            while ($carry > 0) {
                $dec[] = $carry % 10;
                $carry = (int) ($carry / 10);
            }
        }

        return join('', array_reverse($dec));
    }

    // translated from https://github.com/MPrtenjak/SLOTax/blob/master/SharedService/Modulo/Modulo10_Easy.cs
    protected function calculateModulo10($value)
    {
        $sum = 0;
        for ($i=0; $i<strlen($value); $i++) {
            $sum += intval(substr($value, $i, 1));
        }

        return $sum % 10;
    }

    protected function send(
        $url,
        $client_key_filename,
        $client_key_password,
        $ca_public_key_filename,
        $message
    ) {
        $this->emitEvent('fiscal-verification.before-send', $message);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        //curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 0);

        // workaround for double header in response
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($url, $header) {
            return strlen($header);
        });

        // use TLS 1.2 if available; otherwise, use TLS 1.0
        $ssl_version = defined('CURL_SSLVERSION_TLSv1_2') ? CURL_SSLVERSION_TLSv1_2 : 4 /* CURL_SSLVERSION_TLSv1_0 */;
        curl_setopt($ch, CURLOPT_SSLVERSION, $ssl_version);

        // client private certificate
        curl_setopt($ch, CURLOPT_SSLCERT, $client_key_filename);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $client_key_password);

        // server's (or its CA) public certificate
        curl_setopt($ch, CURLOPT_CAINFO, $ca_public_key_filename);

        // set message
        curl_setopt($ch, CURLOPT_POSTFIELDS, (!is_string($message) ? json_encode($message) : $message));

        $response = curl_exec($ch);
        $this->emitEvent('fiscal-verification.after-send', $response);

        if ($response === false) {
            //if (curl_errno($ch) == 35) { //handle "Unknown SSL protocol error in connection to" error
                //let's simply retry, it usually solves this issue
                $response = curl_exec($ch);
                $this->emitEvent('fiscal-verification.after-repeat-send', $response);
            //}

            if ($response === false) {
                $error = new \Exception('Curl error', 1, new \Exception(curl_error($ch), curl_errno($ch)));

                $this->emitEvent('fiscal-verification.after-send-error', $error);

                throw $error;
            }
        }

        curl_close($ch);

        //list($header, $body) = explode("\r\n\r\n", $response, 2);
        $body = $response;

        return $body;
    }

    protected function formatNumber($val)
    {
        return round($val, 2);
    }

    protected function parseResponse($response)
    {
        $obj = json_decode($response);
        if ($obj === false) {
            //TODO throw an exception
            return array('error');
        }

        $arr = explode('.', $obj->token);
        if (sizeof($arr) != 3) {
            //TODO throw an exception
        }

        return array(
            'header'    => \Base64Url\Base64Url::decode($arr[0]),
            'payload'   => \Base64Url\Base64Url::decode($arr[1]),
            'signature' => \Base64Url\Base64Url::decode($arr[2])
        );
    }

    protected function emitEvent($event, $message)
    {
        if ($this->event_emitter == null) {
            return;
        }

        $this->event_emitter->emit($event, array($message));
    }
}
