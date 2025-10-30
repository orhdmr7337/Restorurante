<?php

class Purchase extends Connection
{
    public function createPurchase($data)
    {
        $query = "INSERT INTO purchases (supplier_id, invoice_number, total_amount, tax_amount, purchase_date, due_date, user_id, notes) 
                  VALUES (:supplier_id, :invoice_number, :total_amount, :tax_amount, :purchase_date, :due_date, :user_id, :notes)";
        $stmt = $this->con->prepare($query);
        $stmt->execute([
            ':supplier_id' => $data['supplier_id'],
            ':invoice_number' => $data['invoice_number'] ?? null,
            ':total_amount' => $data['total_amount'] ?? 0,
            ':tax_amount' => $data['tax_amount'] ?? 0,
            ':purchase_date' => $data['purchase_date'],
            ':due_date' => $data['due_date'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':notes' => $data['notes'] ?? null
        ]);
        return $this->con->lastInsertId();
    }

    public function addPurchaseItem($purchaseId, $materialId, $quantity, $unitPrice)
    {
        $totalPrice = $quantity * $unitPrice;
        $query = "INSERT INTO purchase_items (purchase_id, material_id, quantity, unit_price, total_price) 
                  VALUES (:purchase_id, :material_id, :quantity, :unit_price, :total_price)";
        $stmt = $this->con->prepare($query);
        $result = $stmt->execute([
            ':purchase_id' => $purchaseId,
            ':material_id' => $materialId,
            ':quantity' => $quantity,
            ':unit_price' => $unitPrice,
            ':total_price' => $totalPrice
        ]);

        if ($result) {
            $material = new Material();
            $material->addStockMovement($materialId, 'in', $quantity, 'purchase', $purchaseId);
        }
        return $result;
    }

    public function getPurchase($id)
    {
        $query = "SELECT p.*, s.name as supplier_name 
                  FROM purchases p 
                  LEFT JOIN suppliers s ON p.supplier_id = s.id 
                  WHERE p.id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPurchaseItems($purchaseId)
    {
        $query = "SELECT pi.*, m.name as material_name, m.unit 
                  FROM purchase_items pi 
                  LEFT JOIN materials m ON pi.material_id = m.id 
                  WHERE pi.purchase_id = :purchase_id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':purchase_id' => $purchaseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPurchases($limit = 50)
    {
        $query = "SELECT p.*, s.name as supplier_name 
                  FROM purchases p 
                  LEFT JOIN suppliers s ON p.supplier_id = s.id 
                  ORDER BY p.created_at DESC 
                  LIMIT :limit";
        $stmt = $this->con->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePaymentStatus($id, $status)
    {
        $query = "UPDATE purchases SET payment_status = :status WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function makePayment($purchaseId, $amount)
    {
        $purchase = $this->getPurchase($purchaseId);
        if (!$purchase) return false;

        $supplier = new Supplier();
        $supplier->updateBalance($purchase['supplier_id'], $amount, 'subtract');

        $remainingAmount = $purchase['total_amount'] - $amount;
        $status = ($remainingAmount <= 0) ? 'paid' : 'partial';
        
        return $this->updatePaymentStatus($purchaseId, $status);
    }

    public function getUnpaidPurchases()
    {
        $query = "SELECT p.*, s.name as supplier_name 
                  FROM purchases p 
                  LEFT JOIN suppliers s ON p.supplier_id = s.id 
                  WHERE p.payment_status IN ('unpaid', 'partial') 
                  ORDER BY p.due_date ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}
