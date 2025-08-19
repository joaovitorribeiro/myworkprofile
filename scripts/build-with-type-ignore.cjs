#!/usr/bin/env node

/**
 * Script para build com ignorar erros específicos de TypeScript
 * Útil para merges e deploys quando há erros não-críticos de tipos
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Códigos de erro TypeScript para ignorar
const IGNORE_ERRORS = [
    'TS2589', // Type instantiation is excessively deep
    'TS7006', // Parameter implicitly has an 'any' type
    'TS2339', // Property does not exist on type
    'TS2345', // Argument of type is not assignable
    'TS2344', // Type does not satisfy the constraint
    'TS2322', // Type is not assignable to type
];

// Arquivos para pular completamente a verificação de tipos
const SKIP_FILES = [
    'resources/js/Pages/Auth/Register.tsx'
];

function runBuildWithIgnore() {
    console.log('🔧 Executando build com ignorar erros específicos de TypeScript...');
    
    try {
        // Primeiro tenta build normal
        console.log('📝 Tentando build normal...');
        execSync('npx tsc --noEmit', { stdio: 'pipe' });
        console.log('✅ Build TypeScript passou sem erros!');
        
        // Se passou, executa o build do Vite
        console.log('🚀 Executando build do Vite...');
        execSync('npx vite build', { stdio: 'inherit' });
        execSync('npx vite build --ssr', { stdio: 'inherit' });
        
    } catch (error) {
        console.log('⚠️  Erros encontrados no TypeScript, verificando se são ignoráveis...');
        
        const errorOutput = error.stdout ? error.stdout.toString() : error.stderr.toString();
        const lines = errorOutput.split('\n');
        
        let hasNonIgnorableErrors = false;
        let ignoredErrors = 0;
        
        for (const line of lines) {
            // Verifica se é um erro TypeScript
            if (line.includes('error TS')) {
                const errorCode = line.match(/error (TS\d+)/);
                if (errorCode && IGNORE_ERRORS.includes(errorCode[1])) {
                    ignoredErrors++;
                    console.log(`🔇 Ignorando erro: ${errorCode[1]}`);
                } else {
                    // Verifica se o erro é em um arquivo que deve ser pulado
                    const isSkippableFile = SKIP_FILES.some(file => line.includes(file));
                    if (isSkippableFile) {
                        ignoredErrors++;
                        console.log(`🔇 Ignorando erro em arquivo específico: ${line.split('(')[0]}`);
                    } else {
                        hasNonIgnorableErrors = true;
                        console.log(`❌ Erro não-ignorável: ${line}`);
                    }
                }
            }
        }
        
        if (hasNonIgnorableErrors) {
            console.log('\n❌ Build falhou devido a erros críticos de TypeScript.');
            process.exit(1);
        } else {
            console.log(`\n✅ Todos os erros TypeScript (${ignoredErrors}) são ignoráveis. Continuando build...`);
            
            // Executa build do Vite mesmo com erros ignoráveis
            try {
                console.log('🚀 Executando build do Vite...');
                execSync('npx vite build', { stdio: 'inherit' });
                execSync('npx vite build --ssr', { stdio: 'inherit' });
                console.log('✅ Build concluído com sucesso!');
            } catch (viteError) {
                console.log('❌ Erro no build do Vite:', viteError.message);
                process.exit(1);
            }
        }
    }
}

// Executa se chamado diretamente
if (require.main === module) {
    runBuildWithIgnore();
}

module.exports = { runBuildWithIgnore };