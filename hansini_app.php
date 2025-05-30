<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Will You Be My Valentine?</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #ffebee;
            font-family: Arial, sans-serif;
        }
        .container {
            text-align: center;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #e91e63;
            margin-bottom: 20px;
        }
        .buttons {
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        #yes {
            background-color: #e91e63;
            color: white;
        }
        #no {
            background-color: #f8bbd0;
            color: #ad1457;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Will you be my Valentine?</h1>
        <p>Pookie please...</p>
        <div class="buttons">
            <button id="yes">Yes</button>
            <button id="no">No</button>
        </div>
    </div>

    <script>
        document.getElementById('yes').addEventListener('click', function() {
            alert("Yay! I'm so happy! ❤️");
        });
        
        document.getElementById('no').addEventListener('mouseover', function() {
            const button = this;
            const container = document.querySelector('.container');
            const containerRect = container.getBoundingClientRect();
            
            // Move button to random position within container
            const maxX = containerRect.width - button.offsetWidth;
            const maxY = containerRect.height - button.offsetHeight;
            
            button.style.position = 'absolute';
            button.style.left = Math.random() * maxX + 'px';
            button.style.top = Math.random() * maxY + 'px';
        });
    </script>
</body>
</html>