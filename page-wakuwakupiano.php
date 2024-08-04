<?php
/*
Template Name: わくわくピアノ
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> <?php Arkhe::root_attrs(); ?>>
<head>
<meta charset="utf-8">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, viewport-fit=cover">
<?php
	wp_head();
	$setting = Arkhe::get_setting(); // SETTING取得
?>
<style>
    body {
        margin: 0;
        background-color: #add8e6; /* 水色の背景色 */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    canvas {
        display: block;
    }
</style>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3/dist/phaser.min.js"></script>
    <script>
    const assetsUrl = "<?php echo get_template_directory_uri(); ?>/assets/games";

    const config = {
        type: Phaser.AUTO,
        width: window.innerWidth, // ゲームの幅をウィンドウの幅に合わせる
        height: window.innerHeight, // ゲームの高さをウィンドウの高さに合わせる
        backgroundColor: '#add8e6', // 水色の背景色を設定
        physics: {
            default: 'arcade',
            arcade: {
                gravity: { y: 0 },
                debug: false
            }
        },
        scene: {
            preload: preload,
            create: create
        }
    };

    // Phaserのインスタンスを作成
    const game = new Phaser.Game(config);

    // 音階と周波数の対応
    const noteFrequencies = {
        'ド': 261.63,
        'レ': 293.66,
        'ミ': 329.63,
        'ファ': 349.23,
        'ソ': 392.00,
        'ラ': 440.00,
        'シ': 493.88,
        'ド#': 277.18,
        'レ#': 311.13,
        'ファ#': 369.99,
        'ソ#': 415.30,
        'ラ#': 466.16,
        '高ド': 523.25 // 1オクターブ高いド
    };

    // AudioContextを初期化
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    // 音を鳴らす関数
    function playSound(frequency, scene) {
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.type = 'square'; // 波形をスクエア波に設定
        oscillator.frequency.setValueAtTime(frequency, audioCtx.currentTime);
        
        const attack = 0.01; // アタックタイム
        const decay = 0.05; // ディケイタイム
        const sustain = 0.3; // サステインレベル
        const release = 0.1; // リリースタイム

        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(1, audioCtx.currentTime + attack);
        gainNode.gain.linearRampToValueAtTime(sustain, audioCtx.currentTime + attack + decay);
        gainNode.gain.setValueAtTime(sustain, audioCtx.currentTime + attack + decay);
        gainNode.gain.linearRampToValueAtTime(0, audioCtx.currentTime + attack + decay + release);

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + attack + decay + release); // アタック、ディケイ、サステイン、リリースを考慮した時間

        // カラフルなモチーフを生成
        createMotif(scene);

        // ランダムに鳥を飛び出す
        if (Math.random() < 0.1) { // 10%の確率で鳥を飛び出す
            createBird(scene);
        }
    }

    function preload() {
        // ここで必要なリソースを読み込む
        this.load.image('bird', '${assetsUrl}/pikopo.svg'); // 鳥の画像をロード
    }

    function createMotif(scene) {
        const colors = ['#fea5b2', '#ffcb9c', '#fff599', '#9dffde', '#eda9ed', '#a2a4ff']; // ペールトーンの色
        const shapes = ['circle', 'rectangle', 'triangle', 'star', 'heart'];
        const motifCount = 10; // 一度に飛び出すモチーフの数

        for (let i = 0; i < motifCount; i++) {
            const shapeType = shapes[Math.floor(Math.random() * shapes.length)];
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Phaser.Math.Between(15, 50); // 大小様々で全体的に小さく
            let motif;

            if (shapeType === 'circle') {
                motif = scene.add.graphics();
                motif.fillStyle(Phaser.Display.Color.HexStringToColor(color).color, 1);
                motif.fillCircle(scene.cameras.main.centerX, scene.cameras.main.centerY, size);
                motif.lineStyle(1, 0x000000, 1);
                motif.strokeCircle(scene.cameras.main.centerX, scene.cameras.main.centerY, size);
            } else if (shapeType === 'rectangle') {
                motif = scene.add.graphics();
                motif.fillStyle(Phaser.Display.Color.HexStringToColor(color).color, 1);
                motif.fillRoundedRect(scene.cameras.main.centerX - size * 0.75, scene.cameras.main.centerY - size * 0.5, size * 1.5, size, 10);
                motif.lineStyle(1, 0x000000, 1);
                motif.strokeRoundedRect(scene.cameras.main.centerX - size * 0.75, scene.cameras.main.centerY - size * 0.5, size * 1.5, size, 10);
            } else if (shapeType === 'triangle') {
                motif = scene.add.graphics();
                motif.fillStyle(Phaser.Display.Color.HexStringToColor(color).color, 1);
                motif.fillPoints([
                    { x: scene.cameras.main.centerX, y: scene.cameras.main.centerY - size },
                    { x: scene.cameras.main.centerX + size, y: scene.cameras.main.centerY + size },
                    { x: scene.cameras.main.centerX - size, y: scene.cameras.main.centerY + size }
                ]);
                motif.lineStyle(1, 0x000000, 1);
                motif.strokePoints([
                    { x: scene.cameras.main.centerX, y: scene.cameras.main.centerY - size },
                    { x: scene.cameras.main.centerX + size, y: scene.cameras.main.centerY + size },
                    { x: scene.cameras.main.centerX - size, y: scene.cameras.main.centerY + size }
                ], true);
            } else if (shapeType === 'star') {
                motif = createStar(scene, scene.cameras.main.centerX, scene.cameras.main.centerY, 5, size, size / 2, color);
            } else if (shapeType === 'heart') {
                motif = createHeart(scene, scene.cameras.main.centerX, scene.cameras.main.centerY, size, color);
            }

            motif.setDepth(-1); // 鍵盤の背面に配置

            scene.tweens.add({
                targets: motif,
                x: { value: Phaser.Math.Between(-50, scene.sys.game.config.width + 150), duration: 1500 }, // 画面の外に散らばる
                y: { value: Phaser.Math.Between(-50, scene.sys.game.config.height + 150), duration: 1500 }, // 画面の外に散らばる
                alpha: { value: 0, duration: 4000 }, // 最後に透明にして消える
                scaleX: { value: 2, duration: 1500 },
                scaleY: { value: 2, duration: 1500 },
                ease: 'Cubic.easeOut',
                onComplete: () => {
                    motif.destroy();
                }
            });
        }
    }

    function createStar(scene, x, y, points, radius1, radius2, color) {
        const star = scene.add.graphics();
        const path = createStarPoints(points, radius1, radius2);
        star.fillStyle(Phaser.Display.Color.HexStringToColor(color).color, 1);
        star.fillPoints(path, true);
        star.lineStyle(1, 0x000000, 1);
        star.strokePoints(path, true);
        star.x = x;
        star.y = y;
        return star;
    }

    function createStarPoints(points, radius1, radius2) {
        const step = Math.PI / points;
        const path = [];
        for (let i = 0; i < 2 * points; i++) {
            const radius = i % 2 === 0 ? radius1 : radius2;
            const angle = i * step;
            path.push({ x: Math.cos(angle) * radius, y: Math.sin(angle) * radius });
        }
        return path;
    }

    function createHeart(scene, x, y, size, color) {
        const heart = scene.add.graphics();
        heart.fillStyle(Phaser.Display.Color.HexStringToColor(color).color, 1);
        heart.fillPoints(createHeartPoints(size), true);
        heart.lineStyle(1, 0x000000, 1);
        heart.strokePoints(createHeartPoints(size), true);
        heart.x = x;
        heart.y = y;
        return heart;
    }

    function createHeartPoints(size) {
        const heartPoints = [];
        const xOffset = 0;
        const yOffset = 0;
        for (let deg = 0; deg <= 180; deg += 1) {
            const rad = Phaser.Math.DegToRad(deg);
            const x = size * 0.5 * Math.pow(Math.sin(rad), 3);
            const y = -size * 0.5 * (0.8125 * Math.cos(rad) - 0.3125 * Math.cos(2 * rad) - 0.125 * Math.cos(3 * rad) - 0.0625 * Math.cos(4 * rad));
            heartPoints.push({ x: x + xOffset, y: y + yOffset });
        }
        for (let deg = 180; deg >= 0; deg -= 1) {
            const rad = Phaser.Math.DegToRad(deg);
            const x = -size * 0.5 * Math.pow(Math.sin(rad), 3);
            const y = -size * 0.5 * (0.8125 * Math.cos(rad) - 0.3125 * Math.cos(2 * rad) - 0.125 * Math.cos(3 * rad) - 0.0625 * Math.cos(4 * rad));
            heartPoints.push({ x: x + xOffset, y: y + yOffset });
        }
        return heartPoints;
    }

    function createBird(scene) {
        const bird = scene.add.sprite(scene.cameras.main.centerX, scene.cameras.main.centerY, 'bird');
        bird.setScale(0.1); // 鳥のサイズを調整
        bird.setDepth(-1); // 鍵盤の背面に配置

        scene.tweens.add({
            targets: bird,
            x: { value: Phaser.Math.Between(-50, scene.sys.game.config.width + 150), duration: 6000 },
            y: { value: Phaser.Math.Between(-50, scene.sys.game.config.height + 150), duration: 6000 },
            alpha: { value: 0, duration: 4000 },
            rotation: { value: 6.28, duration: 6000 }, // 2πラジアン (360度) 回転
            ease: 'Cubic.easeOut',
            onComplete: () => {
                bird.destroy();
            }
        });
    }

    function create() {
        // ここでピアノの鍵盤を作成するロジックを記述する
        const whiteKeys = ['ド', 'レ', 'ミ', 'ファ', 'ソ', 'ラ', 'シ', '高ド']; // 白鍵盤のキー
        const blackKeys = ['ド#', 'レ#', 'ファ#', 'ソ#', 'ラ#']; // 黒鍵盤のキー

        const keyWidth = Math.min(80, window.innerWidth / (whiteKeys.length + 0.65)); // 鍵盤の幅を画面幅に合わせる
        const whiteKeyHeight = window.innerHeight > 320 ?  keyWidth * 4 : window.innerHeight / 2; // 白鍵盤の高さ
        const blackKeyHeight = whiteKeyHeight * 2 / 3; // 黒鍵盤の高さ（白鍵盤の3分の2）

        // 鍵盤を画面に配置するための計算
        const totalKeys = whiteKeys.length;
        const startX = window.innerWidth / 2 - ((keyWidth) * totalKeys) / 2 + (keyWidth / 2);
        const startY = window.innerHeight / 2;

        // 白鍵盤を描画
        whiteKeys.forEach((key, index) => {
            const keyX = startX + index * keyWidth;
            const keyShape = this.add.rectangle(keyX, startY, keyWidth, whiteKeyHeight, 0xffffff);
            keyShape.setInteractive();

            // 黒い線で囲む
            keyShape.setStrokeStyle(2, 0x000000); // 線の太さと色を設定

            // クリック処理
            keyShape.on('pointerdown', () => {
                console.log('Key clicked:', key);
                playSound(noteFrequencies[key], this); // 音を鳴らす
            });
        });

        // 黒鍵盤を描画
        blackKeys.forEach((key, index) => {
            const whiteKeyIndex = [0, 1, 3, 4, 5][index]; // 対応する白鍵盤のインデックスを取得

            if (whiteKeyIndex !== undefined) {
                const whiteKeyX = startX - 6 + whiteKeyIndex * keyWidth;
                const keyX = whiteKeyX + keyWidth * 0.65; // 黒鍵盤の幅を調整して配置
                const keyShape = this.add.rectangle(keyX, startY - whiteKeyHeight / 6, keyWidth * 0.7, blackKeyHeight, 0x000000);
                keyShape.setInteractive();
                
                // クリック処理
                keyShape.on('pointerdown', () => {
                    console.log('Key clicked:', key);
                    playSound(noteFrequencies[key], this); // 音を鳴らす
                });
            }
        });
    }

    </script>
</body>
</html>