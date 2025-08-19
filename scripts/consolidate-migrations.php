#!/usr/bin/env php
<?php

/**
 * Script para Consolidar Migrações Duplicadas
 * 
 * Este script identifica e resolve conflitos nas migrações de banco de dados,
 * especialmente aqueles relacionados à tabela users.
 */

require_once __DIR__ . '/../vendor/autoload.php';

class MigrationConsolidator
{
    private $migrationsPath;
    private $backupPath;
    private $conflicts = [];
    private $dryRun = false;

    public function __construct($dryRun = false)
    {
        $this->migrationsPath = __DIR__ . '/../database/migrations';
        $this->backupPath = __DIR__ . '/migration-backups';
        $this->dryRun = $dryRun;
        
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    public function analyze()
    {
        echo "🔍 Analisando migrações para conflitos...\n\n";
        
        $this->findUserTableConflicts();
        $this->generateReport();
        
        return $this->conflicts;
    }

    private function findUserTableConflicts()
    {
        $userMigrations = [
            '2025_01_15_000016_add_social_fields_to_users_table.php',
            '2025_08_11_213313_add_user_profile_fields.php',
            '2025_08_11_214759_add_sobrenome_idade_to_users_table.php',
            '2025_08_12_001323_add_data_nascimento_to_users_table.php',
            '2025_08_12_024346_add_social_counts_to_users_table.php',
            '2025_08_13_000136_add_location_fields_to_users_table.php',
            '2025_08_15_114542_add_filter_fields_to_users_table.php'
        ];

        $fieldsMap = [];
        
        foreach ($userMigrations as $migration) {
            $filePath = $this->migrationsPath . '/' . $migration;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $fields = $this->extractFields($content);
                
                foreach ($fields as $field) {
                    if (!isset($fieldsMap[$field])) {
                        $fieldsMap[$field] = [];
                    }
                    $fieldsMap[$field][] = $migration;
                }
            }
        }

        // Identificar conflitos
        foreach ($fieldsMap as $field => $migrations) {
            if (count($migrations) > 1) {
                $this->conflicts[] = [
                    'type' => 'duplicate_field',
                    'field' => $field,
                    'migrations' => $migrations,
                    'severity' => $this->getSeverity($field, $migrations)
                ];
            }
        }

        // Conflitos semânticos específicos
        $this->addSemanticConflicts();
    }

    private function extractFields($content)
    {
        $fields = [];
        
        // Regex para capturar definições de campos
        preg_match_all('/\$table->\w+\([\'"]([\w_]+)[\'"]/', $content, $matches);
        
        if (isset($matches[1])) {
            $fields = array_unique($matches[1]);
        }
        
        return $fields;
    }

    private function addSemanticConflicts()
    {
        // Conflito: data_nascimento vs birth_date
        $this->conflicts[] = [
            'type' => 'semantic_conflict',
            'description' => 'Campos data_nascimento e birth_date representam a mesma informação',
            'fields' => ['data_nascimento', 'birth_date'],
            'migrations' => [
                '2025_08_12_001323_add_data_nascimento_to_users_table.php',
                '2025_01_15_000016_add_social_fields_to_users_table.php'
            ],
            'severity' => 'high',
            'recommendation' => 'Padronizar para birth_date e migrar dados'
        ];

        // Conflito: posts_count vs publications_count
        $this->conflicts[] = [
            'type' => 'semantic_conflict',
            'description' => 'Campos posts_count e publications_count podem representar a mesma informação',
            'fields' => ['posts_count', 'publications_count'],
            'migrations' => [
                '2025_01_15_000016_add_social_fields_to_users_table.php',
                '2025_08_12_024346_add_social_counts_to_users_table.php'
            ],
            'severity' => 'medium',
            'recommendation' => 'Verificar uso e consolidar se necessário'
        ];
    }

    private function getSeverity($field, $migrations)
    {
        $criticalFields = ['bio', 'avatar', 'followers_count', 'following_count'];
        
        if (in_array($field, $criticalFields)) {
            return 'critical';
        }
        
        if (count($migrations) > 2) {
            return 'high';
        }
        
        return 'medium';
    }

    private function generateReport()
    {
        echo "📊 RELATÓRIO DE CONFLITOS\n";
        echo str_repeat("=", 50) . "\n\n";
        
        if (empty($this->conflicts)) {
            echo "✅ Nenhum conflito encontrado!\n";
            return;
        }
        
        $severityCount = ['critical' => 0, 'high' => 0, 'medium' => 0];
        
        foreach ($this->conflicts as $conflict) {
            $severity = $conflict['severity'] ?? 'medium';
            $severityCount[$severity]++;
            
            $icon = $this->getSeverityIcon($severity);
            echo "{$icon} {$conflict['type']}\n";
            
            if (isset($conflict['field'])) {
                echo "   Campo: {$conflict['field']}\n";
            }
            
            if (isset($conflict['description'])) {
                echo "   Descrição: {$conflict['description']}\n";
            }
            
            echo "   Migrações afetadas:\n";
            foreach ($conflict['migrations'] as $migration) {
                echo "     - {$migration}\n";
            }
            
            if (isset($conflict['recommendation'])) {
                echo "   💡 Recomendação: {$conflict['recommendation']}\n";
            }
            
            echo "\n";
        }
        
        echo "📈 RESUMO:\n";
        echo "   🔴 Críticos: {$severityCount['critical']}\n";
        echo "   🟡 Altos: {$severityCount['high']}\n";
        echo "   🟢 Médios: {$severityCount['medium']}\n";
        echo "   📊 Total: " . array_sum($severityCount) . "\n\n";
    }

