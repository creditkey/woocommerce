<?php
    namespace CreditKey;
    use CreditKey\CartContents;
    use CreditKey\Models\Order;

    final class Orders
    {
        private static function shouldLog()
        {
            if (!function_exists('get_option')) {
                return false;
            }
            $settings = get_option('woocommerce_credit_key_settings', []);
            return isset($settings['logging']) && $settings['logging'] === 'yes' && function_exists('wc_get_logger');
        }

        private static function log($message, $data = [])
        {
            if (self::shouldLog()) {
                wc_get_logger()->debug(print_r([
                    'sdk' => 'credit_key',
                    'message' => $message,
                    'data' => $data,
                ], true), ['source' => 'credit_key']);
            }
        }

        public static function confirm($ckOrderId, $merchantOrderId, $merchantOrderStatus, $cartContents, $charges)
        {
            $payload = array(
                'id' => $ckOrderId,
                'merchant_order_id' => $merchantOrderId,
                'merchant_status' => $merchantOrderStatus,
                'cart_contents' => CartContents::buildFormCartItems($cartContents),
                'charges' => $charges->toFormData()
            );

            self::log('orders.confirm.request', [
                'payload' => $payload,
            ]);

            try {
                $result = \CreditKey\Api::post('/ecomm/confirm_order', $payload);
                $logResult = is_array($result) ? $result : (is_object($result) ? (array) $result : []);
                if (isset($logResult['shipping_address'])) {
                    $logResult['shipping_address'] = '[redacted]';
                }
                self::log('orders.confirm.response', [ 'result' => $logResult ]);
                return Order::fromServiceData($result);
            } catch (\Throwable $e) {
                self::log('orders.confirm.error', [ 'error' => $e->getMessage() ]);
                throw $e;
            }
        }

        public static function update($ckOrderId, $merchantOrderStatus, $merchantOrderId, $cartContents, $charges, $shippingAddress)
        {
            $formData = array(
                'id' => $ckOrderId,
                'merchant_status' => $merchantOrderStatus,
                'merchant_order_id' => $merchantOrderId
            );

            if (!is_null($cartContents))
                $formData['cart_items'] = CartContents::buildFormCartItems($cartContents);

            if (!is_null($charges))
                $formData['charges'] = $charges->toFormData();

            if (!is_null($shippingAddress))
                $formData['shipping_address'] = $shippingAddress->toFormData();

            $logFormData = $formData;
            if (isset($logFormData['shipping_address'])) {
                $logFormData['shipping_address'] = '[redacted]';
            }
            self::log('orders.update.request', [ 'payload' => $logFormData ]);

            try {
                $result = \CreditKey\Api::post('/ecomm/update_order', $formData);
                $logResult = is_array($result) ? $result : (is_object($result) ? (array) $result : []);
                if (isset($logResult['shipping_address'])) {
                    $logResult['shipping_address'] = '[redacted]';
                }
                self::log('orders.update.response', [ 'result' => $logResult ]);
                return Order::fromServiceData($result);
            } catch (\Throwable $e) {
                self::log('orders.update.error', [ 'error' => $e->getMessage() ]);
                throw $e;
            }
        }

        public static function find($ckOrderId)
        {
            $result = \CreditKey\Api::get('/ecomm/find_order', array('id' => $ckOrderId));
            return Order::fromServiceData($result);
        }

        public static function findByMerchantOrderId($merchantOrderId)
        {
            $result = \CreditKey\Api::get('/ecomm/find_order_by_merchant_order_id',
                array('merchant_order_id' => $merchantOrderId));
            return Order::fromServiceData($result);
        }

        public static function cancel($ckOrderId)
        {
            $result = \CreditKey\Api::post('/ecomm/cancel_order', array('id' => $ckOrderId));
            return Order::fromServiceData($result);
        }

        public static function refund($ckOrderId, $refundAmount)
        {
            $result = \CreditKey\Api::post('/ecomm/refund', array(
                'id' => $ckOrderId,
                'amount' => $refundAmount
            ));
            return Order::fromServiceData($result);
        }
    }
?>
