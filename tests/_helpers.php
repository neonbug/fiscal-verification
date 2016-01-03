<?php
function validateAgainstSchema($schema_name, $data)
{
    $retriever = new JsonSchema\Uri\UriRetriever();
    $schema = $retriever->retrieve('file://' . __DIR__ . '/assets/' . $schema_name);
    
    $ref_resolver = new JsonSchema\RefResolver(new JsonSchema\Uri\UriRetriever());
    $ref_resolver->resolve($schema);
 
    $validator = new JsonSchema\Validator();
    $validator->check($data, $schema);
    
    return array('valid' => $validator->isValid(), 'errors' => $validator->getErrors());
}

function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64UrlDecode($data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}
