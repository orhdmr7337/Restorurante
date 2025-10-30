<?php

class Material extends Connection
{
    // Tüm malzemeleri getir
    public function getAllMaterials()
    {
        $query = "SELECT * FROM materials ORDER BY name ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tek malzeme getir
    public function getMaterial($id)
    {
        $query = "SELECT * FROM materials WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Yeni malzeme ekle
    public function addMaterial($data)
    {
        $query = "INSERT INTO materials (name, unit, current_stock, min_stock, cost_price) 
                  VALUES (:name, :unit, :current_stock, :min_stock, :cost_price)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':unit' => $data['unit'],
            ':current_stock' => $data['current_stock'] ?? 0,
            ':min_stock' => $data['min_stock'] ?? 0,
            ':cost_price' => $data['cost_price'] ?? 0
        ]);
    }

    // Malzeme güncelle
    public function updateMaterial($id, $data)
    {
        $query = "UPDATE materials SET 
                  name = :name, 
                  unit = :unit, 
                  min_stock = :min_stock, 
                  cost_price = :cost_price 
                  WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':unit' => $data['unit'],
            ':min_stock' => $data['min_stock'],
            ':cost_price' => $data['cost_price']
        ]);
    }

    // Malzeme sil (soft delete)
    public function deleteMaterial($id)
    {
        $query = "DELETE FROM materials WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // Düşük stoklu malzemeler
    public function getLowStock()
    {
        $query = "SELECT * FROM materials 
                  WHERE current_stock <= min_stock 
                  ORDER BY current_stock ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Stok hareketi ekle
    public function addStockMovement($materialId, $type, $quantity, $referenceType = null, $referenceId = null, $userId = null, $notes = null)
    {
        // Stok hareketi kaydet
        $query = "INSERT INTO stock_movements (material_id, type, quantity, reference_type, reference_id, user_id, notes) 
                  VALUES (:material_id, :type, :quantity, :reference_type, :reference_id, :user_id, :notes)";
        $stmt = $this->con->prepare($query);
        $stmt->execute([
            ':material_id' => $materialId,
            ':type' => $type,
            ':quantity' => $quantity,
            ':reference_type' => $referenceType,
            ':reference_id' => $referenceId,
            ':user_id' => $userId,
            ':notes' => $notes
        ]);

        // Stok miktarını güncelle
        $updateQuery = "UPDATE materials SET current_stock = current_stock " . 
                       ($type == 'in' ? '+' : '-') . " :quantity WHERE id = :material_id";
        $updateStmt = $this->con->prepare($updateQuery);
        return $updateStmt->execute([
            ':quantity' => $quantity,
            ':material_id' => $materialId
        ]);
    }

    // Stok geçmişi
    public function getStockHistory($materialId, $limit = 50)
    {
        $query = "SELECT sm.*, u.username, m.name as material_name 
                  FROM stock_movements sm 
                  LEFT JOIN users u ON sm.user_id = u.id 
                  LEFT JOIN materials m ON sm.material_id = m.id 
                  WHERE sm.material_id = :material_id 
                  ORDER BY sm.created_at DESC 
                  LIMIT :limit";
        $stmt = $this->con->prepare($query);
        $stmt->bindValue(':material_id', $materialId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Toplam malzeme sayısı
    public function getTotalCount()
    {
        $query = "SELECT COUNT(*) as total FROM materials";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Toplam stok değeri
    public function getTotalStockValue()
    {
        $query = "SELECT SUM(current_stock * cost_price) as total_value FROM materials";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['total_value'] ?? 0;
    }
}
