<?php
return array(
    /**
     * Full path to PEM-encoded client certificate
     * Example: __DIR__ . '/assets/test_certificate.pem'
     */
    'client_key_filename' => null,

    /**
     * Password for unlocking client password
     * Example: 'password'
     */
    'client_key_password' => null,

    /**
     * Full path to PEM-encoded public key
     * Example: __DIR__ . '/assets/sitest-ca.pem'
     */
    'ca_public_key_filename' => __DIR__ . '/assets/sitest-ca.pem',

    /**
     * Base URL for FURS' JSON endpoint
     * Example: 'https://blagajne-test.fu.gov.si:9002'
     */
    'base_url' => 'https://blagajne-test.fu.gov.si:9002',

    /**
     * Info from client certificate
     */
    'client_key_info' => array(
        /**
         * Certificate serial number
         * Should be a string
         * Example: '3943415972554603486'
         */
        'serial' => null,

        /**
         * Complete issuer name
         * Example: 'C=SI, O=state-institutions, CN=Tax CA Test'
         */
        'issuer_name' => null,

        /**
         * Complete subject name
         * Example: '/C=SI/O=state-institutions/OU=DavPotRacTEST/OU=10359320/serialNumber=1/CN=TESTNO PODJETJE 838'
         */
        'subject_name' => null
    ),

    /**
     * Same tax number as in client certificate
     * Must be a number, not a string
     * Example: 12345678
     */
    'tax_number' => null,

    /**
     * Timezone we need to set for generating proper timestamps
     * you need to set Timezone in which you live
     */
    'timezone' => 'Europe/Berlin'
);
