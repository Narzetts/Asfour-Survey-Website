<?php
/**
 * AI Analysis Handler for ASFOUR Survey
 * Uses Qwen3 Coder 480B A35B via OpenRouter API
 * For categorization of survey responses
 */

require_once 'config.php';
session_start();

// OpenRouter API Configuration
define('OPENROUTER_API_KEY', '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'); // Set your OpenRouter API key here or in environment
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('OPENROUTER_MODEL', 'qwen/qwen3-coder-480b-a35b:free');

/**
 * Analyze a single aspirasi text using Qwen AI
 * Returns category (kesiswaan/kurikulum/humas/sarana prasarana)
 */
function analyzeAspirasi($text) {
    $apiKey = OPENROUTER_API_KEY;
    
    if (empty($apiKey)) {
        // If no API key, use keyword-based analysis as fallback
        return keywordBasedAnalysis($text);
    }
    
    $prompt = sprintf('
Analisis teks aspirasi berikut dalam bahasa Indonesia:

"%s"

Berikan hasil analisis dalam format JSON dengan struktur berikut:
{
    "category": "kesiswaan" atau "kurikulum" atau "humas" atau "sarana_prasarana",
    "confidence": 0.0 sampai 1.0,
    "explanation": "penjelasan singkat dalam bahasa Indonesia"
}

Kategori:
- kesiswaan: terkait kegiatan siswa, organisasi siswa, disiplin siswa, kegiatan ekstrakurikuler
- kurikulum: terkait pembelajaran, mata pelajaran, metode mengajar, evaluasi belajar
- humas: terkait komunikasi sekolah, hubungan dengan orang tua, citra sekolah, informasi publik
- sarana_prasarana: terkait fasilitas sekolah, gedung, peralatan, infrastruktur

Hanya kembalikan JSON tanpa teks tambahan.
', htmlspecialchars($text));

    $data = [
        'model' => OPENROUTER_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Anda adalah analis kategorisasi teks bahasa Indonesia yang ahli. Anda menganalisis aspirasi dan masukan untuk institusi pendidikan.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 300
    ];

    $ch = curl_init(OPENROUTER_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'HTTP-Referer: ' . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://localhost'),
        'X-Title: ASFOUR Survey Analysis'
    ]);
    // Set timeout to prevent hanging
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Log curl errors for debugging
    if ($curlError) {
        error_log("cURL error in analyzeAspirasi: " . $curlError);
    }

    if ($httpCode === 200 && $response && !$curlError) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            $content = trim($result['choices'][0]['message']['content']);
            // Extract JSON from the response (in case there's extra text)
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $jsonStr = $matches[0];
                $analysis = json_decode($jsonStr, true);
                if ($analysis) {
                    return $analysis;
                }
            }
        }
    }

    // Fallback to keyword-based analysis
    return keywordBasedAnalysis($text);
}

/**
 * Fallback keyword-based analysis when API is not available
 */
function keywordBasedAnalysis($text) {
    $text = strtolower($text);
    
    // Category analysis based on keywords
    $categories = [
        'kesiswaan' => ['siswa', 'osiss', 'ekskul', 'ekstrakurikuler', 'pramuka', 'paskibra', 'pmr', 'basket', 'sepak bola', 'voli', 'seni', 'tari', 'musik', 'disiplin', 'tata tertib', 'poin', 'pelanggaran', 'kegiatan siswa', 'organisasi', 'kepemimpinan', 'siswi', 'kelas', 'teman', 'guru bk', 'konseling'],
        'kurikulum' => ['pelajaran', 'materi', 'guru', 'mengajar', 'ujian', 'tugas', 'pr', 'nilai', 'rapor', 'semester', 'kkm', 'kurikulum', 'merdeka', 'pembelajaran', 'belajar', 'pendidikan', 'akademik', 'jadwal', 'jam pelajaran', 'metode', 'evaluasi', 'asesmen', 'ulangan', 'quiz'],
        'humas' => ['informasi', 'komunikasi', 'website', 'media sosial', 'instagram', 'facebook', 'whatsapp', 'grup', 'pengumuman', 'berita', 'publikasi', 'dokumentasi', 'humas', 'public relation', 'citra', 'promosi', 'ppdb', 'pendaftaran', 'wali murid', 'orang tua', 'rapat', 'undangan', 'surat', 'kontak'],
        'sarana_prasarana' => ['fasilitas', 'gedung', 'ruangan', 'kelas', 'toilet', 'kamar mandi', 'wc', 'ac', 'kipas', 'meja', 'kursi', 'papan tulis', 'proyektor', 'lcd', 'komputer', 'laboratorium', 'lab', 'perpustakaan', 'buku', 'lapangan', 'kantin', 'parkir', 'air', 'listrik', 'internet', 'wifi', 'tempat sampah', 'kebersihan', 'perbaikan', 'renovasi', 'bangunan', 'infrastruktur']
    ];
    
    $categoryScores = [];
    foreach ($categories as $category => $keywords) {
        $score = 0;
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $score++;
            }
        }
        $categoryScores[$category] = $score;
    }
    
    arsort($categoryScores);
    $category = key($categoryScores);
    
    // If all scores are 0, default to neutral category
    if ($categoryScores[$category] == 0) {
        $category = 'lainnya';
    }
    
    return [
        'category' => $category,
        'confidence' => 0.5,
        'explanation' => 'Analisis berdasarkan kata kunci'
    ];
}

