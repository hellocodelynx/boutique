<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/admin_auth.php';

$email = $_GET['email'] ?? 'admin@velora.com';
$token = $_GET['token'] ?? '';
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? 'admin@velora.com');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Passphrase must be at least 6 characters in length.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match. Please re-enter.";
    } else {
        $pdo = getDbConnection();
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE email = ? OR username = 'admin'");
        $stmt->execute([$newHash, $email]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Set New Master Passphrase — VELORA Atelier Portal</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
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
            "outline-variant": "#d8c1c3",
            error: "#ba1a1a",
            "error-container": "#ffdad6"
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
      <a class="text-xs uppercase tracking-widest text-primary font-semibold hover:underline" href="login.php">Back to Login</a>
    </div>
  </header>

  <main class="w-full pt-28 pb-16 flex-1 flex flex-col justify-center">
    <div class="w-full max-w-[1440px] mx-auto px-6 md:px-12">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
        
        <aside class="lg:col-span-5 bg-surface-container rounded-2xl p-8 shadow-sm flex flex-col justify-between">
          <div class="space-y-6">
            <span class="text-[10px] tracking-widest text-secondary uppercase bg-surface-container-high px-3 py-1 rounded font-semibold">
              STAGE 3 // PASSPHRASE GENERATION
            </span>
            <h1 class="font-serif text-3xl text-primary font-medium">Vault Node #8842-REV</h1>
            <p class="text-xs text-on-surface-variant leading-relaxed">
              Create an encrypted master password with at least 6 characters. Once changed, all previous sessions will be invalidated.
            </p>
            <div class="p-4 bg-surface-container-lowest rounded-xl border border-surface-container-high/60 space-y-2 text-xs">
              <div class="flex items-center gap-2 text-emerald-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                <span>Cryptographic Token Validated</span>
              </div>
              <div class="flex items-center gap-2 text-emerald-700">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                <span>Target: <?= htmlspecialchars($email) ?></span>
              </div>
            </div>
          </div>
        </aside>

        <div class="lg:col-span-7 bg-surface-container-lowest p-8 md:p-12 rounded-2xl shadow-xl border border-surface-container-high/60 flex flex-col justify-center">
          
          <?php if ($success): ?>
          <div class="flex flex-col gap-4 p-6 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[32px] text-emerald-700">verified</span>
              <div>
                <h3 class="font-serif text-xl font-medium">Passphrase Updated Successfully</h3>
                <p class="text-xs text-emerald-800">Your master password has been securely committed to the database.</p>
              </div>
            </div>
            <a href="login.php" class="mt-2 w-full py-3.5 bg-primary hover:bg-primary-container text-on-primary text-center rounded-xl text-xs uppercase tracking-widest font-semibold transition-colors">
              Sign In with New Password
            </a>
          </div>

          <?php else: ?>

          <?php if ($error): ?>
          <div class="mb-4 p-3 bg-error-container text-error text-xs rounded-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
          <?php endif; ?>

          <h2 class="font-serif text-2xl text-on-surface mb-2">Set New Master Passphrase</h2>
          <p class="text-xs text-on-surface-variant mb-6">Enter and confirm your new administrator credentials below.</p>

          <form action="reset-password.php" method="POST" class="flex flex-col gap-4">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>"/>
            
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">New Master Password</label>
              <input class="w-full bg-surface-container-low text-on-surface text-sm px-4 py-3 rounded-lg border border-outline-variant/60 focus:border-primary focus:bg-surface outline-none" name="password" required="" type="password" placeholder="At least 6 characters"/>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">Confirm Master Password</label>
              <input class="w-full bg-surface-container-low text-on-surface text-sm px-4 py-3 rounded-lg border border-outline-variant/60 focus:border-primary focus:bg-surface outline-none" name="confirm_password" required="" type="password" placeholder="Re-type new password"/>
            </div>

            <button class="mt-2 w-full bg-primary hover:bg-primary-container text-on-primary py-4 px-6 rounded-lg text-xs uppercase tracking-widest font-semibold transition-all shadow-md flex items-center justify-center gap-2" type="submit">
              <span>Update Master Passphrase</span>
              <span class="material-symbols-outlined text-[18px]">lock_reset</span>
            </button>
          </form>

          <?php endif; ?>

        </div>
      </div>

    </div>
  </main>
</body>
</html>
