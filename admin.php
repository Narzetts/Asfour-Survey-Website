<?php
require_once 'config.php';
session_start();

// --- Authentication & Role Management ---
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_user'] = $admin['username'];
        logActivity($pdo, $admin['id'], "Login", "Admin logged in");
        header("Location: admin.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

if (isset($_GET['logout'])) {
    logActivity($pdo, $_SESSION['admin_id'], "Logout", "Admin logged out");
    session_destroy();
    header("Location: admin.php");
    exit;
}

$isLoggedIn = isset($_SESSION['admin_id']);

// --- Admin Actions ---
if ($isLoggedIn) {
    $currentRole = $_SESSION['admin_role'];

    // Toggle Survey Status
    if (isset($_POST['toggle_survey']) && $currentRole !== 'staff') {
        $newStatus = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE settings SET survey_status = ? WHERE id = 1");
        $stmt->execute([$newStatus]);
        logActivity($pdo, $_SESSION['admin_id'], "Toggle Survey", "Status changed to $newStatus");
    }

    // Delete Survey Entry
    if (isset($_GET['delete_survey']) && $currentRole === 'super_admin') {
        $id = $_GET['delete_survey'];
        $stmt = $pdo->prepare("DELETE FROM surveys WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['admin_id'], "Delete Survey", "Deleted survey ID: $id");
    }
    
    // Add New Admin
    if (isset($_POST['add_admin']) && $currentRole === 'super_admin') {
        $user = $_POST['new_username'];
        $pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $role = $_POST['new_role'];
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$user, $pass, $role]);
            logActivity($pdo, $_SESSION['admin_id'], "Add Admin", "Added new admin: $user ($role)");
        } catch (Exception $e) { 
            $error = "Gagal menambah admin!"; 
        }
    }

    // Delete Admin
    if (isset($_GET['delete_admin']) && $currentRole === 'super_admin') {
        $adminIdToDelete = $_GET['delete_admin'];
        
        // Prevent deleting self
        if ($adminIdToDelete == $_SESSION['admin_id']) {
            $error = "Anda tidak bisa menghapus akun Anda sendiri!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$adminIdToDelete]);
            logActivity($pdo, $_SESSION['admin_id'], "Delete Admin", "Deleted admin ID: $adminIdToDelete");
            header("Location: admin.php"); // Refresh to update list
            exit;
        }
    }

    
    // Export to CSV
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="asfour_survey_data.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Tanggal', 'Jam', 'Peran', 'Angkatan', 'Aspirasi', 'File']);
        $stmt = $pdo->query("SELECT *, DATE(created_at) as tgl, TIME(created_at) as jam FROM surveys ORDER BY created_at DESC");
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['id'], $row['tgl'], $row['jam'], $row['role'], $row['angkatan'], $row['aspirasi'], $row['file_path']]);
        }
        fclose($output);
        exit;
    }
}

