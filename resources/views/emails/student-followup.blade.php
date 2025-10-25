<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
        .message-body {
            white-space: pre-line;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Plataforma de Microlearning UC</h2>
    </div>
    
    <div class="content">
        <div class="message-body">
            {{ $messageBody }}
        </div>
    </div>
    
    <div class="footer">
        <p>Este es un mensaje automático de la Plataforma de Microlearning UC.</p>
        <p>Por favor, no responder a este email.</p>
    </div>
</body>
</html>