<?php 
require_once 'config.php'; 
if (isSurveyOpen($pdo)) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASFOUR 2026 - Belum Dimulai</title>
    <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="ASFOUR 2026 - Survey Belum Dimulai">
    <meta property="og:description" content="Survey ASFOUR 2026 saat ini belum dibuka atau telah berakhir.">
    <meta property="og:image" content="logo.png">
    <meta property="og:type" content="website">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DynaPuff:wght@400..700&family=Ranchers&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0F172A;
            --text-light: #F3F4F6;
            --accent: #3B82F6;
            --soft-accent: #94A3B8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DynaPuff', system-ui;
            font-optical-sizing: auto;
            font-weight: 400;
            font-style: normal;
            background: linear-gradient(135deg, #FFD700 0%, #FF8C00 50%, #FF6347 100%);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            background: #FFFFFF;
            border-radius: 30px;
            box-shadow: 
                0 10px 40px -10px rgba(0,0,0,0.15),
                inset 0 2px 0 rgba(255,255,255,0.5);
            animation: fadeIn 1s ease-out;
        }

        .icon-wrapper {
            width: 120px;
            height: 120px;
            background: linear-gradient(180deg, #4A90D9 0%, #3B82F6 50%, #2563EB 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 2rem;
            position: relative;
            box-shadow: 
                0 6px 0 #1D4ED8,
                0 8px 15px rgba(0, 0, 0, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }
        
        .icon-wrapper::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50% 50% 0 0;
            z-index: 1;
        }
        
        .icon-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 100%);
            border-radius: 0 0 50% 50%;
            z-index: 1;
        }
        
        .icon-wrapper i {
            font-size: 3rem;
            color: #FFFFFF;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        h1 {
            font-family: 'Ranchers', sans-serif;
            font-weight: 400;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #111111;
            display: inline-block;
        }

        p {
            font-size: 1.1rem;
            color: #4B5563;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            font-weight: 400;
        }

        .btn-back {
            font-family: 'DynaPuff', system-ui;
            display: inline-block;
            padding: 16px 48px;
            background: linear-gradient(180deg, #FF8C42 0%, #FF6B35 50%, #E55A2B 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 400;
            font-size: 1.2rem;
            letter-spacing: 1px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 6px 0 #B84720,
                0 8px 15px rgba(0, 0, 0, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .btn-back::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50px 50px 0 0;
            z-index: -1;
        }
        
        .btn-back::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 100%);
            border-radius: 0 0 50px 50px;
            z-index: -1;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 9px 0 #B84720,
                0 12px 20px rgba(0, 0, 0, 0.35),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
            background: linear-gradient(180deg, #FF9B55 0%, #FF7B45 50%, #E56A3B 100%);
        }
        
        .btn-back:active {
            transform: translateY(3px);
            box-shadow: 
                0 2px 0 #B84720,
                0 4px 10px rgba(0, 0, 0, 0.3),
                inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.8; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h1>Survey Belum Dimulai</h1>
        <p>Mohon maaf, saat ini survey ASFOUR 2026 belum dibuka atau telah berakhir. Silakan kembali lagi nanti atau hubungi panitia untuk informasi lebih lanjut.</p>
        <a href="index.php" class="btn-back">Coba Lagi</a>
    </div>
</body>
</html>