// --- Data Fetching ---
$surveyStatus = $pdo->query("SELECT survey_status FROM settings WHERE id = 1")->fetch()['survey_status'];
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM surveys")->fetchColumn(),
    'guru' => $pdo->query("SELECT COUNT(*) FROM surveys WHERE role='Guru'")->fetchColumn(),
    'siswa' => $pdo->query("SELECT COUNT(*) FROM surveys WHERE role='Siswa'")->fetchColumn(),
    'umum' => $pdo->query("SELECT COUNT(*) FROM surveys WHERE role='Umum'")->fetchColumn(),
    'warga' => $pdo->query("SELECT COUNT(*) FROM surveys WHERE role='Warga Sekolah'")->fetchColumn(),
];
$responses = $pdo->query("SELECT *, DATE(created_at) as tgl, TIME(created_at) as jam FROM surveys ORDER BY created_at DESC")->fetchAll();
$admins = $pdo->query("SELECT * FROM admins")->fetchAll();
$logs = $pdo->query("SELECT l.*, a.username FROM admin_logs l JOIN admins a ON l.admin_id = a.id ORDER BY l.created_at DESC LIMIT 50")->fetchAll();
$gallery = $pdo->query("SELECT id, file_path, role, angkatan, aspirasi, created_at FROM surveys WHERE file_path IS NOT NULL ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ASFOUR 2026</title>
    <link rel="icon" type="image/png" href="logo.png">
    
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
            --radius-card: 20px;
            --transition-speed: 0.3s;
            --sidebar-bg: linear-gradient(180deg, #E55A2B 0%, #FF6B35 50%, #FF8C42 100%);
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
            min-height: 100vh;
            display: flex;
        }

        /* Login Styles */
        .login-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #FFD700 0%, #FF8C00 50%, #FF6347 100%);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: var(--radius-card);
            width: 100%;
            max-width: 420px;
            box-shadow: 
                var(--shadow-soft),
                inset 0 2px 0 rgba(255,255,255,0.5);
            text-align: center;
            animation: scaleIn 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .login-card h2 {
            font-family: 'Ranchers', sans-serif;
            font-weight: 400;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .login-card .logo-login {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 6s ease-in-out infinite;
        }

        .login-card .logo-login img {
            width: 100%;
            height: auto;
        }

        .form-group {
            margin-bottom: 1.2rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #E5E7EB;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text-dark);
            background: #F9FAFB;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        /* 3D Button Style */
        .btn {
            font-family: 'DynaPuff', system-ui;
            padding: 14px 32px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 400;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background: linear-gradient(180deg, #FF8C42 0%, #FF6B35 50%, #E55A2B 100%);
            color: white;
            box-shadow: 
                0 5px 0 #B84720,
                0 7px 12px rgba(0, 0, 0, 0.25),
                inset 0 2px 0 rgba(255, 255, 255, 0.25);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50px 50px 0 0;
            z-index: -1;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.12) 100%);
            border-radius: 0 0 50px 50px;
            z-index: -1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 8px 0 #B84720,
                0 10px 18px rgba(0, 0, 0, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.25);
            background: linear-gradient(180deg, #FF9B55 0%, #FF7B45 50%, #E56A3B 100%);
        }

        .btn-primary:active {
            transform: translateY(3px);
            box-shadow: 
                0 2px 0 #B84720,
                0 4px 8px rgba(0, 0, 0, 0.25),
                inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            background: linear-gradient(180deg, #EF5350 0%, #E53935 50%, #C62828 100%);
            color: white;
            box-shadow: 
                0 5px 0 #8B0000,
                0 7px 12px rgba(0, 0, 0, 0.25),
                inset 0 2px 0 rgba(255, 255, 255, 0.25);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 8px 0 #8B0000,
                0 10px 18px rgba(0, 0, 0, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.25);
        }

        .btn-danger:active {
            transform: translateY(3px);
            box-shadow: 
                0 2px 0 #8B0000,
                0 4px 8px rgba(0, 0, 0, 0.25);
        }

        .btn-small {
            padding: 10px 20px;
            font-size: 0.85rem;
        }

        /* Main Layout */
        aside {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        main {
            flex: 1;
            padding: 30px;
            margin-left: 260px;
            overflow-y: auto;
            min-height: 100vh;
        }

        .sidebar-header {
            font-family: 'Ranchers', sans-serif;
            font-weight: 400;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: white;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 6px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            box-shadow: inset 0 2px 0 rgba(255,255,255,0.2);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Dashboard Content */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255,255,255,0.3);
        }

        .header h1 {
            font-family: 'Ranchers', sans-serif;
            font-weight: 400;
            font-size: 2rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #FFD700, #FF8C00, #FF6347);
        }

        .stat-card h3 {
            font-size: 0.85rem;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius-card);
            padding: 25px;
            box-shadow: var(--shadow-soft);
            margin-bottom: 2rem;
        }

        .card h3 {
            font-family: 'Ranchers', sans-serif;
            font-weight: 400;
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th {
            text-align: left;
            background: linear-gradient(180deg, #FFF5E6 0%, #FFE4B5 100%);
            padding: 14px;
            font-size: 0.85rem;
            color: #7C5A1A;
            font-weight: 600;
            border-bottom: 2px solid #FFD700;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 0.9rem;
            color: #374151;
        }

        tr:hover td {
            background: #FFFBF0;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: 'DynaPuff', system-ui;
        }

        .badge-open {
            background: linear-gradient(180deg, #2ECC71 0%, #27AE60 100%);
            color: white;
            box-shadow: 0 3px 0 #1E8449, 0 4px 8px rgba(0,0,0,0.2);
        }

        .badge-closed {
            background: linear-gradient(180deg, #EF5350 0%, #E53935 100%);
            color: white;
            box-shadow: 0 3px 0 #8B0000, 0 4px 8px rgba(0,0,0,0.2);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 1;
            background: #F3F4F6;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-item .overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 107, 53, 0.9);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .overlay {
            opacity: 1;
        }

        .gallery-item .overlay a {
            color: white;
            font-size: 1.5rem;
            text-decoration: none;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        /* Mobile Elements */
        .mobile-header {
            display: none;
            background: var(--sidebar-bg);
            color: white;
            padding: 15px 20px;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .mobile-header .sidebar-header {
            margin-bottom: 0;
            font-size: 1.4rem;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Action Links */
        .action-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .action-link:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        .action-link.danger {
            color: var(--error);
        }

        .action-link.danger:hover {
            color: #DC2626;
        }

        /* User Info in Sidebar */
        .sidebar-user-info {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar-user-info p {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 10px;
        }

        .logout-link {
            color: #FCA5A5 !important;
        }

        .logout-link:hover {
            color: #FEE2E2 !important;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            aside {
                left: -260px;
                transition: left 0.3s ease;
            }

            aside.open {
                left: 0;
            }

            main {
                margin-left: 0;
                padding: 20px;
            }

            .mobile-header {
                display: flex;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .card {
                padding: 15px;
            }

            table {
                font-size: 0.8rem;
            }

            th, td {
                padding: 10px 8px;
            }

            .btn {
                padding: 12px 24px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .login-card {
                padding: 30px 20px;
                margin: 0 20px;
            }
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <div class="login-overlay">
        <div class="login-card">
            <div class="logo-login">
                <img src="logo.png" alt="Logo ASFOUR 2026">
            </div>
            <h2>Admin Login</h2>
            <?php if (isset($error)): ?><p style="color:var(--error); margin-bottom:1rem; font-size:0.9rem;"><?php echo $error; ?></p><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                </div>
                <button type="submit" name="login" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">Login</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="mobile-header">
    <div class="sidebar-header">ASFOUR 2026</div>
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
</div>

<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar(false)"></div>

<aside id="sidebar">
    <div class="sidebar-header">ASFOUR 2026</div>
    <nav>
        <a href="#" class="nav-link active" onclick="showSection('overview')"><i class="fa-solid fa-chart-line"></i> Overview</a>
        <a href="#" class="nav-link" onclick="showSection('surveys')"><i class="fa-solid fa-list-check"></i> Survey Responses</a>
        <a href="#" class="nav-link" onclick="showSection('gallery')"><i class="fa-solid fa-images"></i> Media Gallery</a>
        <a href="#" class="nav-link" onclick="showSection('ai-analysis')"><i class="fa-solid fa-brain"></i> AI Analysis</a>
        <?php if ($currentRole !== 'staff'): ?>
            <a href="#" class="nav-link" onclick="showSection('settings')"><i class="fa-solid fa-gears"></i> Settings</a>
        <?php endif; ?>
        <?php if ($currentRole === 'super_admin'): ?>
            <a href="#" class="nav-link" onclick="showSection('admins')"><i class="fa-solid fa-users-gear"></i> Admin Management</a>
            <a href="#" class="nav-link" onclick="showSection('logs')"><i class="fa-solid fa-history"></i> Activity Logs</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-user-info">
        <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></p>
        <a href="?logout=1" class="nav-link logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</aside>

<main>
    <div class="header">
        <h1>Dashboard</h1>
        <div class="badge <?php echo $surveyStatus === 'open' ? 'badge-open' : 'badge-closed'; ?>">
            Survey: <?php echo strtoupper($surveyStatus); ?>
        </div>
    </div>

    <!-- Section: Overview -->
    <div id="overview" class="section active">
        <div class="stats-grid">
            <div class="stat-card"><h3>Total Responden</h3><div class="value"><?php echo $stats['total']; ?></div></div>
            <div class="stat-card"><h3>Siswa</h3><div class="value"><?php echo $stats['siswa']; ?></div></div>
            <div class="stat-card"><h3>Guru</h3><div class="value"><?php echo $stats['guru']; ?></div></div>
            <div class="stat-card"><h3>Warga Sekolah</h3><div class="value"><?php echo $stats['warga']; ?></div></div>
        </div>
        <div class="card">
            <h3>Statistik Singkat</h3>
            <p style="color: #6B7280;">Data responden yang masuk per hari ini.</p>
        </div>
    </div>

    <!-- Section: Surveys -->
    <div id="surveys" class="section">
        <div class="header" style="border-bottom:none; padding-bottom:0;">
            <h3 style="font-family:'Ranchers',sans-serif; font-size:1.5rem; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.2);">Survey Responses</h3>
            <a href="?export=1" class="btn btn-primary btn-small"><i class="fa-solid fa-file-export"></i> Export CSV</a>
        </div>
        <div class="card" style="padding:0; overflow-x: auto;">
            <table style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 140px;">Waktu</th>
                        <th style="width: 100px;">Peran</th>
                        <th style="width: 90px;">Angkatan</th>
                        <th>Aspirasi</th>
                        <th style="width: 100px;">Lampiran</th>
                        <?php if ($currentRole === 'super_admin'): ?><th style="width: 70px;">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($responses as $r): ?>
                    <tr>
                        <td style="font-size: 0.85rem; white-space: nowrap;">
                            <div style="color: #6B7280;"><?php echo $r['tgl']; ?></div>
                            <div style="color: #9CA3AF; font-size: 0.8rem;"><?php echo $r['jam']; ?></div>
                        </td>
                        <td>
                            <span class="badge" style="background: var(--soft-accent); color: white; font-size: 0.75rem; padding: 4px 12px;">
                                <?php echo htmlspecialchars($r['role']); ?>
                            </span>
                        </td>
                        <td style="text-align: center; font-size: 0.9rem;">
                            <?php echo $r['angkatan'] ? htmlspecialchars($r['angkatan']) : '<span style="color:#9CA3AF;">-</span>'; ?>
                        </td>
                        <td>
                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($r['aspirasi']); ?>">
                                <?php echo htmlspecialchars(mb_substr($r['aspirasi'], 0, 80)) . (mb_strlen($r['aspirasi']) > 80 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($r['file_path']): ?>
                                <a href="<?php echo htmlspecialchars($r['file_path']); ?>" target="_blank" class="action-link" title="Lihat file">
                                    <i class="fa-solid fa-paperclip" style="color: var(--accent);"></i>
                                </a>
                            <?php else: ?>
                                <span style="color: #D1D5DB;">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($currentRole === 'super_admin'): ?>
                        <td><a href="?delete_survey=<?php echo $r['id']; ?>" onclick="return confirm('Hapus data ini?')" class="action-link danger" title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </a></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section: Gallery -->
    <div id="gallery" class="section">
        <h3 style="font-family:'Ranchers',sans-serif; font-size:1.5rem; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.2); margin-bottom:1.5rem;">Lampiran User</h3>
        <div class="card" style="padding:0; overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Nama File</th>
                        <th>Peran</th>
                        <th>Angkatan</th>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gallery as $g): 
                        $fileName = basename($g['file_path']);
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $g['file_path']);
                        // Escape data for JavaScript - use addslashes for single quotes
                        $jsFilePath = addslashes($g['file_path']);
                        $jsRole = addslashes($g['role']);
                        $jsAngkatan = addslashes($g['angkatan'] ?: '-');
                        $jsAspirasi = addslashes(substr($g['aspirasi'], 0, 200));
                        $jsDate = addslashes($g['created_at']);
                    ?>
                    <tr>
                        <td style="width:100px; text-align:center;">
                            <?php if ($isImage): ?>
                                <img src="<?php echo htmlspecialchars($g['file_path']); ?>" alt="Preview" style="width:80px; height:80px; object-fit:cover; border-radius:8px; cursor:pointer;" onclick="openPreviewModal('<?php echo $jsFilePath; ?>', '<?php echo $jsRole; ?>', '<?php echo $jsAngkatan; ?>', '<?php echo $jsAspirasi; ?>', '<?php echo $jsDate; ?>')">
                            <?php else: ?>
                                <i class="fa-solid fa-file" style="font-size:2.5rem; color:var(--accent); cursor:pointer;" onclick="openPreviewModal('<?php echo $jsFilePath; ?>', '<?php echo $jsRole; ?>', '<?php echo $jsAngkatan; ?>', '<?php echo $jsAspirasi; ?>', '<?php echo $jsDate; ?>')"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($fileName); ?></td>
                        <td><span class="badge" style="background:var(--soft-accent); color:white;"><?php echo htmlspecialchars($g['role']); ?></span></td>
                        <td><?php echo htmlspecialchars($g['angkatan'] ?: '-'); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($g['created_at'])); ?></td>
                        <td>
                            <button onclick="openPreviewModal('<?php echo $jsFilePath; ?>', '<?php echo $jsRole; ?>', '<?php echo $jsAngkatan; ?>', '<?php echo $jsAspirasi; ?>', '<?php echo $jsDate; ?>')" class="btn btn-primary btn-small" style="padding:8px 16px; font-size:0.8rem;"><i class="fa-solid fa-eye"></i> Preview</button>
                            <a href="<?php echo htmlspecialchars($g['file_path']); ?>" download class="btn btn-small" style="padding:8px 16px; font-size:0.8rem; background:linear-gradient(180deg, #2ECC71 0%, #27AE60 100%); color:white; box-shadow:0 3px 0 #1E8449;"><i class="fa-solid fa-download"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:2000; align-items:center; justify-content:center; animation:fadeIn 0.3s ease;" onclick="closePreviewModal(event)">
        <div style="background:var(--card-bg); border-radius:var(--radius-card); max-width:900px; width:90%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.5); position:relative;" onclick="event.stopPropagation();">
            <button onclick="closePreviewModal(event)" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:1.5rem; color:#6B7280; cursor:pointer; z-index:10; transition:color 0.2s;" onmouseover="this.style.color='#EF4444'" onmouseout="this.style.color='#6B7280'"><i class="fa-solid fa-xmark"></i></button>
            
            <div style="padding:30px;">
                <h3 style="font-family:'Ranchers',sans-serif; font-size:1.3rem; margin-bottom:1.5rem; color:var(--text-dark);">Preview Lampiran</h3>
                
                <!-- File Preview -->
                <div id="modalPreviewContent" style="text-align:center; margin-bottom:1.5rem; background:#F9FAFB; border-radius:12px; padding:20px; min-height:200px; display:flex; align-items:center; justify-content:center;">
                </div>
                
                <!-- Survey Origin Info -->
                <div style="background:linear-gradient(180deg, #FFF5E6 0%, #FFE4B5 100%); border-radius:12px; padding:20px; border:2px solid #FFD700;">
                    <h4 style="font-family:'Ranchers',sans-serif; font-size:1.1rem; color:#7C5A1A; margin-bottom:1rem;"><i class="fa-solid fa-database"></i> Asal Survey</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="font-size:0.8rem; color:#6B7280; display:block; margin-bottom:4px;">Peran Responden</label>
                            <p id="modalRole" style="font-weight:600; color:var(--text-dark);">-</p>
                        </div>
                        <div>
                            <label style="font-size:0.8rem; color:#6B7280; display:block; margin-bottom:4px;">Angkatan</label>
                            <p id="modalAngkatan" style="font-weight:600; color:var(--text-dark);">-</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:0.8rem; color:#6B7280; display:block; margin-bottom:4px;">Tanggal Upload</label>
                            <p id="modalDate" style="font-weight:600; color:var(--text-dark);">-</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:0.8rem; color:#6B7280; display:block; margin-bottom:4px;">Aspirasi</label>
                            <p id="modalAspirasi" style="font-weight:600; color:var(--text-dark); white-space:pre-wrap; background:white; padding:12px; border-radius:8px;">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: AI Analysis -->
    <div id="ai-analysis" class="section">
        <h3 style="font-family:'Ranchers',sans-serif; font-size:1.5rem; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.2); margin-bottom:1.5rem;">
            <i class="fa-solid fa-brain"></i> AI Analysis - Kategorisasi Aspirasi
        </h3>
        
        <div class="stats-grid" style="margin-bottom:2rem;">
            <div class="stat-card">
                <h3>Total Dianalisis</h3>
                <div class="value" id="totalAnalyzed">0</div>
            </div>
            <div class="stat-card">
                <h3>Belum Dianalisis</h3>
                <div class="value" id="notAnalyzed">0</div>
            </div>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h3 style="font-size:1.1rem;"><i class="fa-solid fa-chart-pie"></i> Distribusi Kategori</h3>
                <button onclick="runAIAnalysis()" class="btn btn-primary btn-small" id="analyzeBtn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Analisis dengan AI
                </button>
            </div>
            <div style="height:350px; max-width:600px; margin:0 auto;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3 style="font-size:1.1rem; margin-bottom:1rem;"><i class="fa-solid fa-list-check"></i> Hasil Analisis Terbaru</h3>
            <div style="overflow-x:auto;">
                <table id="analysisTable">
                    <thead>
                        <tr>
                            <th>Aspirasi</th>
                            <th>Kategori</th>
                            <th>Kepercayaan AI</th>
                            <th>Penjelasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#9CA3AF;">Belum ada data analisis. Klik tombol "Analisis dengan AI" untuk memulai.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section: Settings -->
    <div id="settings" class="section">
        <h3 style="font-family:'Ranchers',sans-serif; font-size:1.5rem; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.2); margin-bottom:1.5rem;">Pengaturan Survey</h3>
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Status Survey Saat Ini: <strong style="color:var(--accent);"><?php echo strtoupper($surveyStatus); ?></strong></label>
                    <select name="status" class="form-control" style="max-width:200px;">
                        <option value="open" <?php echo $surveyStatus === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo $surveyStatus === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <button type="submit" name="toggle_survey" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <!-- Section: Admins -->
    <div id="admins" class="section">
        <h3 style="font-family:'Ranchers',sans-serif; font-size:1.5rem; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.2); margin-bottom:1.5rem;">Kelola Admin</h3>
        <div class="card">
            <h3 style="font-size:1.1rem;">Tambah Admin Baru</h3>
            <form method="POST" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:150px;">
                    <label>Username</label>
                    <input type="text" name="new_username" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1; min-width:150px;">
                    <label>Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1; min-width:150px;">
                    <label>Role</label>
                    <select name="new_role" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_admin" class="btn btn-primary btn-small" style="margin-bottom:0;">Tambah</button>
            </form>
        </div>
        <div class="card">
            <h3 style="font-size:1.1rem;">Daftar Admin</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>Username</th><th>Role</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($admins as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['username']); ?></td>
                                <td><?php echo strtoupper($a['role']); ?></td>
                                <td>
                                    <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                                        <a href="?delete_admin=<?php echo $a['id']; ?>" onclick="return confirm('Hapus admin ini?')" class="action-link danger"><i class="fa-solid fa-trash"></i> Hapus</a>
                                    <?php else: ?>
                                        <span style="color:#9CA3AF; font-size:0.85rem;">Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section: Logs -->
    <div id="logs" class="section">
        <h3 style="font-family:'Ranchers',sans-serif; font-size:1.5rem; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.2); margin-bottom:1.5rem;">Log Aktivitas Admin</h3>
        <div class="card" style="padding:0; overflow-x: auto;">
            <table>
                <thead><tr><th>Waktu</th><th>Admin</th><th>Aksi</th><th>Detail</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($l['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($l['username']); ?></td>
                            <td><?php echo htmlspecialchars($l['action']); ?></td>
                            <td><?php echo htmlspecialchars($l['details']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    function showSection(id) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        event.currentTarget.classList.add('active');
        
        // Auto close sidebar on mobile
        if (window.innerWidth <= 768) {
            toggleSidebar(false);
        }
    }

    function toggleSidebar(show = null) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        if (show === null) {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        } else if (show === true) {
            sidebar.classList.add('open');
            overlay.classList.add('active');
        } else {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
    }

    function openPreviewModal(filePath, role, angkatan, aspirasi, date) {
        const modal = document.getElementById('previewModal');
        const previewContent = document.getElementById('modalPreviewContent');
        const modalRole = document.getElementById('modalRole');
        const modalAngkatan = document.getElementById('modalAngkatan');
        const modalDate = document.getElementById('modalDate');
        const modalAspirasi = document.getElementById('modalAspirasi');
        
        // Set survey origin info
        modalRole.textContent = role;
        modalAngkatan.textContent = angkatan;
        modalDate.textContent = new Date(date).toLocaleString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        modalAspirasi.textContent = aspirasi || 'Tidak ada aspirasi';
        
        // Check if file is an image
        const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(filePath);
        
        if (isImage) {
            previewContent.innerHTML = `<img src="${filePath}" alt="Preview" style="max-width:100%; max-height:400px; border-radius:12px; object-fit:contain;">`;
        } else {
            // For non-image files, show embed or download link
            const fileExt = filePath.split('.').pop().toLowerCase();
            if (fileExt === 'pdf') {
                previewContent.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" height="400px" style="border-radius:12px;">`;
            } else {
                previewContent.innerHTML = `
                    <div style="text-align:center;">
                        <i class="fa-solid fa-file" style="font-size:4rem; color:var(--accent); margin-bottom:1rem;"></i>
                        <p style="color:#6B7280; margin-bottom:1rem;">File tidak dapat ditampilkan sebagai preview</p>
                        <a href="${filePath}" download class="btn btn-primary btn-small"><i class="fa-solid fa-download"></i> Download File</a>
                    </div>
                `;
            }
        }
        
        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closePreviewModal(event) {
        if (event) {
            event.stopPropagation();
        }
        const modal = document.getElementById('previewModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreviewModal();
        }
    });

    // ==================== AI ANALYSIS FUNCTIONS ====================
    let categoryChartInstance = null;

    // Override showSection to load AI stats when AI section is shown
    const originalShowSection = showSection;
    showSection = function(id) {
        originalShowSection(id);
        if (id === 'ai-analysis') {
            loadAIStats();
        }
    };

    function loadAIStats() {
        fetch('ai_analysis.php?action=stats')
            .then(response => response.json())
            .then(data => {
                if (data.error) return;
                
                document.getElementById('totalAnalyzed').textContent = data.total.analyzed || 0;
                document.getElementById('notAnalyzed').textContent = data.total.not_analyzed || 0;
                
                renderCategoryChart(data.category);
                renderAnalysisTable(data.recent);
            })
            .catch(error => console.error('Error loading AI stats:', error));
    }

    function renderCategoryChart(categoryData) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        if (categoryChartInstance) categoryChartInstance.destroy();
        
        const categoryLabels = {
            'kesiswaan': 'Kesiswaan', 'kurikulum': 'Kurikulum', 'humas': 'Humas',
            'sarana_prasarana': 'Sarana Prasarana', 'lainnya': 'Lainnya'
        };
        const categoryColors = {
            'kesiswaan': '#3B82F6', 'kurikulum': '#8B5CF6', 'humas': '#F59E0B',
            'sarana_prasarana': '#10B981', 'lainnya': '#6B7280'
        };
        
        const labels = categoryData.map(item => categoryLabels[item.category] || item.category);
        const colors = categoryData.map(item => categoryColors[item.category] || '#6B7280');
        const values = categoryData.map(item => item.count);
        
        categoryChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Aspirasi',
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Distribusi Kategori', font: { size: 16 } }
                },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    function renderAnalysisTable(recentData) {
        const tbody = document.querySelector('#analysisTable tbody');
        if (!recentData || recentData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#9CA3AF;">Belum ada data analisis.</td></tr>';
            return;
        }
        
        const categoryLabels = {
            'kesiswaan': '<span class="badge" style="background:#3B82F6; color:white;">Kesiswaan</span>',
            'kurikulum': '<span class="badge" style="background:#8B5CF6; color:white;">Kurikulum</span>',
            'humas': '<span class="badge" style="background:#F59E0B; color:white;">Humas</span>',
            'sarana_prasarana': '<span class="badge" style="background:#10B981; color:white;">Sarana Prasarana</span>',
            'lainnya': '<span class="badge" style="background:#6B7280; color:white;">Lainnya</span>'
        };
        
        tbody.innerHTML = recentData.map(item => `
            <tr>
                <td style="max-width:300px;">${escapeHtml(item.aspirasi)}</td>
                <td>${categoryLabels[item.category] || '-'}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:60px; height:8px; background:#E5E7EB; border-radius:4px; overflow:hidden;">
                            <div style="width:${(item.ai_confidence || 0) * 100}%; height:100%; background:var(--accent); border-radius:4px;"></div>
                        </div>
                        <span>${Math.round((item.ai_confidence || 0) * 100)}%</span>
                    </div>
                </td>
                <td style="font-size:0.85rem; color:#6B7280;">${escapeHtml(item.ai_explanation) || '-'}</td>
            </tr>
        `).join('');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function runAIAnalysis() {
        const btn = document.getElementById('analyzeBtn');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menganalisis...';
        
        fetch('ai_analysis.php?action=analyze')
            .then(response => {
                // Check if response is OK (status 200-299)
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    // Show detailed error message from server
                    let errorMsg = 'Error: ' + data.error;
                    if (data.details) {
                        errorMsg += '\n\nDetails: ' + data.details;
                    }
                    alert(errorMsg);
                } else {
                    alert(`Analisis selesai! ${data.analyzed_count} aspirasi telah dianalisis.`);
                    loadAIStats();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show more specific error message
                let errorMsg = 'Terjadi kesalahan saat menganalisis.\n\n';
                if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                    errorMsg += 'Kesalahan jaringan. Pastikan server berjalan dan file ai_analysis.php dapat diakses.';
                } else if (error.message.includes('Unexpected token')) {
                    errorMsg += 'Respons server tidak valid. Periksa log error PHP untuk detail.';
                } else {
                    errorMsg += error.message;
                }
                alert(errorMsg);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
    }
</script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</body>
</html>
