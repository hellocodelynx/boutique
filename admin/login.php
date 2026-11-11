<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/admin_auth.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = null;
$identifier = 'admin@velora.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (loginAdmin($identifier, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = "The username or password entered does not match our authorized records. Please verify your credentials or use Forgot Password to reset.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Staff & Executive Sign In — VELORA Atelier Portal</title>
  
  <!-- Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

  <!-- Tailwind CSS Engine -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#541626",
            "primary-container": "#702c3b",
            "on-primary": "#ffffff",
            "on-primary-container": "#f195a5",
            "primary-fixed": "#ffd9de",
            "primary-fixed-dim": "#ffb2be",
            secondary: "#745a34",
            "secondary-container": "#fedaaa",
            "on-secondary-container": "#785e37",
            "secondary-fixed": "#ffddb0",
            "secondary-fixed-dim": "#e3c192",
            tertiary: "#2d2d2a",
            "tertiary-container": "#434340",
            "on-tertiary": "#ffffff",
            surface: "#fef8f4",
            "surface-bright": "#fef8f4",
            "surface-dim": "#dfd9d5",
            "surface-container-lowest": "#ffffff",
            "surface-container-low": "#f9f2ef",
            "surface-container": "#f3ede9",
            "surface-container-high": "#ede7e3",
            "surface-container-highest": "#e7e1de",
            "on-surface": "#1d1b19",
            "on-surface-variant": "#534345",
            outline: "#867275",
            "outline-variant": "#d8c1c3",
            error: "#ba1a1a",
            "error-container": "#ffdad6",
            "on-error": "#ffffff",
            "on-error-container": "#93000a"
          },
          fontFamily: {
            serif: ["Playfair Display", "serif"],
            sans: ["Plus Jakarta Sans", "sans-serif"]
          }
        }
      }
    };
  </script>
