<?php

class Account extends Connection
{
    public function createAccount($type, $data)
    {
        $query = "INSERT INTO accounts (type, name, phone, email, address, tax_number, credit_limit) 
                  VALUES (:type, :name, :phone, :email, :address, :tax_number, :credit_limit)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':type' => $type,
            ':name' => $data['name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':address' => $data['address'] ?? null,
            ':tax_number' => $data['tax_number'] ?? null,
            ':credit_limit' => $data['credit_limit'] ?? 0
        ]);
    }

    public function getAllAccounts($type = null)
    {
        if ($type) {
            $query = "SELECT * FROM accounts WHERE type = :type ORDER BY name ASC";
            $stmt = $this->con->prepare($query);
            $stmt->execute([':type' => $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $query = "SELECT * FROM accounts ORDER BY name ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccount($id)
    {
        $query = "SELECT * FROM accounts WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addTransaction($accountId, $type, $amount, $description = null, $referenceType = null, $referenceId = null, $userId = null)
    {
        $query = "INSERT INTO account_transactions (account_id, type, amount, description, reference_type, reference_id, transaction_date, user_id) 
                  VALUES (:account_id, :type, :amount, :description, :reference_type, :reference_id, CURDATE(), :user_id)";
        $stmt = $this->con->prepare($query);
        $result = $stmt->execute([
            ':account_id' => $accountId,
            ':type' => $type,
            ':amount' => $amount,
            ':description' => $description,
            ':reference_type' => $referenceType,
            ':reference_id' => $referenceId,
            ':user_id' => $userId
        ]);

        if ($result) {
            $operator = ($type == 'debit') ? '-' : '+';
            $updateQuery = "UPDATE accounts SET balance = balance {$operator} :amount WHERE id = :account_id";
            $updateStmt = $this->con->prepare($updateQuery);
            $updateStmt->execute([':amount' => $amount, ':account_id' => $accountId]);
        }
        return $result;
    }

    public function getAccountBalance($id)
    {
        $query = "SELECT balance FROM accounts WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }

    public function getAccountStatement($id, $startDate = null, $endDate = null)
    {
        $query = "SELECT * FROM account_transactions WHERE account_id = :account_id";
        $params = [':account_id' => $id];

        if ($startDate && $endDate) {
            $query .= " AND transaction_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }
        $query .= " ORDER BY transaction_date DESC, created_at DESC";

        $stmt = $this->con->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function makePayment($accountId, $amount, $method = 'cash', $notes = null, $userId = null)
    {
        $query = "INSERT INTO debt_payments (account_id, amount, payment_method, payment_date, notes, user_id) 
                  VALUES (:account_id, :amount, :payment_method, CURDATE(), :notes, :user_id)";
        $stmt = $this->con->prepare($query);
        $result = $stmt->execute([
            ':account_id' => $accountId,
            ':amount' => $amount,
            ':payment_method' => $method,
            ':notes' => $notes,
            ':user_id' => $userId
        ]);

        if ($result) {
            $this->addTransaction($accountId, 'credit', $amount, "Ödeme: {$notes}", 'payment', $this->con->lastInsertId(), $userId);
        }
        return $result;
    }

    public function getDebtors()
    {
        $query = "SELECT * FROM accounts WHERE balance < 0 ORDER BY balance ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCreditors()
    {
        $query = "SELECT * FROM accounts WHERE balance > 0 ORDER BY balance DESC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}
