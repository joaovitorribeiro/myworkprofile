<?php

namespace App\Helpers;

/**
 * Helper para criptografia das informações do autor
 * 
 * Centraliza todas as operações criptográficas relacionadas
 * à proteção dos dados do autor do projeto.
 */
class AuthorCrypto
{
    /**
     * Algoritmo de criptografia utilizado
     */
    private const CIPHER = 'AES-256-CBC';
    
    /**
     * Tamanho do IV em bytes
     */
    private const IV_LENGTH = 16;
    
    /**
     * Obter chave de criptografia baseada no APP_KEY
     */
    private static function getEncryptionKey(): string
    {
        $appKey = env('APP_KEY', 'MyWorkProfile-default-key');
        return hash('sha256', $appKey, true);
    }
    
    /**
     * Criptografar dados
     */
    public static function encrypt(array $data): string
    {
        $key = self::getEncryptionKey();
        $iv = random_bytes(self::IV_LENGTH);
        
        $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
        $encrypted = openssl_encrypt($jsonData, self::CIPHER, $key, 0, $iv);
        
        if ($encrypted === false) {
            throw new \RuntimeException('Falha na criptografia dos dados');
        }
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Descriptografar dados
     */
    public static function decrypt(string $encryptedData): array
    {
        $key = self::getEncryptionKey();
        $data = base64_decode($encryptedData);
        
        if ($data === false || strlen($data) < self::IV_LENGTH) {
            throw new \RuntimeException('Dados criptografados inválidos');
        }
        
        $iv = substr($data, 0, self::IV_LENGTH);
        $encrypted = substr($data, self::IV_LENGTH);
        
        $decrypted = openssl_decrypt($encrypted, self::CIPHER, $key, 0, $iv);
        
        if ($decrypted === false) {
            throw new \RuntimeException('Falha na descriptografia dos dados');
        }
        
        $result = json_decode($decrypted, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Dados descriptografados inválidos');
        }
        
        return $result;
    }
    
    /**
     * Verificar integridade dos dados
     */
    public static function verifyIntegrity(array $data): bool
    {
        $storedHash = config('author.integrity_hash');
        $currentHash = hash('sha256', serialize($data));
        
        return hash_equals($storedHash, $currentHash);
    }
    
    /**
     * Obter dados do autor descriptografados
     */
    public static function getAuthorData(): ?array
    {
        try {
            $encryptedData = config('author.encrypted_author');
            
            if (!$encryptedData) {
                return null;
            }
            
            return self::decrypt($encryptedData);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao descriptografar dados do autor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    
    /**
     * Validar estrutura dos dados do autor
     */
    public static function validateAuthorData(array $data): bool
    {
        $requiredFields = ['name', 'email', 'github', 'created_at', 'project_name', 'version'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Verificar se o projeto está correto
        if ($data['project_name'] !== 'MyWorkProfile') {
            return false;
        }
        
        return true;
    }
}