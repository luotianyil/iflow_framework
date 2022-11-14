<?php


namespace iflow\Utils;


use DateTimeInterface;
use iflow\Utils\BuildResponseBody\ResponseMessage;

class BasicTools
{
    use ResponseMessage;

    /**
     * 判断是否为合法的身份证号码
     * @param $vStr
     * @return static
     */
    public function isCreditNo($vStr): static {
        $vCity = [
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        ];
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) $this->error = "身份证格式错误";
        if (!in_array(substr($vStr, 0, 2), $vCity)) $this->error = "身份证省市代码错误";
        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);
        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) $this->error = "身份证年龄格式错误";
        if ($vLength == 18) {
            $vSum = 0;
            for ($i = 17 ; $i >= 0 ; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }
            if($vSum % 11 != 1) $this->error = "身份证格式错误";
        }
        return $this;
    }

    /**
     * 生成 GMT-IOS8601 规范时间
     * @param $time
     * @return string
     */
    public function gmt_iso8601($time) : string {
        $dtStr = date("c", $time);
        try {
            $datetime = new \DateTime($dtStr);
            $expiration = $datetime->format(DateTimeInterface::ISO8601);
            $pos = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration."Z";
        } catch (\Exception $e) {
            return $e -> getMessage();
        }
    }

    /**
     * 创建订单编号
     * @param string $prefix
     * @return string
     */
    public function makeOrder(string $prefix = '') : string {
        $order_id_main = (microtime(true) * 10000) . rand(1000, 9999);
        $order_id_sum = 0;
        for ($i = 0; $i < strlen($order_id_main); $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i,1));
        }
        return $prefix . $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    }


    /**
     * 身份证脱敏
     * @param string $card_id
     * @param int $offset
     * @param int $length
     * @param string $replace
     * @return string
     */
    public function card_id_replace(string $card_id, int $offset = 8, int $length = 4, string $replace = '*') : string {

        $replaceLength = $offset + $length;
        $cardIdList = str_split($card_id);

        if ($replaceLength > 18) return 'offset exceeds CardId length';
        if (count($cardIdList) > 18 || count($cardIdList) < 18) return 'unknown CardId';

        for ($i = $offset; $i <= $replaceLength; $i++) $cardIdList[$offset] = $replace;
        return implode($cardIdList);
    }

    /**
     * xml转数组
     * @param string $xml
     * @param string $className
     * @param int $option
     * @return array
     */
    public function xmlToArray(string $xml, string $className = 'SimpleXMLElement', int $option = LIBXML_NOCDATA): array
    {
        return json_decode(
            json_encode(simplexml_load_string($xml, 'SimpleXMLElement', $option), JSON_UNESCAPED_UNICODE),
            true
        );
    }

    /**
     * 执行shell
     * @param string $shell
     * @return string
     */
    public function execShell(string $shell = ''): string {
        $responseBody = shell_exec($shell);
        return $responseBody ? trim($responseBody, PHP_EOL) : '';
    }

    /**
     * 对象转 数组
     * @param object $object
     * @return array
     */
    public function objectToArray(object $object): array {
        if (method_exists($object, 'toArray')) return $object -> toArray();
        return get_object_vars($object);
    }

    /**
     * 根据两点经纬度 获取 距离
     * @param array $location [ 'lat', 'lng' ]
     * @param array $nextLocation [ 'lat', 'lng' ]
     * @return float
     */
    public function getDistance(array $location, array $nextLocation): float {

        $locationLat = $location[0] * M_PI / 180.0;
        $nextLocationLat = $nextLocation[0] * M_PI / 180.0;

        $locationLng = $location[1] * M_PI / 180.0;
        $nextLocationLng = $nextLocation[1] * M_PI / 180.0;

        $calcLongitude = $nextLocationLng - $locationLng;
        $calcLatitude = $nextLocationLat - $locationLat;

        $sept = 2 * asin(
            sqrt(pow(sin($calcLatitude / 2), 2))
                + cos($locationLat)
                * cos($nextLocationLat)
                * pow(sin($calcLongitude / 2), 2)
            );
        return sprintf("%.3f", round($sept * 6367 * 10000) / 10000);
    }
}