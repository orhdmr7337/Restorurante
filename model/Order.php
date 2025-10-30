<?php

class Order extends Connection
{
    // masadaki siparişe ürün ekleme
    public function addProductToTableOrder($productId, $tableId)
    {
        // ürün id ve masa id gelir
        // masa üstünde açık sipariş var mı diye bakılır
        // sipariş yoksa yeni sipariş oluşturulur ve id'si alınır
        if (!$orderId = $this->isTableHaveOrder($tableId)) {
            // Bu masada siparis yok, bi tane olusturalim
            $orderId = $this->createOrder($tableId);
        }
        // Bu masada siparis varmıs. idsi: ". $orderId
        // sipariş varsa aktif id alınır
        // ürün değerleriyle beraber sipariş ürünleri tablosuna eklenir
        return $this->addProductToOrder($productId, $orderId);
    }

    private function isTableHaveOrder($tableId)
    {
        // SELECT id FROM orders WHERE status = 1 AND table_id = $tableId
        $isTableHaveOrder = $this->con->prepare("SELECT id FROM orders WHERE status = 1 AND table_id = :tableid");
        $isTableHaveOrder->execute(array("tableid" => $tableId));
        $isTableHaveOrder = $isTableHaveOrder->fetch(PDO::FETCH_ASSOC);
        if (is_array($isTableHaveOrder)) return $isTableHaveOrder['id'];
        return $isTableHaveOrder;
    }

    private function createOrder($tableId)
    {
        // masa id'si gelir, bu id ile yeni bir sipariş açarız
        $createOrderQuery = $this->con->exec("INSERT INTO orders (table_id) VALUES ('" . $tableId . "')");
        if ($createOrderQuery) {
            // sipariş açıldıysa masa durumu aktif olur
            $orderId = $this->con->lastInsertId();
            $this->con->exec("UPDATE tables SET status = 1 WHERE id = " . $tableId);
            return $orderId;
        }
        return false;
    }

    private function addProductToOrder($productId, $orderId)
    {
        // order_products tablosuna ürün bilgilerini siparişe bağlı olarak ekleyeceğiz
        $product = $this->con->query("SELECT name, price FROM products WHERE id = " . $productId)->fetch(PDO::FETCH_ASSOC);
        $newOrderProduct = $this->con->exec("INSERT INTO order_products (order_id, product_id, product_name, product_price) VALUES ('" . $orderId . "', '" . $productId . "', '" . $product['name'] . "', '" . $product['price'] . "')");
        if ($newOrderProduct) {
            return $this->con->lastInsertId();
        }
        return false;
    }

    public function getTableOrderedItems($tableId)
    {
        // sipariş edilmiş ürünleri dizi içinde döndürür
        $items = [];
        // $tableId üzerindeki orderId'ye ihtiyacım var
        if (!$orderId = $this->isTableHaveOrder($tableId)) {
            return $items;
        }
        // Ürünleri grupla ve miktarını say
        $orderItems = $this->con->query("
            SELECT 
                MIN(id) as id,
                product_id,
                product_name,
                product_price,
                COUNT(*) as total,
                order_id
            FROM order_products 
            WHERE order_id = " . $orderId . " 
            GROUP BY product_id, product_name, product_price
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        return $orderItems;
    }
    
     public function getAllOrdersCount(){
        $getAllOrdersCount = $this->con->query('SELECT COUNT(id) FROM orders')->fetch(PDO::FETCH_ASSOC);
        return $getAllOrdersCount;
   }

    public function deleteProductFromOrder($orderProductId)
    {
        // Önce bu ürünün bilgilerini al
        $product = $this->con->query("SELECT product_id, order_id FROM order_products WHERE id = " . $orderProductId)->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Aynı üründen kaç tane var?
            $count = $this->con->query("SELECT COUNT(*) as total FROM order_products WHERE product_id = " . $product['product_id'] . " AND order_id = " . $product['order_id'])->fetch(PDO::FETCH_ASSOC);
            
            if ($count['total'] > 1) {
                // Birden fazla varsa sadece bir tanesini sil
                $delete = $this->con->exec("DELETE FROM order_products WHERE id = " . $orderProductId . " LIMIT 1");
            } else {
                // Tek tane varsa hepsini sil
                $delete = $this->con->exec("DELETE FROM order_products WHERE product_id = " . $product['product_id'] . " AND order_id = " . $product['order_id']);
            }
            
            if ($delete > 0) return $delete;
        }
        
        return false;
    }

