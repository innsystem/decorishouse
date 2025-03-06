<?php

namespace App\Helpers;

class MessageHelper
{
    public static function getMessage($module, $channel, $action, $data = [])
    {
        $messages = include base_path("app/Messages/{$module}Messages.php");

        if (!isset($messages[$channel][$action])) {
            return null;
        }

        $message = $messages[$channel][$action];

        if (is_array($message)) {
            $message['subject'] = self::replacePlaceholders($message['subject'], $data);
            $message['body'] = self::replacePlaceholders($message['body'], $data);
            return $message;
        }

        return self::replacePlaceholders($message, $data);
    }

    private static function replacePlaceholders($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }
        return $content;
    }
}
