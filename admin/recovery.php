<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/admin_auth.php';

$success = false;
$emailEntered = '';
$resetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailEntered = trim($_POST['email'] ?? '');
    if (!empty($emailEntered)) {
        $success = true;
        $resetUrl = 'reset-password.php?token=' . bin2hex(random_bytes(16)) . '&email=' . urlencode($emailEntered);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Credential Recovery Enclave — VELORA Atelier Portal</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

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
            secondary: "#745a34",
            "secondary-container": "#fedaaa",
            "on-secondary-container": "#785e37",
            tertiary: "#2d2d2a",
            surface: "#fef8f4",
            "surface-container-lowest": "#ffffff",
            "surface-container-low": "#f9f2ef",
            "surface-container": "#f3ede9",
            "surface-container-high": "#ede7e3",
            "on-surface": "#1d1b19",
            "on-surface-variant": "#534345",
            outline: "#867275",
            "outline-variant": "#d8c1c3"
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

  <!-- Header -->
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl border-b border-surface-container-high/60 shadow-sm">
    <div class="h-20 max-w-[1440px] mx-auto px-6 md:px-12 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="login.php" class="flex items-center gap-3">
          <img alt="VELORA Logo" class="h-8 w-auto object-contain" src="<?= htmlspecialchars(veloraImageUrl('assets/img/velora-logo.svg')) ?>"/>
          <div class="flex flex-col">
            <span class="font-serif text-2xl text-primary font-medium tracking-widest uppercase">VELORA</span>
            <span class="text-[10px] tracking-widest text-secondary uppercase font-semibold">Atelier Executive Portal</span>
          </div>
        </a>
      </div>

      <nav class="flex items-center gap-4">
        <a class="text-xs uppercase tracking-widest text-primary hover:text-primary-container font-semibold flex items-center gap-1" href="login.php">
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          Back to Login
        </a>
      </nav>
    </div>
  </header>

  <main class="w-full pt-28 pb-16 flex-1 flex flex-col justify-center">
    <div class="w-full max-w-[1440px] mx-auto px-6 md:px-12">
      
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            <span class="text-[10px] uppercase tracking-widest text-secondary font-semibold">HSM Session Auth • Protocol Node #8842-REV</span>
          </div>
          <h1 class="font-serif text-3xl md:text-4xl text-primary font-medium">Credential Recovery Enclave</h1>
          <p class="text-xs text-on-surface-variant max-w-xl mt-1">Secure cryptographic self-service identity restoration for atelier managers and master couturiers.</p>
        </div>
        <a class="text-xs uppercase tracking-widest text-primary font-semibold hover:underline" href="login.php">Return to Sign In</a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
        
        <!-- Left Panel: Protocol Stages -->
        <aside class="lg:col-span-5 bg-surface-container rounded-2xl p-8 shadow-sm flex flex-col justify-between relative overflow-hidden">
          <div class="space-y-6 relative z-10">
            <div class="flex items-center justify-between">
              <span class="text-[10px] tracking-widest text-secondary uppercase bg-surface-container-high px-3 py-1 rounded font-semibold">
                CREDENTIAL DISPATCH // ATELIER SECURITY PROTOCOL
              </span>
              <span class="text-[10px] text-outline uppercase font-mono">#SEC-RECOVERY-8842</span>
            </div>

            <div class="bg-surface-container-lowest/80 backdrop-blur-sm p-4 rounded-xl shadow-sm border border-surface-container-high/60">
              <p class="font-serif italic text-sm text-tertiary leading-relaxed">
                “Authorized access to the VELORA Haute Couture archive and atelier production telemetry is safeguarded by cryptographic token verification.”
              </p>
              <div class="mt-2 flex items-center justify-between text-[10px] uppercase tracking-widest text-secondary font-semibold">
                <span>Maison Velora Security Charter</span>
                <span>Sec. 14.3.B</span>
              </div>
            </div>

            <div class="space-y-3">
              <span class="text-[10px] text-secondary uppercase tracking-widest block font-bold">Identity Re-establishment Stages</span>
              
              <div class="flex items-start gap-3 bg-surface-container-lowest p-3 rounded-xl shadow-sm">
                <div class="w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center text-xs font-bold shrink-0">1</div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-primary">Identity Verification</span>
                    <span class="bg-secondary-container text-on-secondary-container text-[9px] px-1.5 py-0.5 rounded uppercase font-bold">Current</span>
                  </div>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">Enter registered admin username or enterprise work email.</p>
                </div>
              </div>

              <div class="flex items-start gap-3 p-3">
                <div class="w-6 h-6 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-bold shrink-0">2</div>
                <div>
                  <span class="text-xs font-semibold text-tertiary">Cryptographic Reset Token</span>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">One-time reset URL generated instantly for verification.</p>
                </div>
              </div>

              <div class="flex items-start gap-3 p-3">
                <div class="w-6 h-6 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-bold shrink-0">3</div>
                <div>
                  <span class="text-xs font-semibold text-tertiary">Passphrase Generation</span>
                  <p class="text-[11px] text-on-surface-variant mt-0.5">Enforce minimum 8 characters with security encryption.</p>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Right Panel: Recovery Form or Success Card -->
        <div class="lg:col-span-7 bg-surface-container-lowest p-8 md:p-12 rounded-2xl shadow-xl border border-surface-container-high/60 flex flex-col justify-center">
          
          <?php if ($success): ?>
          <div class="flex flex-col gap-5 p-6 bg-secondary-container/20 border border-secondary/30 rounded-2xl">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">mark_email_read</span>
              </div>
              <div>
                <span class="text-[10px] uppercase tracking-widest text-secondary font-bold">Token Dispatched</span>
                <h3 class="font-serif text-2xl text-on-surface">Authorization Link Transmitted</h3>
              </div>
            </div>
            
            <p class="text-xs text-on-surface-variant leading-relaxed">
              A cryptographic identity restoration ticket has been generated for <strong><?= htmlspecialchars($emailEntered) ?></strong>.
              In this development environment, you can proceed directly to create your new master passphrase below:
            </p>

            <a href="<?= htmlspecialchars($resetUrl) ?>" class="mt-2 w-full py-4 px-6 bg-primary hover:bg-primary-container text-on-primary text-center rounded-xl font-semibold text-xs uppercase tracking-widest transition-colors shadow-md flex items-center justify-center gap-2">
              <span>Proceed to Set New Master Passphrase</span>
              <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
          </div>

          <?php else: ?>

          <div class="flex items-center gap-3 pb-4 mb-4 border-b border-surface-container-high/60">
            <div class="w-12 h-12 rounded-full bg-secondary-container/50 flex items-center justify-center text-primary">
              <span class="material-symbols-outlined text-[24px]">vpn_key</span>
            </div>
            <div>
              <span class="text-[10px] uppercase tracking-widest text-secondary font-bold block">Stage 1 of 3</span>
              <h2 class="font-serif text-2xl text-on-surface">Reset Master Password</h2>
            </div>
          </div>

          <p class="text-xs text-on-surface-variant mb-6 leading-relaxed">
            Please enter your authorized corporate email address. Our security enclave will verify the directory record and generate your reset passphrase token.
          </p>

          <form action="recovery.php" method="POST" class="flex flex-col gap-5">
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">Enterprise Email Address</label>
              <div class="relative flex items-center">
                <div class="absolute left-3.5 text-secondary">
                  <span class="material-symbols-outlined text-[20px]">mail</span>
                </div>
                <input class="w-full bg-surface-container-low text-on-surface text-sm pl-11 pr-4 py-3.5 rounded-lg focus:outline-none border border-outline-variant/60 focus:border-primary focus:bg-surface transition-all shadow-sm" name="email" placeholder="admin@velora.com" required="" type="email" value="admin@velora.com"/>
              </div>
            </div>

            <button class="w-full bg-primary hover:bg-primary-container text-on-primary py-4 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-3 text-xs uppercase tracking-[0.16em] font-semibold group cursor-pointer" type="submit">
              <span>Transmit Reset Token</span>
              <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">send</span>
            </button>
          </form>

          <?php endif; ?>

        </div>
      </div>

    </div>
  </main>
</body>
</html>
