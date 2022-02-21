<?php
/**
 * Class for dealing with features, provided by http://api.vk.com
 */
class Vk {

    /**
     * Service url
     */
    private static $url;

    /**
     * Application id
     */
    private static $app = '';

    /**
     * Access token
     */
    private static $token = '';

    /**
     * Api version
     */
    private static $v = '';

    /**
     * Array of response codes, grouped by method names
     *
     * @var array
     */
    protected static $_codeA = [
        1 => 'Произошла неизвестная ошибка. Попробуйте повторить запрос позже.',
        2 => 'Приложение выключено. Необходимо включить приложение в настройках https://vk.com/editapp?id={Ваш API_ID} или использовать тестовый режим (test_mode=1)',
        3 => 'Передан неизвестный метод. Проверьте, правильно ли указано название вызываемого метода: http://vk.com/dev/methods',
        4 => 'Неверная подпись. Проверьте правильность формирования подписи запроса: https://vk.com/dev/api_nohttps',
        5 => 'Авторизация пользователя не удалась. Убедитесь, что Вы используете верную схему авторизации',
        6 => 'Слишком много запросов в секунду. Задайте больший интервал между вызовами или используйте метод execute. Подробнее об ограничениях на частоту вызовов см. на странице http://vk.com/dev/api_requests',
        7 => 'Нет прав для выполнения этого действия. Проверьте, получены ли нужные права доступа при авторизации. Это можно сделать с помощью метода account.getAppPermissions.',
        8 => 'Неверный запрос. Проверьте синтаксис запроса и список используемых параметров (его можно найти на странице с описанием метода)',
        9 => 'Слишком много однотипных действий. Нужно сократить число однотипных обращений. Для более эффективной работы Вы можете использовать execute или JSONP.',
        10 => 'Произошла внутренняя ошибка сервера. Попробуйте повторить запрос позже.',
        11 => 'В тестовом режиме приложение должно быть выключено или пользователь должен быть залогинен. Выключите приложение в настройках https://vk.com/editapp?id={Ваш API_ID}',
        14 => 'Требуется ввод кода с картинки (Captcha). Процесс обработки этой ошибки подробно описан на отдельной странице.',
        15 => 'Доступ запрещён. Убедитесь, что Вы используете верные идентификаторы, и доступ к контенту для текущего пользователя есть в полной версии сайта.',
        16 => 'Требуется выполнение запросов по протоколу HTTPS, т.к. пользователь включил настройку, требующую работу через безопасное соединение. Чтобы избежать появления такой ошибки, в Standalone-приложении Вы можете предварительно проверять состояние этой настройки у пользователя методом account.getInfo.',
        17 => 'Требуется валидация пользователя. Действие требует подтверждения — необходимо перенаправить пользователя на служебную страницу для валидации.',
        18 => 'Страница удалена или заблокирована. Страница пользователя была удалена или заблокирована',
        20 => 'Данное действие запрещено для не Standalone приложений. Если ошибка возникает несмотря на то, что Ваше приложение имеет тип Standalone, убедитесь, что при авторизации Вы используете redirect_uri=https://oauth.vk.com/blank.html. Подробнее см. http://vk.com/dev/auth_mobile.',
        21 => 'Данное действие разрешено только для Standalone и Open API приложений.',
        23 => 'Метод был выключен. Все актуальные методы ВК API, которые доступны в настоящий момент, перечислены здесь: http://vk.com/dev/methods.',
        24 => 'Требуется подтверждение со стороны пользователя.',
        100 => 'Один из необходимых параметров был не передан или неверен. Проверьте список требуемых параметров и их формат на странице с описанием метода.',
        101 => 'Неверный API ID приложения. Найдите приложение в списке администрируемых на странице http://vk.com/apps?act=settings и укажите в запросе верный API_ID (идентификатор приложения).',
        113 => 'Неверный идентификатор пользователя. Убедитесь, что Вы используете верный идентификатор. Получить ID по короткому имени можно методом utils.resolveScreenName.',
        150 => 'Неверный timestamp. Получить актуальное значение Вы можете методом utils.getServerTime.',
        200 => 'Доступ к альбому запрещён. Убедитесь, что Вы используете верные идентификаторы (для пользователей owner_id положительный, для сообществ — отрицательный), и доступ к запрашиваемому контенту для текущего пользователя есть в полной версии сайта.',
        201 => 'Доступ к аудио запрещён. Убедитесь, что Вы используете верные идентификаторы (для пользователей owner_id положительный, для сообществ — отрицательный), и доступ к запрашиваемому контенту для текущего пользователя есть в полной версии сайта.',
        203 => 'Доступ к группе запрещён. Убедитесь, что текущий пользователь является участником или руководителем сообщества (для закрытых и частных групп и встреч).',
        300 => 'Альбом переполнен. Перед продолжением работы нужно удалить лишние объекты из альбома или использовать другой альбом.',
        500 => 'Действие запрещено. Вы должны включить переводы голосов в настройках приложения. Проверьте настройки приложения: https://vk.com/editapp?id={Ваш API_ID}&section=payments',
        600 => 'Нет прав на выполнение данных операций с рекламным кабинетом.',
        603 => 'Произошла ошибка при работе с рекламным кабинетом.'
    ];

    /**
     * Get the response
     */
    private static function response($method, $data) {

        // Assign ini-props as internal props
        foreach (ini('vk') as $p => $v) if (property_exists(__CLASS__, $p)) self::$$p = $v;

        // Build request url
        $url = self::$url . $method;

        // Append 'v' param
        $data['v'] = self::$v;

        // Append 'access_token' param
        $data['access_token'] = self::$token;

        // Try make cURL request
        try {

            // Prepare and make CURL request
            $ch = curl_init($url . '?' . http_build_query($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
            $response = curl_exec($ch);
            curl_close($ch);

            // If response is boolean false - return is as is
            if ($response === false) $response = 'curl_exec() === false: ' . curl_error($ch);

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

        // Else if raw response can't be json-decoded
        else if (($json = json_decode($raw, true)) === null) $result = ['success' => false, 'msg' => $raw];

        // Else
        else {

            // Shortcut to $json['error']
            $e = $json['error'];

            // Build result
            $result = [
                'success' => $e ? false : true,
                'msg' => $e ? self::$_codeA[$e['error_code']] . "\n" . $e['error_msg'] : '',
                'json' => $json
            ];
        }

        // Start building result
        $result['request'] = $response['request'];

        // If no success
        if (!$result['success']) {

            // Ensure it to be logged
            Indi::logging('vkerr', true);

            // Log it
            Indi::log('vkerr', $result);
        }

        // Return result
        return $result;
    }

    /**
     * Send message
     *
     * @static
     * @param $to
     * @param $message
     * @return array
     */
    public static function send($to, $message) {

        // Setup method
        $method = 'messages.send';

        // Append data
        $data = [
            'domain' => $to,
            'message' => $message
        ];

        // Get response
        $response = self::response($method, $data);

        // Return result
        return self::result($method, $response);
    }

    /**
     * Get the object type by the given screen name $name
     *
     * @static
     * @param $name
     * @return array
     */
    public static function type($name) {

        // Setup method
        $method = 'utils.resolveScreenName';

        // Append data
        $data = [
            'screen_name' => $name
        ];

        // Get response
        $response = self::response($method, $data);

        // Return result
        return self::result($method, $response);
    }
}