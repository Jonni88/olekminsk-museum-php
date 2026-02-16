<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/VeteranModel.php';

$veteranModel = new VeteranModel();
$veterans = $veteranModel->getForPolk(200); // Получаем 200 ветеранов с фото
?\>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бессмертный полк | <?= e(SITE_NAME) ?\u003e</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(180deg, #0a0a1a 0%, #1a1a2e 30%, #16213e 70%, #0f3460 100%);
            min-height: 100vh;
            overflow: hidden;
            color: white;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.95), transparent);
            padding: 20px;
            z-index: 1000;
            text-align: center;
        }

        .header h1 {
            font-family: 'PT Serif', serif;
            font-size: 2rem;
            color: #d4af37;
            text-shadow: 0 0 30px rgba(212,175,55,0.5);
            margin-bottom: 10px;
        }

        .back-link {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #d4af37;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .scene {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            perspective: 1000px;
            perspective-origin: 50% 50%;
        }

        .road {
            position: absolute;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
        }

        .march-row {
            position: absolute;
            width: 100%;
            top: 40%;
            display: flex;
            justify-content: center;
            gap: 50px;
            transform-style: preserve-3d;
            opacity: 0;
        }

        .march-row.animating {
            animation: march-forward 15s linear forwards;
        }

        @keyframes march-forward {
            0% {
                transform: translateZ(-2000px) scale(0.3);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            40% {
                transform: translateZ(200px) scale(1.2);
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateZ(1500px) scale(3);
                opacity: 0;
            }
        }

        .veteran-portrait {
            width: 110px;
            height: 145px;
            background: linear-gradient(180deg, #f5f5dc 0%, #e0e0c0 100%);
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
            transform: rotateY(-5deg);
            position: relative;
        }

        .veteran-portrait img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: sepia(30%);
        }

        .veteran-name-tag {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            color: #d4af37;
            font-size: 0.75rem;
            padding: 5px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            z-index: 1000;
        }

        .btn {
            background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
            color: #1a1a1a;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212,175,55,0.4);
        }

        .counter {
            position: fixed;
            top: 100px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
        }

        .counter-number {
            font-size: 2rem;
            color: #d4af37;
            font-weight: bold;
        }

        .counter-label {
            font-size: 0.8rem;
            color: #888;
        }

        /* Мобильная версия */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.3rem;
            }
            .veteran-portrait {
                width: 80px;
                height: 105px;
            }
            .veteran-name-tag {
                font-size: 0.65rem;
                padding: 3px;
            }
            .march-row {
                gap: 25px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/" class="back-link">← Назад</a>
        <h1>Бессмертный полк</h1>
        <p>Олёкминский район</p>
    </header>

    <div class="counter">
        <div class="counter-number" id="counter">0</div>
        <div class="counter-label">героев прошло</div>
    </div>

    <div class="scene" id="scene">
        <div class="road" id="road"></div>
    </div>

    <div class="controls">
        <button class="btn" id="startBtn">▶ Начать шествие</button>
        <button class="btn" id="pauseBtn" style="display: none;">⏸ Пауза</button>
    </div>

    <script>
        const veterans = <?= json_encode($veterans) ?\u003e;
        let currentIndex = 0;
        let isPlaying = false;
        let intervalId = null;
        let shownCount = 0;

        const road = document.getElementById('road');
        const startBtn = document.getElementById('startBtn');
        const pauseBtn = document.getElementById('pauseBtn');
        const counter = document.getElementById('counter');

        function createVeteranCard(veteran) {
            const div = document.createElement('div');
            div.className = 'veteran-portrait';
            
            const img = document.createElement('img');
            img.src = `/uploads/photos/${veteran.photo_main}`;
            img.alt = veteran.last_name;
            
            const nameTag = document.createElement('div');
            nameTag.className = 'veteran-name-tag';
            nameTag.textContent = `${veteran.last_name} ${veteran.first_name[0]}.`;
            
            div.appendChild(img);
            div.appendChild(nameTag);
            
            div.addEventListener('click', () => {
                window.open(`/veteran.php?id=${veteran.id}`, '_blank');
            });
            
            return div;
        }

        function createRow() {
            const row = document.createElement('div');
            row.className = 'march-row';
            
            // 5 человек на ПК, 3 на мобильных
            const isMobile = window.innerWidth <= 768;
            const count = isMobile ? 3 : 5;
            
            for (let i = 0; i < count; i++) {
                if (currentIndex >= veterans.length) {
                    currentIndex = 0; // Начинаем сначала
                }
                
                const card = createVeteranCard(veterans[currentIndex]);
                row.appendChild(card);
                currentIndex++;
            }
            
            road.appendChild(row);
            
            // Запускаем анимацию с небольшой задержкой
            setTimeout(() => {
                row.classList.add('animating');
            }, 50);
            
            // Обновляем счётчик
            shownCount += count;
            counter.textContent = shownCount;
            
            // Удаляем после анимации
            setTimeout(() => {
                row.remove();
            }, 15000);
        }

        function startMarch() {
            if (veterans.length === 0) {
                alert('Нет ветеранов с фотографиями в базе');
                return;
            }
            
            isPlaying = true;
            startBtn.style.display = 'none';
            pauseBtn.style.display = 'block';
            
            // Первый ряд сразу
            createRow();
            
            // Новые ряды каждые 3 секунды
            intervalId = setInterval(createRow, 3000);
        }

        function pauseMarch() {
            isPlaying = false;
            clearInterval(intervalId);
            startBtn.style.display = 'block';
            pauseBtn.style.display = 'none';
            startBtn.textContent = '▶ Продолжить';
        }

        startBtn.addEventListener('click', startMarch);
        pauseBtn.addEventListener('click', pauseMarch);

        // Предзагрузка фото
        window.addEventListener('load', () => {
            veterans.slice(0, 20).forEach(v => {
                const img = new Image();
                img.src = `/uploads/photos/${v.photo_main}`;
            });
        });
    </script>
</body>
</html>
