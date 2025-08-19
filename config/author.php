<?php

/**
 * Configuração do Autor do Projeto
 * 
 * Este arquivo contém informações criptografadas sobre o autor original do projeto.
 * NÃO DEVE SER MODIFICADO EM PRODUÇÃO.
 * 
 * @author [PROTECTED]
 * @created 2025-01-15
 * @license Proprietary
 */

// Chave de criptografia baseada no APP_KEY
$encryptionKey = hash('sha256', env('APP_KEY', 'MyWorkProfile-default-key'), true);

// Dados originais
$originalAuthorData = [
    'name' => 'João Vitor Ribeiro Tim',
    'email' => 'joao@MyWorkProfile.com',
    'github' => 'joaovitorribeiro',
    'created_at' => '2025-01-15',
    'project_name' => 'MyWorkProfile',
    'version' => '1.0.0',
];

// Função inline para criptografar
$encryptData = function($data, $key) {
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt(json_encode($data), 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
};

return [
    /*
    |--------------------------------------------------------------------------
    | Informações do Autor Original (Criptografadas)
    |--------------------------------------------------------------------------
    |
    | Estas informações identificam o autor original do projeto MyWorkProfile.
    | Dados são criptografados com AES-256-CBC para proteção adicional.
    |
    */
    
    'encrypted_author' => $encryptData($originalAuthorData, $encryptionKey),
    
    /*
    |--------------------------------------------------------------------------
    | Hash de Verificação
    |--------------------------------------------------------------------------
    |
    | Hash SHA256 das informações do autor para verificação de integridade.
    | Qualquer alteração nas informações acima invalidará este hash.
    |
    */
    
    'integrity_hash' => hash('sha256', serialize($originalAuthorData)),
    
    /*
    |--------------------------------------------------------------------------
    | Proteção contra Cópia
    |--------------------------------------------------------------------------
    |
    | Configurações para detectar e prevenir cópias não autorizadas.
    |
    */
    
    'protection' => [
        'check_integrity' => true,
        'log_violations' => true,
        'block_on_violation' => env('APP_ENV') === 'production',
    ],
];