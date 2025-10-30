<?php

class Supplier extends Connection
{
    public function getAllSuppliers()
    {
        $query = "SELECT * FROM suppliers ORDER BY name ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSupplier($id)
    {
        $query = "SELECT * FROM suppliers WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addSupplier($data)
    {
        $query = "INSERT INTO suppliers (name, contact_person, phone, email, address, tax_number, iban) 
                  VALUES (:name, :contact_person, :phone, :email, :address, :tax_number, :iban)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':contact_person' => $data['contact_person'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':address' => $data['address'] ?? null,
            ':tax_number' => $data['tax_number'] ?? null,
            ':iban' => $data['iban'] ?? null
        ]);
    }

    public function updateSupplier($id, $data)
    {
        $query = "UPDATE suppliers SET 
                  name = :name, 
                  contact_person = :contact_person, 
                  phone = :phone, 
                  email = :email, 
                  address = :address, 
                  tax_number = :tax_number, 
                  iban = :iban 
                  WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':contact_person' => $data['contact_person'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':address' => $data['address'],
            ':tax_number' => $data['tax_number'],
            ':iban' => $data['iban']
        ]);
    }

    public function deleteSupplier($id)
    {
        $query = "UPDATE suppliers SET status = 0 WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function getSupplierBalance($id)
    {
        $query = "SELECT balance FROM suppliers WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }

    public function updateBalance($id, $amount, $operation = 'add')
    {
        $operator = ($operation == 'add') ? '+' : '-';
        $query = "UPDATE suppliers SET balance = balance {$operator} :amount WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':amount' => $amount, ':id' => $id]);
    }

    public function getTotalCount()
    {
        $query = "SELECT COUNT(*) as total FROM suppliers WHERE status = 1";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getTotalDebt()
    {
        $query = "SELECT SUM(balance) as total_debt FROM suppliers WHERE status = 1 AND balance > 0";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['total_debt'] ?? 0;
    }
}
