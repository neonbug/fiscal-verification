<?php

use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

function validateAgainstSchema($schema_name, $data)
{
    $schemaStorage = new SchemaStorage();

    $schemaStorage->addSchema('file://' . __DIR__ . '/assets/' . $schema_name);

    $jsonValidator = new Validator( new Factory($schemaStorage));

    $jsonValidator->validate($data, $schemaStorage);


    return array('valid' => $jsonValidator->isValid(), 'errors' => $jsonValidator->getErrors());
}

function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64UrlDecode($data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}
