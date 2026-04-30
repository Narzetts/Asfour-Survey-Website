<?php 
require_once 'config.php'; 
if (!isSurveyOpen($pdo)) {
    header("Location: belum_mulai.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASFOUR 2026</title>
    <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="ASFOUR 2026 - Survey Aspirasi">
    <meta property="og:description" content="Survey Aspirasi dan Suara Anda untuk ASFOUR 2026">
    <meta property="og:image" content="logo.png">
    <meta property="og:type" content="website">
    
    <!-- Google Fonts: Ranchers & DynaPuff -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DynaPuff:wght@400..700&family=Ranchers&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- CSS VARIABLES & RESET --- */
        :root {
            --bg-primary: #FF8C00;
            --bg-secondary: #FFA500;
            --card-bg: #FFFFFF;
            --text-dark: #111111;
            --text-light: #F3F4F6;
            --accent: #FF6B35;
            --accent-hover: #E55A2B;
            --soft-accent: #FFB347;
            --error: #EF4444;
            --success: #10B981;
            --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.15);
            --shadow-glow: 0 0 20px rgba(255, 107, 53, 0.4);
            --radius-form: 24px;
            --radius-btn: 14px;
            --transition-speed: 0.5s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
        }

        body {
            font-family: 'DynaPuff', system-ui;
            font-optical-sizing: auto;
            font-weight: 400;
            font-style: normal;
            background: linear-gradient(135deg, #FFD700 0%, #FF8C00 50%, #FF6347 100%);
            color: var(--text-dark);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hidden { display: none !important; }

        /* Wrapper umum untuk konten agar rapi di tengah */
        .content-wrapper {
            text-align: center;
            width: 100%;
            max-width: 700px;
            padding: 0 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* --- PAGE SECTIONS --- */
        section {
            width: 100%;
            min-height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.6s ease;
            z-index: 1;
        }

        section.active {
            opacity: 1;
            pointer-events: all;
            z-index: 10;
            position: relative;
        }

        /* --- PAGE 1: LANDING --- */
        #landing-page { color: #FFF5E6; }

        .logo-container {
            margin-bottom: 1.5rem;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 6s ease-in-out infinite;
            filter: drop-shadow(0 0 10px rgba(255, 107, 53, 0.4));
        }
        .logo-container img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        h1.main-title {
            font-family: 'Ranchers', sans-serif;
            font-weight: 400;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 400;
            margin-bottom: 0.5rem;
            letter-spacing: -1.5px;
            line-height: 1.1;
            background: linear-gradient(135deg, #FFFFFF 20%, #FFE4B5 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        p.subtitle {
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: #FFDAB9;
            margin-bottom: 3.5rem;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .btn-start {
            font-family: 'DynaPuff', system-ui;
            background: linear-gradient(180deg, #FF8C42 0%, #FF6B35 50%, #E55A2B 100%);
            color: white;
            padding: 18px 64px;
            border-radius: 50px;
            font-size: 1.3rem;
            font-weight: 400;
            letter-spacing: 1px;
            border: none;
            cursor: pointer;
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

        .btn-start::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50px 50px 0 0;
            z-index: -1;
        }
        
        .btn-start::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 100%);
            border-radius: 0 0 50px 50px;
            z-index: -1;
        }

        .btn-start:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 9px 0 #B84720,
                0 12px 20px rgba(0, 0, 0, 0.35),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
            background: linear-gradient(180deg, #FF9B55 0%, #FF7B45 50%, #E56A3B 100%);
        }
        
        .btn-start:active {
            transform: translateY(3px);
            box-shadow: 
                0 2px 0 #B84720,
                0 4px 10px rgba(0, 0, 0, 0.3),
                inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Animations */
        .stagger-1 { animation: fadeInUp 0.8s ease-out 0.2s backwards; }
        .stagger-2 { animation: fadeInUp 0.8s ease-out 0.4s backwards; }
        .stagger-3 { animation: fadeInUp 0.8s ease-out 0.6s backwards; }
        .stagger-4 { animation: fadeInUp 0.8s ease-out 0.8s backwards; }

        /* --- PAGE 2: LOADING --- */
        #loading-page { color: #FFF5E6; }
        .loading-container { display: flex; flex-direction: column; align-items: center; gap: 1.5rem; width: 100%; max-width: 400px; }
        .loading-text { font-size: 1.25rem; font-weight: 500; letter-spacing: 1px; color: #FFF5E6; margin-bottom: 0.5rem; }
        .progress-track { width: 100%; height: 4px; background: rgba(255, 255, 255, 0.2); border-radius: 4px; overflow: hidden; position: relative; }
        .progress-bar { height: 100%; width: 0%; background: linear-gradient(90deg, #FFD700, #FF6B35, #FF8C00); border-radius: 4px; box-shadow: 0 0 15px rgba(255, 107, 53, 0.6); transition: width 0.1s linear; }
        .percentage { font-family: 'Courier New', monospace; font-size: 0.9rem; color: #FFDAB9; letter-spacing: -0.5px; margin-top: 0.5rem; }

        /* --- PAGE 3: SURVEY FORM --- */
        #survey-page { background: linear-gradient(135deg, #FFD700 0%, #FF8C00 50%, #FF6347 100%); padding: 40px 20px; }

        .form-container {
            background: var(--card-bg);
            width: 100%;
            max-width: 550px;
            border-radius: var(--radius-form);
            padding: 40px;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
            animation: scaleIn 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        /* --- BANNER AREA UPDATE (USER IMAGE) --- */
        .banner-area {
            width: 100%;
            height: 180px; /* Tinggi banner */
            background-color: #F3F4F6;
            border-radius: 16px;
            margin-bottom: 2rem;
            overflow: hidden; /* Penting */
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
        }

        .banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Agar gambar penuh tanpa gepeng */
            object-position: center;
            display: block;
            transition: transform 0.5s ease;
        }

        .banner-area:hover .banner-img {
            transform: scale(1.03); /* Zoom sedikit saat hover */
        }

        /* --- END BANNER AREA UPDATE --- */

        .form-header { margin-bottom: 2rem; text-align: center; }
        .form-header h2 { font-family: 'Ranchers', sans-serif; font-weight: 400; color: var(--text-dark); font-size: 1.6rem; margin-bottom: 0.5rem; }
        
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group label { display: block; margin-bottom: 0.6rem; font-weight: 500; color: #374151; font-size: 0.9rem; margin-left: 4px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid #E5E7EB; border-radius: 12px; font-family: inherit; font-size: 0.95rem; color: var(--text-dark); background: #F9FAFB; transition: all 0.3s ease; }
        .form-control:focus { border-color: var(--accent); background: #FFFFFF; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08); }
        textarea.form-control { resize: vertical; min-height: 110px; line-height: 1.5; }

        #angkatan-group { max-height: 0; opacity: 0; overflow: hidden; transition: all 0.4s ease; }
        #angkatan-group.visible { max-height: 100px; opacity: 1; margin-bottom: 1.5rem; }

        .file-upload-wrapper { border: 2px dashed #D1D5DB; border-radius: 12px; padding: 24px; text-align: center; cursor: pointer; transition: all 0.3s; background: #F9FAFB; position: relative; }
        .file-upload-wrapper:hover { border-color: var(--accent); background: #EFF6FF; }
        .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .file-upload-content i { font-size: 1.8rem; color: #9CA3AF; margin-bottom: 8px; transition: color 0.3s; }
        .file-upload-wrapper:hover .file-upload-content i { color: var(--accent); }
        .file-upload-content p { color: #6B7280; font-size: 0.85rem; }

        #file-preview { margin-top: 12px; display: none; align-items: center; background: #F3F4F6; padding: 8px 12px; border-radius: 8px; border: 1px solid #E5E7EB; }
        .preview-img { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; margin-right: 12px; }
        .file-info { flex: 1; font-size: 0.85rem; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
        .remove-file { color: var(--error); cursor: pointer; padding: 6px; transition: 0.2s; border-radius: 6px; }
        .remove-file:hover { background: rgba(239, 68, 68, 0.1); }

        .btn-submit {
            font-family: 'DynaPuff', system-ui;
            width: 100%;
            background: linear-gradient(180deg, #FF8C42 0%, #FF6B35 50%, #E55A2B 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 400;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 5px 0 #B84720,
                0 7px 12px rgba(0, 0, 0, 0.25),
                inset 0 2px 0 rgba(255, 255, 255, 0.25);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
            border-radius: 12px 12px 0 0;
            z-index: -1;
        }
        
        .btn-submit::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.12) 100%);
            border-radius: 0 0 12px 12px;
            z-index: -1;
        }
        
        .btn-submit:hover {
            background: linear-gradient(180deg, #FF9B55 0%, #FF7B45 50%, #E56A3B 100%);
            transform: translateY(-2px);
            box-shadow: 
                0 8px 0 #B84720,
                0 10px 18px rgba(0, 0, 0, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.25);
        }
        
        .btn-submit:active {
            transform: translateY(3px);
            box-shadow: 
                0 2px 0 #B84720,
                0 4px 8px rgba(0, 0, 0, 0.25),
                inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* --- PAGE 4: THANK YOU --- */
        #thank-you-page { text-align: center; color: #FFF5E6; }
        .success-icon-wrapper {
            width: 90px;
            height: 90px;
            background: linear-gradient(180deg, #2ECC71 0%, #27AE60 50%, #1E8449 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 2rem;
            position: relative;
            box-shadow: 
                0 6px 0 #145A32,
                0 8px 15px rgba(0, 0, 0, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
        }
        
        .success-icon-wrapper::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50% 50% 0 0;
            z-index: 1;
        }
        
        .success-icon-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 100%);
            border-radius: 0 0 50% 50%;
            z-index: 1;
        }
        
        .success-icon-wrapper i {
            font-size: 2.5rem;
            color: #FFFFFF;
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        .ty-title { font-family: 'Ranchers', sans-serif; font-weight: 400; font-size: 2.5rem; margin-bottom: 1rem; color: #FFFFFF; }
        .ty-desc { font-size: 1rem; color: #FFDAB9; max-width: 450px; line-height: 1.7; font-weight: 400; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes popIn { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    </style>
    <script>
        // Check survey status on load
        const surveyStatus = "<?php echo isSurveyOpen($pdo) ? 'open' : 'closed'; ?>";
        if (surveyStatus === 'closed') {
            window.location.href = 'belum_mulai.php';
        }
    </script>
</head>
<body>

    <!-- Audio Elements -->
    <audio id="bg-music-1" loop>
        <source src="backsound1.mp3" type="audio/mpeg">
    </audio>
    <audio id="bg-music-2" loop>
        <source src="https://cdn.pixabay.com/download/audio/2022/01/18/audio_d0a13f69d2.mp3?filename=beautiful-sky-11194.mp3" type="audio/mpeg">
    </audio>

    <!-- PAGE 1: LANDING -->
    <section id="landing-page" class="active">
        <div class="content-wrapper">
            <div class="logo-container stagger-1">
                <img src="logo.png" alt="Logo ASFOUR 2026">
            </div>
            <h1 class="main-title stagger-2">ASFOUR 2026</h1>
            <p class="subtitle stagger-3">Survey Aspirasi dan Suara Anda</p>
            <button class="btn-start stagger-4" onclick="goToLoading()">MULAI</button>
        </div>
    </section>

    <!-- PAGE 2: LOADING -->
    <section id="loading-page">
        <div class="content-wrapper loading-container">
            <div class="loading-text">Memuat Survey...</div>
            <div class="progress-track">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <div class="percentage" id="percentage">0%</div>
        </div>
    </section>

    <!-- PAGE 3: SURVEY FORM -->
    <section id="survey-page">
        <div class="form-container">
            
            <!-- BANNER IMAGE USER -->
            <div class="banner-area">
                <!-- GANTI src="banner.jpg" DENGAN NAMA FILE FOTO ANDA -->
                <img src="banner.png" alt="Banner Survey" class="banner-img">
            </div>

            <div class="form-header">
                <h2>ASFOUR 2026</h2>
                <p style="color: #6B7280; font-size: 0.9rem;">Kami menghargai setiap aspirasi Anda.</p>
            </div>

            <form id="asfourForm" onsubmit="handleFormSubmit(event)">
                
                <div class="form-group">
                    <label for="role">Anda Mengisi Survey Sebagai? <span style="color:var(--error)">*</span></label>
                    <select id="role" name="role" class="form-control" required onchange="toggleAngkatan()">
                        <option value="" disabled selected>-- Pilih Peran --</option>
                        <option value="Guru">Guru</option>
                        <option value="Warga Sekolah">Warga Sekolah</option>
                        <option value="Siswa">Siswa</option>
                        <option value="Umum">Umum</option>
                    </select>
                </div>

                <div id="angkatan-group">
                    <div class="form-group">
                        <label for="angkatan">Angkatan <span style="color:var(--error)">*</span></label>
                        <select id="angkatan" name="angkatan" class="form-control">
                            <option value="" disabled selected>-- Pilih Angkatan --</option>
                            <option value="A'24">A'24</option>
                            <option value="A'25">A'25</option>
                            <option value="A'26">A'26</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="aspirasi">Sampaikan Aspirasimu! <span style="color:var(--error)">*</span></label>
                    <textarea id="aspirasi" name="aspirasi" class="form-control" placeholder="Tuliskan aspirasi Anda di sini..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Lampirkan File Pendukung (Opsional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="file-upload" name="file-upload" accept="image/*,.pdf,.doc,.docx" onchange="handleFilePreview(this)">
                        <div class="file-upload-content" id="upload-placeholder">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p>Klik untuk upload file</p>
                        </div>
                    </div>
                    
                    <div id="file-preview">
                        <img src="" alt="Preview" class="preview-img" id="preview-img">
                        <div class="file-info" id="file-name">filename.jpg</div>
                        <div class="remove-file" onclick="removeFile()">
                            <i class="fa-solid fa-trash"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-paper-plane"></i> Kirim
                </button>

            </form>
        </div>
    </section>

    <!-- PAGE 4: THANK YOU -->
    <section id="thank-you-page">
        <div class="content-wrapper">
            <div class="success-icon-wrapper">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 class="ty-title">Terima Kasih!</h2>
            <p class="ty-desc">
                Terima kasih telah mengisi survey ASFOUR 2026 dan menyampaikan aspirasi Anda. 
                Suara Anda sangat berarti bagi kami.
            </p>
        </div>
    </section>

    <script>
        const pages = {
            landing: document.getElementById('landing-page'),
            loading: document.getElementById('loading-page'),
            survey: document.getElementById('survey-page'),
            thanks: document.getElementById('thank-you-page')
        };
        
        const audio1 = document.getElementById('bg-music-1');
        const audio2 = document.getElementById('bg-music-2');
        audio1.volume = 0.4;
        audio2.volume = 0.5;

        function switchPage(hideId, showId) {
            pages[hideId].classList.remove('active');
            setTimeout(() => {
                pages[showId].classList.add('active');
            }, 100);
        }

        function goToLoading() {
            switchPage('landing', 'loading');
            simulateLoading();
        }

        function simulateLoading() {
            const progressBar = document.getElementById('progress-bar');
            const percentageText = document.getElementById('percentage');
            let width = 0;

            const interval = setInterval(() => {
                width += Math.random() * 2.5; 
                if (width >= 100) {
                    width = 100;
                    clearInterval(interval);
                    percentageText.innerText = "100%";
                    progressBar.style.width = "100%";
                    setTimeout(() => {
                        switchPage('loading', 'survey');
                        playMusic(1);
                    }, 600);
                } else {
                    progressBar.style.width = width + "%";
                    percentageText.innerText = Math.floor(width) + "%";
                }
            }, 25);
        }

        function playMusic(trackNumber) {
            audio1.pause();
            audio1.currentTime = 0;
            audio2.pause();
            audio2.currentTime = 0;

            if(trackNumber === 1) {
                audio1.play().catch(e => console.log("Audio blocked", e));
            } else if (trackNumber === 2) {
                audio2.play().catch(e => console.log("Audio blocked", e));
            }
        }

        function toggleAngkatan() {
            const role = document.getElementById('role').value;
            const angkatanGroup = document.getElementById('angkatan-group');
            const angkatanSelect = document.getElementById('angkatan');

            if (role === 'Siswa') {
                angkatanGroup.classList.add('visible');
                angkatanSelect.setAttribute('required', 'true');
            } else {
                angkatanGroup.classList.remove('visible');
                angkatanSelect.removeAttribute('required');
                angkatanSelect.value = "";
            }
        }

        function handleFilePreview(input) {
            const previewContainer = document.getElementById('file-preview');
            const previewImg = document.getElementById('preview-img');
            const fileNameDisplay = document.getElementById('file-name');
            const uploadPlaceholder = document.getElementById('upload-placeholder');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                previewContainer.style.display = 'flex';
                uploadPlaceholder.style.opacity = '0.5';
                fileNameDisplay.textContent = file.name;

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                    previewImg.style.display = 'block';
                } else {
                    previewImg.style.display = 'none';
                    fileNameDisplay.innerHTML = `<i class="fa-solid fa-file"></i> ${file.name}`;
                }
            }
        }

        function removeFile() {
            const input = document.getElementById('file-upload');
            const previewContainer = document.getElementById('file-preview');
            const uploadPlaceholder = document.getElementById('upload-placeholder');

            input.value = "";
            previewContainer.style.display = 'none';
            uploadPlaceholder.style.opacity = '1';
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            const role = document.getElementById('role').value;
            const angkatan = document.getElementById('angkatan').value;

            if (role === 'Siswa' && angkatan === "") {
                alert("Mohon pilih Angkatan Anda.");
                return;
            }

            const btn = document.querySelector('.btn-submit');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
            btn.disabled = true;

            const formData = new FormData(document.getElementById('asfourForm'));
            // Note: FormData handles file inputs automatically

            fetch('submit_survey.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    switchPage('survey', 'thanks');
                    playMusic(2);
                    document.getElementById('asfourForm').reset();
                    removeFile();
                    toggleAngkatan();
                } else {
                    alert('Terjadi kesalahan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>