<?php


namespace iflow\Utils;


use iflow\Utils\Message\baseMessage;

class basicTools
{
    use baseMessage;

    /**
     * 判断是否为合法的身份证号码
     * @param $vStr
     * @return static
     */
    public function isCreditNo($vStr): static
    {
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

    public function gmt_iso8601($time) : string
    {
        $dtStr = date("c", $time);
        try {
            $datetime = new \DateTime($dtStr);
            $expiration = $datetime->format(\DateTime::ISO8601);
            $pos = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration."Z";
        } catch (\Exception $e) {
            return $e -> getMessage();
        }
    }

    /**
     * 创建订单编号
     * @return string
     */
    public function make_order() : string
    {
        $order_id_main = date('YmdHis') . rand(1000, 9999);
        $order_id_sum = 0;
        for ($i = 0; $i < strlen($order_id_main); $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i,1));
        }
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    }

    public function gen_random_string(int $length = 20): string {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = '';
        for($i = 0; $i < $length; $i++) $str .= substr($chars, mt_rand(0, 62), 1);
        return $str;
    }

    // 创建随机数
    public function make_random_number() : string
    {
        $mtime = explode(' ',microtime());
        $random = $mtime[1] + $mtime[0] . rand(999, 9999);
        $random_sum = 0;
        for ($i = 0; $i < strlen($random); $i++) {
            $random_sum += (int)(substr($random, $i,1));
        }
        return str_replace('.', '', $random) . str_pad((100 - $random_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    }

    // 创建uuid
    public function create_uuid() : string
    {
        $chars = md5(uniqid(mt_rand(), true));
        return substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
    }

    // 身份证号加密
    public function card_id_replace($card_id) : string
    {
        $card_id_start = substr(substr_replace($card_id,"****",8,4), 0, 12);
        $card_id_end = substr(substr_replace($card_id,"****",14,4), 12, 6);
        return $card_id_start.$card_id_end;
    }

    public function xmlToArray(string $xml, string $className = 'SimpleXMLElement', $option = LIBXML_NOCDATA): array
    {
        return json_decode(
            json_encode(simplexml_load_string($xml, 'SimpleXMLElement', $option), JSON_UNESCAPED_UNICODE),
            true
        );
    }

    // 执行shell
    public function execShell($shell) {
        return trim(shell_exec($shell), PHP_EOL);
    }
}