<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px;">
        <h1 style="color: #333; margin-top: 0;">Подтверждение email адреса</h1>
        
        <p>Здравствуйте!</p>
        
        <p>Для подтверждения вашего email адреса используйте следующий код:</p>
        
        <div style="background-color: #fff; border: 2px solid #007bff; border-radius: 5px; padding: 20px; text-align: center; margin: 20px 0;">
            <h2 style="color: #007bff; font-size: 32px; letter-spacing: 5px; margin: 0;">{{ $code }}</h2>
        </div>
        
        <p>Этот код действителен в течение <strong>15 минут</strong>.</p>
        
        <p>Если вы не запрашивали подтверждение email, просто проигнорируйте это письмо.</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #666; font-size: 12px; margin: 0;">
            Это автоматическое сообщение, пожалуйста, не отвечайте на него.
        </p>
    </div>
</body>
</html>

