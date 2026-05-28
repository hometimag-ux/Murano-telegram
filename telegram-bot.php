<?php
// telegram-bot.php - обработчик уведомлений
header('Content-Type: application/json');

// НАСТРОЙКИ (замените на свои)
$botToken = 'ВАШ_ТОКЕН_БОТА';
$chatId = 'ВАШ_CHAT_ID';

function sendTelegramMessage($message, $botToken, $chatId) {
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

// Обработка входящих запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type'] ?? '';
    
    if ($type === 'order') {
        $orderId = $input['orderId'];
        $amount = $input['amount'];
        $items = $input['items'];
        $delivery = $input['delivery'];
        $address = $input['address'] ?? 'Не указан';
        
        $message = "🛍️ <b>НОВЫЙ ЗАКАЗ!</b>\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "📦 Заказ: {$orderId}\n";
        $message .= "💰 Сумма: {$amount} ₽\n";
        $message .= "🚚 Доставка: {$delivery['name']} ({$delivery['price']} ₽)\n";
        $message .= "🏠 Адрес: {$address}\n";
        $message .= "📋 Товары:\n";
        
        foreach ($items as $item) {
            $message .= "  • {$item['name']} x{$item['quantity']} = " . ($item['price'] * $item['quantity']) . " ₽\n";
        }
        
        sendTelegramMessage($message, $botToken, $chatId);
        echo json_encode(['success' => true]);
        
    } elseif ($type === 'feedback') {
        $name = $input['name'];
        $email = $input['email'];
        $topic = $input['topic'];
        $rating = $input['rating'];
        $message = $input['message'];
        
        $telegramMsg = "💬 <b>НОВОЕ СООБЩЕНИЕ</b>\n";
        $telegramMsg .= "━━━━━━━━━━━━━━━━\n";
        $telegramMsg .= "👤 Имя: {$name}\n";
        $telegramMsg .= "📧 Email: {$email}\n";
        $telegramMsg .= "📌 Тема: {$topic}\n";
        $telegramMsg .= "⭐ Оценка: {$rating}/5\n";
        $telegramMsg .= "💬 Сообщение:\n{$message}\n";
        
        sendTelegramMessage($telegramMsg, $botToken, $chatId);
        echo json_encode(['success' => true]);
    }
}
?>