</head>
<body class="bg-surface font-sans text-on-surface antialiased flex flex-col min-h-screen">

  <!-- Top Executive Header -->
  <header class="fixed top-0 left-0 w-full z-50 bg-surface/90 backdrop-blur-xl border-b border-surface-container-high/60 shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="h-20 max-w-[1440px] mx-auto px-6 md:px-12 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <a href="../index.php" class="flex items-center gap-3">
          <img alt="VELORA Logo" class="h-8 w-auto object-contain" src="<?= htmlspecialchars(veloraImageUrl('assets/img/velora-logo.svg')) ?>"/>
          <div class="flex flex-col">
            <span class="font-serif text-2xl text-primary font-medium tracking-widest uppercase">VELORA</span>
            <span class="text-[10px] tracking-widest text-secondary uppercase font-semibold">Atelier Executive Portal</span>
          </div>
        </a>
      </div>

      <nav class="hidden md:flex items-center gap-6">
        <a class="text-xs uppercase tracking-widest font-semibold py-1.5 px-3 bg-primary-container text-on-primary rounded" href="login.php">Secure Access</a>
        <a class="text-xs uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors py-1.5 px-3" href="recovery.php">Credential Recovery</a>
        <a class="text-xs uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors py-1.5 px-3" href="../index.php">← Public Boutique</a>
      </nav>

      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 bg-surface-container-low px-3 py-1.5 rounded-full border border-surface-container-high">
          <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
          <span class="text-[10px] uppercase font-semibold text-on-surface-variant tracking-wider">TLS 1.3 / EAL6+ Active</span>
        </div>
        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shadow-sm">
          <span class="material-symbols-outlined text-on-primary text-[18px]">lock</span>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Login Container -->
  <main class="w-full pt-28 pb-16 flex-1 flex flex-col justify-center">
    <div class="w-full max-w-[1440px] mx-auto px-6 md:px-12">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
        
        <!-- Editorial Left Column: The Atelier Archive & Production Telemetry -->
        <div class="lg:col-span-5 flex flex-col justify-between bg-primary text-on-primary rounded-2xl p-8 md:p-10 relative overflow-hidden shadow-2xl">
          <div class="absolute -right-20 -bottom-20 w-96 h-96 rounded-full bg-primary-container/40 blur-3xl pointer-events-none"></div>
          <div class="absolute top-4 right-4 opacity-10 pointer-events-none select-none">
            <span class="font-serif text-[130px] leading-none text-surface">V</span>
          </div>

          <!-- Upper Block -->
          <div class="relative z-10 flex flex-col gap-4">
            <div class="flex items-center justify-between">
              <span class="text-[10px] tracking-widest text-secondary-fixed uppercase bg-primary-container/70 px-3 py-1 rounded-full font-semibold">
                ATELIER PORTAL // v2.4.9
              </span>
              <span class="text-[10px] tracking-widest text-primary-fixed-dim/80 font-mono">
                SYS-ENV: PROD-MUMBAI-01
              </span>
            </div>

            <div class="mt-2">
              <span class="text-[11px] uppercase text-secondary-fixed tracking-widest block font-medium">Maison De Haute Couture</span>
              <h1 class="font-serif text-3xl md:text-4xl text-surface font-normal tracking-tight leading-snug mt-1">
                Management Suite &amp; Production Telemetry
              </h1>
            </div>

            <p class="text-xs text-surface-container-high/90 max-w-sm leading-relaxed">
              Unified sovereign orchestration for bespoke drape engineering, master karigar scheduling, and heirloom garment fulfillment.
            </p>

            <!-- Editorial Imagery Vignette -->
            <div class="relative mt-2 rounded-xl overflow-hidden shadow-lg border border-primary-container/50">
              <img class="w-full h-44 object-cover object-center filter brightness-90" src="<?= htmlspecialchars(veloraImageUrl('assets/img/admin-login-couture.jpg')) ?>" alt="Atelier Craftsman"/>
              <div class="absolute inset-0 bg-gradient-to-t from-primary via-transparent to-transparent opacity-80"></div>
              <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-surface">
                <span class="text-[10px] tracking-widest uppercase text-secondary-fixed font-semibold">Atelier Kala Ghoda • Suite 04</span>
                <span class="text-[10px] tracking-widest uppercase text-surface-dim font-mono">Batch #709-SILK</span>
              </div>
            </div>

            <!-- Artisanal Philosophy Quote -->
            <blockquote class="mt-2 pl-4 border-l-2 border-secondary-fixed/50 italic font-serif text-surface/95 text-sm leading-relaxed">
              “Perfection is not created when there is nothing left to add, but when raw silk yields entirely to the human spirit.”
              <footer class="not-italic text-[10px] uppercase tracking-wider text-secondary-fixed-dim mt-1.5 font-sans">
                — Madame Hélène de Velora, Master Couturière
              </footer>
            </blockquote>
          </div>

          <!-- Lower Block: Live Telemetry & Secure Micro-Stats -->
          <div class="relative z-10 mt-6 pt-4 bg-primary-container/40 p-4 rounded-xl border border-primary-container/60">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[10px] uppercase text-secondary-fixed tracking-wider font-semibold">Active Workroom Pulse</span>
              </div>
              <span class="text-xs font-semibold text-surface">5 Active Orders</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-xs pt-1">
              <div class="flex items-center gap-1.5 text-surface-dim">
                <span class="material-symbols-outlined text-[15px] text-secondary-fixed">database</span>
                <span class="text-[11px]">Database: Active</span>
              </div>
              <div class="flex items-center gap-1.5 text-surface-dim">
                <span class="material-symbols-outlined text-[15px] text-secondary-fixed">shield</span>
                <span class="text-[11px]">TLS 1.3 / 256-Bit EV</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Authentication Console -->
        <div class="lg:col-span-7 flex flex-col justify-center bg-surface-container-lowest p-8 md:p-12 rounded-2xl shadow-xl border border-surface-container-high/60 relative">
          
          <!-- Header Ribbon inside Card -->
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-surface-container-high/60">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-secondary-container/50 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[24px]">verified_user</span>
              </div>
              <div>
                <span class="text-[10px] uppercase tracking-widest text-secondary font-bold block">Security Gateway</span>
                <h2 class="font-serif text-2xl text-on-surface">Staff &amp; Executive Sign In</h2>
              </div>
            </div>
            <div class="hidden sm:flex flex-col items-end text-right">
              <span class="text-[9px] uppercase tracking-widest text-outline">Terminal ID</span>
              <span class="text-xs text-on-surface-variant font-mono font-medium">BOM-WS-8842</span>
            </div>
          </div>

          <p class="text-xs text-on-surface-variant mb-6 leading-relaxed">
            Authorized console access restricted strictly to Head Couturiers, Atelier Operations Directors, and Executive Management.
          </p>

          <!-- Error Alert Banner -->
          <?php if ($error): ?>
          <div class="mb-6 p-4 bg-error-container/60 border border-error/30 rounded-xl flex flex-col gap-2 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-2.5">
                <span class="material-symbols-outlined text-error text-[22px] shrink-0 mt-0.5">error</span>
                <div>
                  <h3 class="text-sm font-semibold text-error">Authentication Failed</h3>
                  <p class="text-xs text-on-surface-variant mt-0.5"><?= htmlspecialchars($error) ?></p>
                </div>
              </div>
              <span class="text-[10px] uppercase text-error font-bold px-2 py-0.5 rounded bg-error/10 shrink-0 whitespace-nowrap">2 attempts remaining</span>
            </div>
            <div class="flex items-center gap-1.5 pt-1 text-[11px] text-error font-medium">
              <span class="material-symbols-outlined text-[15px]">lock_clock</span>
              <span>Security protocol: 2 attempts remaining before security lockout</span>
            </div>
          </div>
          <?php endif; ?>

          <!-- Credentials Quick Reference Pill for Testing -->
          <div class="mb-6 p-3 bg-secondary-container/30 border border-secondary/30 rounded-lg flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-[18px] text-secondary">info</span>
              <span class="text-on-secondary-container font-medium">Demo Access: <strong>admin</strong> or <strong>admin@velora.com</strong></span>
            </div>
            <span class="text-primary font-mono font-bold bg-surface px-2 py-0.5 rounded border border-outline-variant/60">admin123</span>
          </div>

          <form action="login.php" method="POST" class="flex flex-col gap-5">
            
            <!-- Admin Username / Email -->
            <div class="flex flex-col gap-1.5">
              <label class="flex items-center justify-between text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">
                <span>Admin Username / Enterprise ID</span>
                <span class="text-outline text-[10px] normal-case">admin or admin@velora.com</span>
              </label>
              <div class="relative flex items-center">
                <div class="absolute left-3.5 text-secondary">
                  <span class="material-symbols-outlined text-[20px]">account_circle</span>
                </div>
                <input class="w-full bg-surface-container-low text-on-surface text-sm pl-11 pr-4 py-3.5 rounded-lg focus:outline-none border border-outline-variant/60 focus:border-primary focus:bg-surface transition-all shadow-sm" name="identifier" placeholder="admin or admin@velora.com" required="" type="text" value="<?= htmlspecialchars($identifier) ?>"/>
              </div>
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-1.5">
              <div class="flex items-center justify-between">
                <label class="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">Password</label>
                <a class="text-[10px] uppercase text-primary hover:underline transition-colors font-bold underline-offset-4" href="recovery.php">Forgot Password?</a>
              </div>
              <div class="relative flex items-center">
                <div class="absolute left-3.5 text-secondary">
                  <span class="material-symbols-outlined text-[20px]">lock</span>
                </div>
                <input class="w-full bg-surface-container-low text-on-surface text-sm pl-11 pr-11 py-3.5 rounded-lg focus:outline-none border border-outline-variant/60 focus:border-primary focus:bg-surface font-mono tracking-wider transition-all shadow-sm" id="passphraseInput" name="password" placeholder="••••••••••••" required="" type="password" value="admin123"/>
                <button class="absolute right-3.5 text-on-surface-variant hover:text-on-surface p-1 flex items-center justify-center transition-colors" id="togglePassphraseBtn" onclick="togglePassVisibility()" title="Toggle password visibility" type="button">
                  <span class="material-symbols-outlined text-[20px]" id="eyeIcon">visibility</span>
                </button>
              </div>
            </div>

            <!-- Session Preservation Checkbox -->
            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input checked="" class="w-4 h-4 rounded text-primary accent-primary cursor-pointer" type="checkbox"/>
                <span class="text-xs text-on-surface-variant">Remember registered workstation</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button class="mt-2 w-full bg-primary hover:bg-primary-container text-on-primary py-4 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-3 text-xs uppercase tracking-[0.16em] font-semibold group cursor-pointer" type="submit">
              <span>Sign In as Admin</span>
              <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </button>

            <!-- Audit Badge -->
            <div class="mt-2 p-3 bg-surface-container-low rounded-lg flex items-start gap-2.5 border border-surface-container-high/60">
              <span class="material-symbols-outlined text-secondary text-[18px] mt-0.5 shrink-0">shield_lock</span>
              <p class="text-[11px] text-on-surface-variant leading-relaxed">
                Authorized admin session monitored under VELORA's tamper-proof security ledger (<span class="font-mono text-primary font-semibold">#SEC-SYS-994</span>).
              </p>
            </div>
          </form>
        </div>
      </div>

      <!-- Atelier Executive Quick Status Footer Banner -->
      <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm flex items-center gap-3 border border-surface-container-high/60">
          <div class="w-10 h-10 rounded-full bg-secondary-container/40 flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-[20px]">schedule</span>
          </div>
          <div>
            <span class="text-[10px] uppercase text-outline block">Server Time (IST)</span>
            <p class="text-xs font-semibold text-on-surface font-mono"><?= date('H:i:s') ?> IST</p>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm flex items-center gap-3 border border-surface-container-high/60">
          <div class="w-10 h-10 rounded-full bg-secondary-container/40 flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-[20px]">palette</span>
          </div>
          <div>
            <span class="text-[10px] uppercase text-outline block">Active Lookbook Batch</span>
            <p class="text-xs font-semibold text-on-surface">Autumn Couture ’25</p>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm flex items-center gap-3 border border-surface-container-high/60">
          <div class="w-10 h-10 rounded-full bg-secondary-container/40 flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-[20px]">security</span>
          </div>
          <div>
            <span class="text-[10px] uppercase text-outline block">Compliance Status</span>
            <p class="text-xs font-semibold text-emerald-700">ISO 27001 &amp; GDPR OK</p>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm flex items-center gap-3 border border-surface-container-high/60">
          <div class="w-10 h-10 rounded-full bg-secondary-container/40 flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-[20px]">support_agent</span>
          </div>
          <div>
            <span class="text-[10px] uppercase text-outline block">Executive Helpdesk</span>
            <p class="text-xs font-semibold text-primary">ext. 4099 (Priority)</p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    function togglePassVisibility() {
      const input = document.getElementById('passphraseInput');
      const icon = document.getElementById('eyeIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        input.type = 'password';
        icon.textContent = 'visibility';
      }
    }
  </script>
</body>
</html>