/**
 * Analyze all unanalyzed survey responses
 */
function analyzeAllResponses($pdo) {
    try {
        // Get responses that haven't been analyzed yet
        $stmt = $pdo->query("SELECT id, aspirasi FROM surveys WHERE analyzed IS NULL OR analyzed = 0 ORDER BY created_at DESC");
        $responses = $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Gagal mengambil data survey: " . $e->getMessage());
    }
    
    $results = [];
    $count = 0;
    
    foreach ($responses as $response) {
        if (!empty(trim($response['aspirasi']))) {
            try {
                $analysis = analyzeAspirasi($response['aspirasi']);
                
                // Validate analysis result
                if (!isset($analysis['category'])) {
                    // Use fallback if analysis is invalid
                    $analysis = keywordBasedAnalysis($response['aspirasi']);
                }
                
                // Save analysis to database
                $updateStmt = $pdo->prepare("UPDATE surveys SET 
                    category = ?, 
                    ai_confidence = ?, 
                    ai_explanation = ?,
                    analyzed = 1,
                    analyzed_at = NOW()
                    WHERE id = ?");
                $updateStmt->execute([
                    $analysis['category'],
                    $analysis['confidence'] ?? 0.5,
                    $analysis['explanation'] ?? '',
                    $response['id']
                ]);
                
                $results[] = [
                    'id' => $response['id'],
                    'aspirasi' => substr($response['aspirasi'], 0, 100) . '...',
                    'analysis' => $analysis
                ];
                $count++;
                
                // Rate limiting - wait between requests
                if (!empty(OPENROUTER_API_KEY)) {
                    usleep(500000); // 0.5 second delay
                }
            } catch (Exception $e) {
                // Log error but continue with other responses
                error_log("Error analyzing response ID {$response['id']}: " . $e->getMessage());
                continue;
            }
        }
    }
    
    return [
        'analyzed_count' => $count,
        'results' => $results
    ];
}

/**
 * Get analysis statistics
 */
function getAnalysisStats($pdo) {
    // Category distribution
    $categoryStmt = $pdo->query("SELECT 
        category,
        COUNT(*) as count
        FROM surveys WHERE analyzed = 1 AND category IS NOT NULL
        GROUP BY category
        ORDER BY count DESC");
    $categoryStats = $categoryStmt->fetchAll();
    
    // Recent analyzed items
    $recentStmt = $pdo->query("SELECT id, aspirasi, category, ai_confidence, analyzed_at 
        FROM surveys WHERE analyzed = 1 
        ORDER BY analyzed_at DESC LIMIT 10");
    $recentItems = $recentStmt->fetchAll();
    
    // Total analyzed vs not analyzed
    $totalStmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN analyzed = 1 THEN 1 ELSE 0 END) as analyzed,
        SUM(CASE WHEN analyzed IS NULL OR analyzed = 0 THEN 1 ELSE 0 END) as not_analyzed
        FROM surveys");
    $totalStats = $totalStmt->fetch();
    
    return [
        'category' => $categoryStats,
        'recent' => $recentItems,
        'total' => $totalStats
    ];
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'analyze':
                if (!isset($_SESSION['admin_id'])) {
                    echo json_encode(['error' => 'Unauthorized. Please login as admin.']);
                    exit;
                }
                $result = analyzeAllResponses($pdo);
                logActivity($pdo, $_SESSION['admin_id'], 'AI Analysis', "Analyzed {$result['analyzed_count']} responses");
                echo json_encode($result);
                break;
                
            case 'stats':
                if (!isset($_SESSION['admin_id'])) {
                    echo json_encode(['error' => 'Unauthorized. Please login as admin.']);
                    exit;
                }
                echo json_encode(getAnalysisStats($pdo));
                break;
                
            default:
                echo json_encode(['error' => 'Invalid action. Use "analyze" or "stats".']);
        }
    } catch (Exception $e) {
        echo json_encode([
            'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'details' => $e->getTraceAsString()
        ]);
    }
    exit;
}
?>