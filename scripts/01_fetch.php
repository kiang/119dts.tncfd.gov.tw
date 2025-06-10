<?php
$rootPath = dirname(__DIR__);
// Set UTF-8 encoding
mb_internal_encoding('UTF-8');

// Function to create year/month directories
function createDirectories($year, $month) {
    global $rootPath;
    $path = "{$rootPath}/docs/{$year}/{$month}";
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    return $path;
}

// Function to process datetime string
function processDateTime($datetime) {
    $dt = DateTime::createFromFormat('Y/m/d H:i:s', $datetime);
    if (!$dt) {
        throw new Exception("Invalid datetime format: {$datetime}");
    }
    return $dt;
}

// Fetch the HTML content
$url = 'https://119dts.tncfd.gov.tw/DTS/caselist/html';
$html = file_get_contents($url);

if ($html === false) {
    die("Failed to fetch data from {$url}\n");
}

// Convert HTML to UTF-8 if needed
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

// Create a DOM document
$dom = new DOMDocument('1.0', 'UTF-8');
@$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

// Create XPath
$xpath = new DOMXPath($dom);

// Find all table rows
$rows = $xpath->query('//table[@id="dataTable"]/tr');

$allData = [];
$currentRow = 0;

// Process each row
foreach ($rows as $row) {
    $currentRow++;
    if ($currentRow === 1) continue; // Skip header row
    
    $cells = $row->getElementsByTagName('td');
    if ($cells->length < 7) continue; // Skip rows without enough cells
    
    $caseType = trim($cells->item(3)->textContent);
    if($caseType === '緊急救護' || $caseType === '其他') {
        continue;
    }
    $caseNumber = trim($cells->item(1)->textContent);
    $datetime = trim($cells->item(2)->textContent);
    $location = trim($cells->item(4)->textContent);
    $unit = trim($cells->item(5)->textContent);
    $status = trim($cells->item(6)->textContent);
    
    try {
        $dt = processDateTime($datetime);
        $year = $dt->format('Y');
        $month = $dt->format('m');
        
        $data = [
            'id' => $caseNumber,
            'datetime' => $datetime,
            'case_type' => $caseType,
            'location' => $location,
            'unit' => $unit,
            'status' => $status
        ];
        
        // Save individual case file with history
        $dir = createDirectories($year, $month);
        $filePath = "{$dir}/{$caseNumber}.json";
        
        // Check if file exists and load existing data
        $caseData = [];
        if (file_exists($filePath)) {
            $existingContent = file_get_contents($filePath);
            $existingData = json_decode($existingContent, true);
            
            // Handle both old format (single record) and new format (with history)
            if (isset($existingData['history'])) {
                $caseData = $existingData;
            } else {
                // Convert old format to new format
                $caseData = [
                    'id' => $existingData['id'],
                    'case_type' => $existingData['case_type'],
                    'location' => $existingData['location'],
                    'unit' => $existingData['unit'],
                    'history' => [
                        [
                            'datetime' => $existingData['datetime'],
                            'status' => $existingData['status'],
                            'updated' => $existingData['datetime']
                        ]
                    ]
                ];
            }
        } else {
            // New case file
            $caseData = [
                'id' => $caseNumber,
                'case_type' => $caseType,
                'location' => $location,
                'unit' => $unit,
                'history' => []
            ];
        }
        
        // Check if this is a new status update
        $lastEntry = end($caseData['history']);
        $needsUpdate = false;
        
        if (!$lastEntry || $lastEntry['status'] !== $status || $lastEntry['datetime'] !== $datetime) {
            $needsUpdate = true;
        }
        
        if ($needsUpdate) {
            $caseData['history'][] = [
                'datetime' => $datetime,
                'status' => $status,
                'updated' => date('Y/m/d H:i:s')
            ];
        }
        
        file_put_contents($filePath, json_encode($caseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        // Add to all data array
        $allData[] = $data;
        
    } catch (Exception $e) {
        echo "Error processing row {$currentRow}: " . $e->getMessage() . "\n";
        continue;
    }
}

// Save complete list
file_put_contents($rootPath . '/docs/list.json', json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "Processing completed. Total cases processed: " . count($allData) . "\n";
