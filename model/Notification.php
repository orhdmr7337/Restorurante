<?php

class Notification extends Connection
{
    public function create($type, $title, $message, $userId = null)
    {
        $query = "INSERT INTO notifications (type, title, message, user_id) 
                  VALUES (:type, :title, :message, :user_id)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':type' => $type,
            ':title' => $title,
            ':message' => $message,
            ':user_id' => $userId
        ]);
    }

    public function getUnread($userId = null)
    {
        if ($userId) {
            $query = "SELECT * FROM notifications 
                      WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0 
                      ORDER BY created_at DESC";
            $stmt = $this->con->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $query = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($userId = null, $limit = 50)
    {
        if ($userId) {
            $query = "SELECT * FROM notifications 
                      WHERE user_id = :user_id OR user_id IS NULL 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            $stmt = $this->con->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->con->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($id)
    {
        $query = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function markAllAsRead($userId = null)
    {
        if ($userId) {
            $query = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id OR user_id IS NULL";
            $stmt = $this->con->prepare($query);
            return $stmt->execute([':user_id' => $userId]);
        }
        
        $query = "UPDATE notifications SET is_read = 1";
        return $this->con->exec($query);
    }

    public function sendStockAlert($materialId)
    {
        $material = new Material();
        $mat = $material->getMaterial($materialId);
        
        if ($mat && $mat['current_stock'] <= $mat['min_stock']) {
            $title = "Düşük Stok Uyarısı";
            $message = "{$mat['name']} stok seviyesi minimum seviyenin altında! Mevcut: {$mat['current_stock']} {$mat['unit']}";
            return $this->create('stock', $title, $message);
        }
        return false;
    }

    public function sendDebtAlert($accountId)
    {
        $account = new Account();
        $acc = $account->getAccount($accountId);
        
        if ($acc && $acc['balance'] < 0) {
            $title = "Borç Uyarısı";
            $message = "{$acc['name']} için ödenmemiş borç: " . abs($acc['balance']) . " TL";
            return $this->create('debt', $title, $message);
        }
        return false;
    }

    public function sendOrderNotification($orderId, $tableId)
    {
        $title = "Yeni Sipariş";
        $message = "Masa {$tableId} için yeni sipariş alındı.";
        return $this->create('order', $title, $message);
    }

    public function sendDailyReport()
    {
        $finance = new Finance();
        $report = $finance->getDailyReport();
        
        $title = "Günlük Rapor";
        $message = "Gelir: {$report['income']} TL, Gider: {$report['expense']} TL, Kâr: {$report['profit']} TL";
        return $this->create('report', $title, $message);
    }

    public function getUnreadCount($userId = null)
    {
        if ($userId) {
            $query = "SELECT COUNT(*) as total FROM notifications 
                      WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0";
            $stmt = $this->con->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        }
        
        $query = "SELECT COUNT(*) as total FROM notifications WHERE is_read = 0";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
