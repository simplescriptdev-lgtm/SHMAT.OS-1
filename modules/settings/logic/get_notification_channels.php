<?php
// Логіка: отримаємо доступні канали сповіщень (плейсхолдер)

function settings_get_notification_channels(): array
{
    return ['email', 'slack', 'sms'];
}




