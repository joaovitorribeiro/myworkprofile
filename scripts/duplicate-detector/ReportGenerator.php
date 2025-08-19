<?php

class ReportGenerator
{
    private $config;
    private $data;
    private $outputDir;

    public function __construct($configPath = null)
    {
        $configPath = $configPath ?: __DIR__ . '/config.json';
        $this->config = json_decode(file_get_contents($configPath), true);
        $this->outputDir = __DIR__ . '/reports';
        
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    public function generateReport($scanData, $format = 'all')
    {
        $this->data = $scanData;
        $timestamp = date('Y-m-d_H-i-s');
        $reports = [];

        echo "📊 Gerando relatórios...\n";

        if ($format === 'all' || $format === 'json') {
            $reports['json'] = $this->generateJsonReport($timestamp);
        }

        if ($format === 'all' || $format === 'html') {
            $reports['html'] = $this->generateHtmlReport($timestamp);
        }

        if ($format === 'all' || $format === 'csv') {
            $reports['csv'] = $this->generateCsvReport($timestamp);
        }

        if ($format === 'all' || $format === 'markdown') {
            $reports['markdown'] = $this->generateMarkdownReport($timestamp);
        }

        echo "✅ Relatórios gerados com sucesso!\n";
        return $reports;
    }

    private function generateJsonReport($timestamp)
    {
        $filename = "duplicate-report-{$timestamp}.json";
        $filepath = $this->outputDir . '/' . $filename;

        $report = [
            'metadata' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '2.0',
                'generator' => 'MyWorkProfile Duplicate Detector',
                'projectType' => $this->config['projectType'] ?? 'unknown'
            ],
            'summary' => $this->generateSummary(),
            'statistics' => $this->generateStatistics(),
            'duplicates' => $this->data['duplicates'] ?? [],
            'processedFiles' => $this->data['processedFiles'] ?? [],
            'recommendations' => $this->generateRecommendations(),
            'performance' => $this->data['performance'] ?? []
        ];

        file_put_contents($filepath, json_encode($report, JSON_PRETTY_PRINT));
        echo "📄 Relatório JSON: {$filename}\n";
        
        return $filepath;
    }

    private function generateHtmlReport($timestamp)
    {
        $filename = "duplicate-report-{$timestamp}.html";
        $filepath = $this->outputDir . '/' . $filename;

        $summary = $this->generateSummary();
        $statistics = $this->generateStatistics();
        $duplicates = $this->data['duplicates'] ?? [];

        $html = $this->getHtmlTemplate();
        $html = str_replace('{{TIMESTAMP}}', date('Y-m-d H:i:s'), $html);
        $html = str_replace('{{PROJECT_TYPE}}', $this->config['projectType'] ?? 'Desconhecido', $html);
        $html = str_replace('{{SUMMARY}}', $this->renderSummaryHtml($summary), $html);
        $html = str_replace('{{STATISTICS}}', $this->renderStatisticsHtml($statistics), $html);
        $html = str_replace('{{DUPLICATES}}', $this->renderDuplicatesHtml($duplicates), $html);
        $html = str_replace('{{RECOMMENDATIONS}}', $this->renderRecommendationsHtml(), $html);

        file_put_contents($filepath, $html);
        echo "🌐 Relatório HTML: {$filename}\n";
        
        return $filepath;
    }

    private function generateCsvReport($timestamp)
    {
        $filename = "duplicate-report-{$timestamp}.csv";
        $filepath = $this->outputDir . '/' . $filename;

        $csv = fopen($filepath, 'w');
        
        // Cabeçalho
        fputcsv($csv, [
            'Grupo',
            'Arquivo',
            'Linha Inicial',
            'Linha Final',
            'Linhas',
            'Similaridade',
            'Hash',
            'Tipo'
        ]);

        // Dados
        foreach ($this->data['duplicates'] ?? [] as $groupIndex => $duplicate) {
            foreach ($duplicate['blocks'] as $block) {
                fputcsv($csv, [
                    $groupIndex + 1,
                    $block['file'],
                    $block['startLine'],
                    $block['endLine'],
                    $block['endLine'] - $block['startLine'] + 1,
                    $duplicate['similarity'] ?? 1.0,
                    $duplicate['hash'] ?? '',
                    $duplicate['type'] ?? 'exact'
                ]);
            }
        }

        fclose($csv);
        echo "📊 Relatório CSV: {$filename}\n";
        
        return $filepath;
    }

