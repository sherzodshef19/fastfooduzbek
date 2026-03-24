<?php

class PosPrinter {
    private $ip;
    private $port;

    public function __construct($ip, $port = 9100) {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function send($data) {
        $fp = @fsockopen($this->ip, $this->port, $errno, $errstr, 3);
        if (!$fp) {
            return ["success" => false, "message" => "Printer bilan bog'lanib bo'lmadi ($errstr)"];
        }

        // Send data
        fwrite($fp, $data);
        
        // Cut paper command (Esc/POS standard)
        fwrite($fp, "\x1D\x56\x41\x03"); // GS V A 3 (Partial cut)
        
        fclose($fp);
        return ["success" => true];
    }

    public static function formatReceipt($order, $items, $store_info) {
        // Simple text formatting for Esc/POS
        $out = "";
        $out .= "\x1B\x61\x01"; // Align center
        $out .= "\x1B\x21\x30"; // Double height and double width
        $out .= $store_info['name'] . "\n";
        $out .= "\x1B\x21\x00"; // Reset style
        $out .= $store_info['address'] . "\n";
        $out .= "Tel: " . $store_info['phone'] . "\n";
        $out .= "--------------------------------\n";
        $out .= "\x1B\x61\x00"; // Align left
        $out .= "Check #" . $order['id'] . "\n";
        $out .= "Sana: " . date('d.m.Y H:i', strtotime($order['created_at'])) . "\n";
        $out .= "Ishchi: " . $order['waiter_name'] . "\n";
        $out .= "--------------------------------\n";
        
        foreach ($items as $item) {
            $name = $item['product_name'];
            if (strlen($name) > 20) $name = substr($name, 0, 17) . "...";
            $line = str_pad($name, 20) . str_pad($item['quantity'] . "x", 4) . str_pad(number_format($item['price'], 0, '', ' '), 8, " ", STR_PAD_LEFT);
            $out .= $line . "\n";
        }
        
        $out .= "--------------------------------\n";
        $out .= "\x1B\x61\x02"; // Align right
        $out .= "JAMI: " . number_format($order['total_amount'], 0, '', ' ') . " so'm\n";
        $out .= "\n\n\n\n";
        return $out;
    }

    public static function formatKitchen($order, $items) {
        // Simplified ticket for kitchen
        $out = "";
        $out .= "\x1B\x61\x01"; // Align center
        $out .= "\x1B\x21\x30"; // Double height and double width
        $out .= "OSHXONA\n";
        $out .= "\x1B\x21\x10"; // Double height only
        $out .= "Stol: " . $order['table_name'] . "\n";
        $out .= "\x1B\x21\x00"; // Reset style
        $out .= "Check #" . $order['id'] . "\n";
        $out .= "--------------------------------\n";
        $out .= "\x1B\x61\x00"; // Align left
        
        foreach ($items as $item) {
            $out .= "\x1B\x21\x10"; // Larger font for kitchen
            $out .= $item['quantity'] . " x " . $item['product_name'] . "\n";
            $out .= "\x1B\x21\x00"; // Reset
        }
        
        $out .= "--------------------------------\n";
        $out .= date('H:i:s') . "\n\n\n\n";
        return $out;
    }
}
