<?php
/**
 * Class for dealing with features, provided by http://sms.ru
 */
class Sms {

    /**
     * Access key
     */
    private static $api_id = '';

    /**
     * Service url
     */
    private static $url;

    /**
     * Array of response codes, grouped by method names
     *
     * @var array
     */
    protected static $_codeA = [
        'sms/send' => [
            100 => 'Cообщение принято к отправке. На следующих строчках вы найдете идентификаторы отправленных сообщений в том же порядке, в котором вы указали номера, на которых совершалась отправка.',
            201 => 'Не хватает средств на лицевом счету',
            203 => 'Нет текста сообщения',
            204 => 'Имя отправителя не согласовано с администрацией',
            205 => 'Сообщение слишком длинное (превышает 8 СМС)',
            206 => 'Будет превышен или уже превышен дневной лимит на отправку сообщений',
            207 => 'На этот номер (или один из номеров) нельзя отправлять сообщения, либо указано более 100 номеров в списке получателей',
            208 => 'Параметр time указан неправильно',
            209 => 'Вы добавили этот номер (или один из номеров) в стоп-лист',
            212 => 'Текст сообщения необходимо передать в кодировке UTF-8 (вы передали в другой кодировке)',
            230 => 'Сообщение не принято к отправке, так как на один номер в день нельзя отправлять более 60 сообщений.',
        ],
        'sms/status' => [
            -1 => '	Сообщение не найдено.',
            100 => 'Сообщение находится в нашей очереди',
            101 => 'Сообщение передается оператору',
            102 => 'Сообщение отправлено (в пути)',
            103 => 'Сообщение доставлено',
            104 => 'Не может быть доставлено: время жизни истекло',
            105 => 'Не может быть доставлено: удалено оператором',
            106 => 'Не может быть доставлено: сбой в телефоне',
            107 => 'Не может быть доставлено: неизвестная причина',
            108 => 'Не может быть доставлено: отклонено',
        ],
        'sms/cost' => [
            100 => 'Запрос выполнен. На второй строчке указана стоимость сообщения. На третьей строчке указана его длина.',
            207 => 'На этот номер нельзя отправлять сообщения',
        ],
        'my/balance' => [
            100 => 'Запрос выполнен. На второй строчке вы найдете ваше текущее состояние баланса.',
        ],
        'my/limit' => [
            100 => 'Запрос выполнен. На второй строчке вы найдете количество номеров, на которое вы можете отправлять сообщения внутри дня. На третьей строчке - количество номеров, на которые вы уже отправили сообщения внутри текущего дня.',
        ],
        'my/senders' => [
            100 => 'Запрос выполнен. На второй и последующих строчках вы найдете ваших одобренных отправителей, которые можно использовать в параметре &from= метода sms/send.',
        ],
        'auth/check' => [
            100 => 'ОК, номер телефона и пароль совпадают.',
        ],
        'stoplist/add' => [
            100 => 'Номер добавлен в стоплист.',
            202 => 'Номер телефона в неправильном формате'
        ],
        'stoplist/del' => [
            100 => 'Номер удален из стоплиста.',
            202 => 'Номер телефона в неправильном формате'
        ],
        'stoplist/get' => [
            100	=> 'Запрос обработан. На последующих строчках будут идти номера телефонов, указанных в стоплисте в формате номер;примечание.'
        ],
        'shared' => [
            200 => 'Неправильный api_id',
            202 => 'Неправильно указан получатель',
            210 => 'Используется GET, где необходимо использовать POST',
            211 => 'Метод не найден',
            220 => 'Сервис временно недоступен, попробуйте чуть позже.',
            300 => 'Неправильный token (возможно истек срок действия, либо ваш IP изменился)',
            301 => 'Неправильный пароль, либо пользователь не найден',
            302 => 'Пользователь авторизован, но аккаунт не подтвержден (пользователь не ввел код, присланный в регистрационной смс)'
        ]
    ];

    /**
     * Get the response
     */
    private static function response($method, $data) {

        // Assign ini-props as internal props
        foreach (ini('sms') as $p => $v) if (property_exists(__CLASS__, $p)) self::$$p = $v;

        // Build request url
        $url = self::$url . $method;

        // Append 'api_id' param
        $data['api_id'] = self::$api_id;

        // Try make cURL request
        try {

            // Prepare and make CURL request
            $cur = curl_init($url);
            curl_setopt($cur, CURLOPT_TIMEOUT, 30);
            curl_setopt($cur, CURLOPT_POST, 1);
            curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cur, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($cur);

            // If response is boolean false - return is as is
            if ($response === false) $response = 'curl_exec() === false: ' . curl_error($cur);

            // Close curl
            curl_close($cur);

        // If Exception caught
        } catch (Exception $e) {
            $response = 'curl exception: ' . $e->getMessage();
        }

        // Return complete info related to request and it's response
        return ['request' => ['url' => $url, 'data' => $data], 'response' => $response];
    }

    /**
     * Deal with cURL request's response
     *
     * @param $method
     * @param $response
     * @return array
     */
    public static function result($method, $response) {

        // Get raw response
        $raw = $response['response'];

        // If cURL response is empty
        if (empty($raw)) $result = ['success' => false, 'msg' => 'Empty cURL raw response'];

        // Else if cURL error occured
        else if (preg_match('/^curl/', $raw)) $result = ['success' => false, 'msg' => $raw];

        // Else
        else {

            // Split raw response on lines
            $rawA = explode("\n", $raw);

            // Get response code
            $code = trim($rawA[0]);

            // If $code is one of 100,101,102,103
            $result = [
                'success' => in($code, '100,101,102,103'),
                'msg' => self::$_codeA[$method][$code] ?: self::$_codeA['shared'][$code],
                'raw' => $raw
            ];
        }

        // Start building result
        $result['request'] = $response['request'];

        // If no success
        if (!$result['success']) {

            // Ensure it to be logged
            Indi::logging('smserr', true);

            // Log it
            Indi::log('smserr', $result);
        }

        // Return result
        return $result;
    }

    /**
     * Send sms
     *
     * @static
     * @param $to
     * @param $text
     * @return mixed
     */
    public static function send($to, $text) {

        // Setup method
        $method = 'sms/send';

        // Check phone numbers validity
        $phoneA = [];
        foreach ($_ = ar($to) as $phone)
            if ($phone = preg_replace('/[^0-9]/', '', $phone))
                if (strlen($phone) >= 11)
                    $phoneA[$phone] = true;

        // If no valid phone numbers detected - return
        if (!$phoneA) return;

        // Prepare data
        $data = [
            'from' => ini('sms')->from,
            'to' => im(array_keys($phoneA)),
            'text' => $text,
            //'translit' => 1
        ];

        // Get response
        $response = self::response($method, $data);

        // Return result
        return self::result($method, $response);
    }
}