    private function generateMarkdownReport($timestamp)
    {
        $filename = "duplicate-report-{$timestamp}.md";
        $filepath = $this->outputDir . '/' . $filename;

        $summary = $this->generateSummary();
        $statistics = $this->generateStatistics();

        $markdown = "# Relatório de Duplicatas de Código\n\n";
        $markdown .= "**Data:** " . date('Y-m-d H:i:s') . "\n";
        $markdown .= "**Projeto:** " . ($this->config['projectType'] ?? 'Desconhecido') . "\n\n";

        // Resumo
        $markdown .= "## 📊 Resumo\n\n";
        $markdown .= "- **Arquivos processados:** {$summary['processedFiles']}\n";
        $markdown .= "- **Grupos de duplicatas:** {$summary['duplicateGroups']}\n";
        $markdown .= "- **Total de duplicatas:** {$summary['totalDuplicates']}\n";
        $markdown .= "- **Linhas duplicadas:** {$summary['duplicatedLines']}\n";
        $markdown .= "- **Economia potencial:** {$summary['potentialSavings']}%\n\n";

        // Estatísticas
        $markdown .= "## 📈 Estatísticas\n\n";
        $markdown .= "### Por Tipo de Arquivo\n\n";
        $markdown .= "| Extensão | Arquivos | Duplicatas | Percentual |\n";
        $markdown .= "|----------|----------|------------|------------|\n";
        
        foreach ($statistics['byFileType'] as $ext => $data) {
            $percentage = round(($data['duplicates'] / $summary['totalDuplicates']) * 100, 1);
            $markdown .= "| {$ext} | {$data['files']} | {$data['duplicates']} | {$percentage}% |\n";
        }

        // Top duplicatas
        $markdown .= "\n### 🔥 Top 10 Duplicatas\n\n";
        $duplicates = $this->data['duplicates'] ?? [];
        usort($duplicates, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $markdown .= "| # | Arquivo | Linhas | Duplicatas |\n";
        $markdown .= "|---|---------|--------|------------|\n";
        
        foreach (array_slice($duplicates, 0, 10) as $index => $duplicate) {
            $firstBlock = $duplicate['blocks'][0] ?? [];
            $file = basename($firstBlock['file'] ?? 'N/A');
            $lines = ($firstBlock['endLine'] ?? 0) - ($firstBlock['startLine'] ?? 0) + 1;
            $markdown .= "| " . ($index + 1) . " | {$file} | {$lines} | {$duplicate['count']} |\n";
        }

        // Recomendações
        $recommendations = $this->generateRecommendations();
        $markdown .= "\n## 💡 Recomendações\n\n";
        foreach ($recommendations as $rec) {
            $markdown .= "- **{$rec['title']}:** {$rec['description']}\n";
        }

        file_put_contents($filepath, $markdown);
        echo "📝 Relatório Markdown: {$filename}\n";
        
        return $filepath;
    }

    private function generateSummary()
    {
        $duplicates = $this->data['duplicates'] ?? [];
        $processedFiles = $this->data['processedFiles'] ?? [];
        
        $totalDuplicates = 0;
        $duplicatedLines = 0;
        $totalLines = 0;
        
        foreach ($duplicates as $duplicate) {
            $totalDuplicates += $duplicate['count'];
            
            foreach ($duplicate['blocks'] as $block) {
                $lines = ($block['endLine'] ?? 0) - ($block['startLine'] ?? 0) + 1;
                $duplicatedLines += $lines;
            }
        }
        
        // Calcular total de linhas processadas
        foreach ($processedFiles as $file) {
            if (file_exists($file)) {
                $totalLines += count(file($file));
            }
        }
        
        $potentialSavings = $totalLines > 0 ? round(($duplicatedLines / $totalLines) * 100, 2) : 0;
        
        return [
            'processedFiles' => count($processedFiles),
            'duplicateGroups' => count($duplicates),
            'totalDuplicates' => $totalDuplicates,
            'duplicatedLines' => $duplicatedLines,
            'totalLines' => $totalLines,
            'potentialSavings' => $potentialSavings
        ];
    }

    private function generateStatistics()
    {
        $duplicates = $this->data['duplicates'] ?? [];
        $processedFiles = $this->data['processedFiles'] ?? [];
        
        $byFileType = [];
        $byDirectory = [];
        $sizeDistribution = ['small' => 0, 'medium' => 0, 'large' => 0];
        
        foreach ($duplicates as $duplicate) {
            foreach ($duplicate['blocks'] as $block) {
                $file = $block['file'] ?? '';
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $dir = dirname($file);
                $lines = ($block['endLine'] ?? 0) - ($block['startLine'] ?? 0) + 1;
                
                // Por tipo de arquivo
                if (!isset($byFileType[$ext])) {
                    $byFileType[$ext] = ['files' => 0, 'duplicates' => 0];
                }
                $byFileType[$ext]['duplicates']++;
                
                // Por diretório
                if (!isset($byDirectory[$dir])) {
                    $byDirectory[$dir] = 0;
                }
                $byDirectory[$dir]++;
                
                // Distribuição por tamanho
                if ($lines <= 5) {
                    $sizeDistribution['small']++;
                } elseif ($lines <= 20) {
                    $sizeDistribution['medium']++;
                } else {
                    $sizeDistribution['large']++;
                }
            }
        }
        
        // Contar arquivos por tipo
        foreach ($processedFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (isset($byFileType[$ext])) {
                $byFileType[$ext]['files']++;
            }
        }
        
        return [
            'byFileType' => $byFileType,
            'byDirectory' => $byDirectory,
            'sizeDistribution' => $sizeDistribution
        ];
    }

    private function generateRecommendations()
    {
        $summary = $this->generateSummary();
        $recommendations = [];
        
        if ($summary['duplicateGroups'] > 10) {
            $recommendations[] = [
                'title' => 'Alto número de duplicatas',
                'description' => 'Considere refatorar o código para extrair funcionalidades comuns em funções ou classes reutilizáveis.',
                'priority' => 'high'
            ];
        }
        
        if ($summary['potentialSavings'] > 20) {
            $recommendations[] = [
                'title' => 'Grande economia potencial',
                'description' => 'Mais de 20% do código é duplicado. Priorize a remoção de duplicatas para melhorar a manutenibilidade.',
                'priority' => 'high'
            ];
        }
        
        $recommendations[] = [
            'title' => 'Implementar hooks de Git',
            'description' => 'Configure hooks pre-commit e pre-merge para prevenir futuras duplicatas.',
            'priority' => 'medium'
        ];
        
        $recommendations[] = [
            'title' => 'Revisão de código',
            'description' => 'Estabeleça processos de revisão de código para identificar duplicatas antes do merge.',
            'priority' => 'medium'
        ];
        
        if ($summary['duplicateGroups'] > 0) {
            $recommendations[] = [
                'title' => 'Remoção automática',
                'description' => 'Use o comando --remove-smart para remover duplicatas automaticamente com backup.',
                'priority' => 'low'
            ];
        }
        
        return $recommendations;
    }

    private function getHtmlTemplate()
    {
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Duplicatas de Código</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; }
        .content { padding: 30px; }
        .section { margin-bottom: 30px; }
        .card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
        .metric { display: inline-block; margin: 10px 20px 10px 0; }
        .metric-value { font-size: 2em; font-weight: bold; color: #667eea; }
        .metric-label { font-size: 0.9em; color: #6c757d; }
        .duplicate-group { border-left: 4px solid #dc3545; padding-left: 15px; margin-bottom: 15px; }
        .file-path { font-family: monospace; background: #f1f3f4; padding: 2px 6px; border-radius: 3px; }
        .recommendation { border-left: 4px solid #28a745; padding-left: 15px; margin-bottom: 10px; }
        .high-priority { border-left-color: #dc3545; }
        .medium-priority { border-left-color: #ffc107; }
        .low-priority { border-left-color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: 600; }
        .progress-bar { background: #e9ecef; border-radius: 10px; height: 20px; overflow: hidden; }
        .progress-fill { background: linear-gradient(90deg, #28a745, #20c997); height: 100%; transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Relatório de Duplicatas de Código</h1>
            <p>Gerado em: {{TIMESTAMP}} | Projeto: {{PROJECT_TYPE}}</p>
        </div>
        <div class="content">
            {{SUMMARY}}
            {{STATISTICS}}
            {{DUPLICATES}}
            {{RECOMMENDATIONS}}
        </div>
    </div>
</body>
</html>';
    }

    private function renderSummaryHtml($summary)
    {
        return '<div class="section">
            <h2>📊 Resumo Executivo</h2>
            <div class="card">
                <div class="metric">
                    <div class="metric-value">' . $summary['processedFiles'] . '</div>
                    <div class="metric-label">Arquivos Processados</div>
                </div>
                <div class="metric">
                    <div class="metric-value">' . $summary['duplicateGroups'] . '</div>
                    <div class="metric-label">Grupos de Duplicatas</div>
                </div>
                <div class="metric">
                    <div class="metric-value">' . $summary['totalDuplicates'] . '</div>
                    <div class="metric-label">Total de Duplicatas</div>
                </div>
                <div class="metric">
                    <div class="metric-value">' . $summary['potentialSavings'] . '%</div>
                    <div class="metric-label">Economia Potencial</div>
                </div>
            </div>
        </div>';
    }

    private function renderStatisticsHtml($statistics)
    {
        $html = '<div class="section">
            <h2>📈 Estatísticas Detalhadas</h2>
            <div class="card">
                <h3>Por Tipo de Arquivo</h3>
                <table>
                    <tr><th>Extensão</th><th>Arquivos</th><th>Duplicatas</th><th>Percentual</th></tr>';
        
        foreach ($statistics['byFileType'] as $ext => $data) {
            $html .= '<tr><td>.' . $ext . '</td><td>' . $data['files'] . '</td><td>' . $data['duplicates'] . '</td><td>' . round(($data['duplicates'] / array_sum(array_column($statistics['byFileType'], 'duplicates'))) * 100, 1) . '%</td></tr>';
        }
        
        $html .= '</table>
            </div>
        </div>';
        
        return $html;
    }

    private function renderDuplicatesHtml($duplicates)
    {
        $html = '<div class="section">
            <h2>🔍 Duplicatas Encontradas</h2>';
        
        foreach (array_slice($duplicates, 0, 10) as $index => $duplicate) {
            $html .= '<div class="card duplicate-group">
                <h4>Grupo #' . ($index + 1) . ' (' . $duplicate['count'] . ' duplicatas)</h4>';
            
            foreach ($duplicate['blocks'] as $block) {
                $html .= '<p><span class="file-path">' . htmlspecialchars($block['file']) . '</span> (linhas ' . $block['startLine'] . '-' . $block['endLine'] . ')</p>';
            }
            
            $html .= '</div>';
        }
        
        if (count($duplicates) > 10) {
            $html .= '<p><em>Mostrando apenas os primeiros 10 grupos. Total: ' . count($duplicates) . ' grupos.</em></p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    private function renderRecommendationsHtml()
    {
        $recommendations = $this->generateRecommendations();
        $html = '<div class="section">
            <h2>💡 Recomendações</h2>';
        
        foreach ($recommendations as $rec) {
            $priorityClass = $rec['priority'] . '-priority';
            $html .= '<div class="recommendation ' . $priorityClass . '">
                <h4>' . $rec['title'] . '</h4>
                <p>' . $rec['description'] . '</p>
            </div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public function generateDashboard($reportData)
    {
        $filename = 'dashboard.html';
        $filepath = $this->outputDir . '/' . $filename;
        
        // Gerar dashboard interativo com gráficos
        $html = $this->getDashboardTemplate();
        $html = str_replace('{{DATA}}', json_encode($reportData), $html);
        
        file_put_contents($filepath, $html);
        echo "📊 Dashboard interativo: {$filename}\n";
        
        return $filepath;
    }

    private function getDashboardTemplate()
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Duplicatas de Código</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .chart-container { width: 400px; height: 400px; display: inline-block; margin: 20px; }
    </style>
</head>
<body>
    <h1>📊 Dashboard de Duplicatas</h1>
    <div class="chart-container">
        <canvas id="fileTypeChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="sizeChart"></canvas>
    </div>
    <script>
        const data = {{DATA}};
        // Implementar gráficos com Chart.js
    </script>
</body>
</html>';
    }
}