    private function getSeverityIcon($severity)
    {
        switch ($severity) {
            case 'critical': return '🔴';
            case 'high': return '🟡';
            case 'medium': return '🟢';
            default: return 'ℹ️';
        }
    }

    public function createConsolidationMigration()
    {
        if ($this->dryRun) {
            echo "🔍 [DRY RUN] Simulando criação de migração de consolidação...\n";
            return;
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_consolidate_user_table_duplicates.php";
        $filepath = $this->migrationsPath . '/' . $filename;

        $migrationContent = $this->generateConsolidationMigrationContent();
        
        file_put_contents($filepath, $migrationContent);
        
        echo "✅ Migração de consolidação criada: {$filename}\n";
        
        return $filepath;
    }

    private function generateConsolidationMigrationContent()
    {
        $timestamp = date('Y_m_d_His');
        
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migração consolida campos duplicados na tabela users
     * e resolve conflitos identificados pelo sistema de detecção.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint \$table) {
            // 1. Resolver conflito data_nascimento vs birth_date
            if (Schema::hasColumn('users', 'data_nascimento') && Schema::hasColumn('users', 'birth_date')) {
                // Migrar dados de data_nascimento para birth_date se birth_date estiver vazio
                DB::statement('
                    UPDATE users 
                    SET birth_date = data_nascimento 
                    WHERE birth_date IS NULL AND data_nascimento IS NOT NULL
                ');
                
                // Remover coluna duplicada
                \$table->dropColumn('data_nascimento');
            } elseif (Schema::hasColumn('users', 'data_nascimento')) {
                // Renomear data_nascimento para birth_date
                \$table->renameColumn('data_nascimento', 'birth_date');
            }
            
            // 2. Resolver conflito posts_count vs publications_count
            if (Schema::hasColumn('users', 'publications_count') && Schema::hasColumn('users', 'posts_count')) {
                // Migrar dados se necessário
                DB::statement('
                    UPDATE users 
                    SET posts_count = publications_count 
                    WHERE posts_count = 0 AND publications_count > 0
                ');
                
                // Remover coluna duplicada
                \$table->dropColumn('publications_count');
            }
            
            // 3. Garantir que campos essenciais existam com verificações
            if (!Schema::hasColumn('users', 'bio')) {
                \$table->text('bio')->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('users', 'avatar')) {
                \$table->string('avatar')->nullable()->after('bio');
            }
            
            // 4. Padronizar contadores sociais
            if (!Schema::hasColumn('users', 'followers_count')) {
                \$table->unsignedInteger('followers_count')->default(0);
            }
            
            if (!Schema::hasColumn('users', 'following_count')) {
                \$table->unsignedInteger('following_count')->default(0);
            }
        });
        
        // Log da consolidação
        \Log::info('Migração de consolidação executada', [
            'migration' => '{$timestamp}_consolidate_user_table_duplicates',
            'conflicts_resolved' => [
                'data_nascimento_vs_birth_date',
                'posts_count_vs_publications_count',
                'duplicate_bio_avatar_fields'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nota: Rollback complexo devido à natureza da consolidação
        // Recomenda-se backup antes da execução
        
        Schema::table('users', function (Blueprint \$table) {
            // Reverter apenas se seguro
            if (Schema::hasColumn('users', 'birth_date')) {
                \$table->date('data_nascimento')->nullable();
                
                DB::statement('
                    UPDATE users 
                    SET data_nascimento = birth_date 
                    WHERE data_nascimento IS NULL AND birth_date IS NOT NULL
                ');
            }
        });
    }
};
PHP;
    }

    public function backupMigrations()
    {
        $conflictedMigrations = [];
        
        foreach ($this->conflicts as $conflict) {
            if (isset($conflict['migrations'])) {
                $conflictedMigrations = array_merge($conflictedMigrations, $conflict['migrations']);
            }
        }
        
        $conflictedMigrations = array_unique($conflictedMigrations);
        
        foreach ($conflictedMigrations as $migration) {
            $source = $this->migrationsPath . '/' . $migration;
            $backup = $this->backupPath . '/' . date('Y-m-d_H-i-s') . '_' . $migration;
            
            if (file_exists($source)) {
                copy($source, $backup);
                echo "📁 Backup criado: {$backup}\n";
            }
        }
    }
}

// Execução do script
function main($argv)
{
    $dryRun = in_array('--dry-run', $argv);
    $help = in_array('--help', $argv) || in_array('-h', $argv);
    
    if ($help) {
        echo "\n🔧 Consolidador de Migrações\n";
        echo "============================\n\n";
        echo "Uso: php consolidate-migrations.php [opções]\n\n";
        echo "Opções:\n";
        echo "  --dry-run    Simular operações sem modificar arquivos\n";
        echo "  --help, -h   Mostrar esta ajuda\n\n";
        return;
    }
    
    echo "🚀 Iniciando consolidação de migrações...\n\n";
    
    $consolidator = new MigrationConsolidator($dryRun);
    
    // Analisar conflitos
    $conflicts = $consolidator->analyze();
    
    if (!empty($conflicts)) {
        echo "⚠️  Conflitos encontrados! Criando backups...\n";
        $consolidator->backupMigrations();
        
        echo "\n🔧 Criando migração de consolidação...\n";
        $consolidator->createConsolidationMigration();
        
        echo "\n✅ Processo concluído!\n";
        echo "\n📋 Próximos passos:\n";
        echo "   1. Revisar a migração de consolidação criada\n";
        echo "   2. Testar em ambiente de desenvolvimento\n";
        echo "   3. Executar: php artisan migrate\n";
        echo "   4. Verificar integridade dos dados\n\n";
    } else {
        echo "✅ Nenhum conflito encontrado nas migrações!\n";
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    main($argv ?? []);
}