    public function cancelTableOrder($tableId)
    {
        // masa siparişini yakala
        if ($orderId = $this->isTableHaveOrder($tableId)) {
            // masa siparişini sil
            $this->deleteOrder($orderId);
            $tableCont = new Table();
            $tableCont->deactive($tableId);
        }
    }

    private function deleteOrder($orderId)
    {
        $deleteOrder = $this->con->exec("DELETE FROM orders WHERE id=" . $orderId);
        $deleteProducts = $this->con->exec("DELETE FROM order_products WHERE order_id=" . $orderId);
        if ($deleteOrder && $deleteProducts) {
            return true;
        }
        return false;
    }

    public function moveTableOrder($fromTableId, $toTableId)
    {
        // siparişin table_id'sini değiştireceğiz
        if ($orderId = $this->isTableHaveOrder($fromTableId)) {
            $moveQ = $this->con->prepare("UPDATE orders SET table_id = :toTableId WHERE id = :orderId");
            //$moveQ->execute(array("toTableId" => $toTableId, "orderId" => $orderId));
            $moveQ->execute(compact("toTableId", "orderId"));
            // $fromTable pasif edilecek
            $tblCont = new Table();
            $tblCont->deactive($fromTableId);
            // $toTable Aktif edilecek
            $tblCont->active($toTableId);
        }
    }

    public function closeTableOrder($tableId,$userID)
    {
        if ($orderId = $this->isTableHaveOrder($tableId)) {

            // fiyatları güncelle
            $edit = $this->con->exec("update orders set total_amount=(select sum(product_price) from order_products where order_id=$orderId),user_id=$userID where id=$orderId");

            // siparişi pasif yap
            $this->deactive($orderId);

            // tabloyu pasif yap
            $tableCont = new Table();
            $tableCont->deactive($tableId);

            if($edit==0)
                return false;
            return true;
        }
    }

    private function changeStatus($orderId, $status)
    {
        $chst = $this->con->query("UPDATE orders SET status=" . $status . " WHERE id=" . $orderId);
        if ($chst) {
            return true;
        }
        return false;

    }

    public function deactive($orderId)
    {
        return $this->changeStatus($orderId, 0);
    }

    public function active($orderId)
    {
        return $this->changeStatus($orderId, 1);
    }
    
    public function getTodayOrders()
    {
        $today = date('Y-m-d');
        $stmt = $this->con->prepare("SELECT * FROM orders WHERE DATE(created_at) = :today");
        $stmt->execute(['today' => $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOrdersByTable($tableId)
    {
        $stmt = $this->con->prepare("SELECT * FROM order_products WHERE order_id IN (SELECT id FROM orders WHERE table_id = :tableid AND status = 1)");
        $stmt->execute(['tableid' => $tableId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActiveOrderByTable($tableId)
    {
        $stmt = $this->con->prepare("SELECT * FROM orders WHERE table_id = :tableid AND status = 1 LIMIT 1");
        $stmt->execute(['tableid' => $tableId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function moveOrderToTable($orderId, $newTableId)
    {
        $stmt = $this->con->prepare("UPDATE orders SET table_id = :newtable WHERE id = :orderid");
        return $stmt->execute(['newtable' => $newTableId, 'orderid' => $orderId]);
    }
    
    public function mergeOrders($sourceOrderId, $targetOrderId)
    {
        // Kaynak siparişin ürünlerini hedef siparişe taşı
        $stmt = $this->con->prepare("UPDATE order_products SET order_id = :target WHERE order_id = :source");
        return $stmt->execute(['target' => $targetOrderId, 'source' => $sourceOrderId]);
    }
    
    public function closeOrder($orderId)
    {
        $stmt = $this->con->prepare("UPDATE orders SET status = 0 WHERE id = :orderid");
        return $stmt->execute(['orderid' => $orderId]);
    }

    // sipariş ekleme
    // siparişe ürün ekleme
    // siparişten ürün silme
    // siparişin masası
    // siparişin toplam tutarı
    // siparişteki ürünler
    // sipariş durumu
    // sipariş kapatma
}
