<?php

class Finance extends Connection
{
    public function addIncome($data)
    {
        try {
            $query = "INSERT INTO incomes (category, amount, description, income_date, payment_method, reference_type, reference_id, user_id) 
                      VALUES (:category, :amount, :description, :income_date, :payment_method, :reference_type, :reference_id, :user_id)";
            $stmt = $this->con->prepare($query);
            $result = $stmt->execute([
                ':category' => $data['category'] ?? 'Satış',
                ':amount' => $data['amount'],
                ':description' => $data['description'] ?? null,
                ':income_date' => $data['income_date'] ?? date('Y-m-d'),
                ':payment_method' => $data['payment_method'] ?? 'cash',
                ':reference_type' => $data['reference_type'] ?? null,
                ':reference_id' => $data['reference_id'] ?? null,
                ':user_id' => $data['user_id'] ?? null
            ]);

            // Kasa işlemini API'de yapıyoruz, burada tekrar yapmaya gerek yok
            return $result;
        } catch (PDOException $e) {
            error_log("Finance addIncome error: " . $e->getMessage());
            throw new Exception("Gelir kaydı hatası: " . $e->getMessage());
        }
    }

    public function addExpense($data)
    {
        $query = "INSERT INTO expenses (category, amount, description, expense_date, payment_method, user_id) 
                  VALUES (:category, :amount, :description, :expense_date, :payment_method, :user_id)";
        $stmt = $this->con->prepare($query);
        $result = $stmt->execute([
            ':category' => $data['category'],
            ':amount' => $data['amount'],
            ':description' => $data['description'] ?? null,
            ':expense_date' => $data['expense_date'] ?? date('Y-m-d'),
            ':payment_method' => $data['payment_method'] ?? 'cash',
            ':user_id' => $data['user_id'] ?? null
        ]);

        if ($result && $data['payment_method'] == 'cash') {
            $this->addCashTransaction('out', $data['amount'], $data['description'], 'expense', $this->con->lastInsertId(), $data['user_id']);
        }
        return $result;
    }

    public function addCashTransaction($type, $amount, $description = null, $referenceType = null, $referenceId = null, $userId = null)
    {
        $query = "INSERT INTO cash_transactions (type, amount, description, reference_type, reference_id, transaction_date, user_id) 
                  VALUES (:type, :amount, :description, :reference_type, :reference_id, CURDATE(), :user_id)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':type' => $type,
            ':amount' => $amount,
            ':description' => $description,
            ':reference_type' => $referenceType,
            ':reference_id' => $referenceId,
            ':user_id' => $userId
        ]);
    }

    public function addBankTransaction($accountName, $type, $amount, $description = null, $referenceNumber = null)
    {
        $query = "INSERT INTO bank_transactions (account_name, type, amount, description, reference_number, transaction_date) 
                  VALUES (:account_name, :type, :amount, :description, :reference_number, CURDATE())";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':account_name' => $accountName,
            ':type' => $type,
            ':amount' => $amount,
            ':description' => $description,
            ':reference_number' => $referenceNumber
        ]);
    }

    public function getCashBalance()
    {
        $query = "SELECT 
                  (SELECT COALESCE(SUM(amount), 0) FROM cash_transactions WHERE type = 'in') - 
                  (SELECT COALESCE(SUM(amount), 0) FROM cash_transactions WHERE type = 'out') as balance";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }

    public function getBankBalance()
    {
        $query = "SELECT 
                  (SELECT COALESCE(SUM(amount), 0) FROM bank_transactions WHERE type = 'in') - 
                  (SELECT COALESCE(SUM(amount), 0) FROM bank_transactions WHERE type = 'out') as balance";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }

    public function getDailyReport($date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM incomes WHERE income_date = :date";
        $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date = :date";
        
        $incomeStmt = $this->con->prepare($incomeQuery);
        $incomeStmt->execute([':date' => $date]);
        $income = $incomeStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $expenseStmt = $this->con->prepare($expenseQuery);
        $expenseStmt->execute([':date' => $date]);
        $expense = $expenseStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'date' => $date,
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense
        ];
    }

    public function getMonthlyReport($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM incomes 
                        WHERE MONTH(income_date) = :month AND YEAR(income_date) = :year";
        $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
                         WHERE MONTH(expense_date) = :month AND YEAR(expense_date) = :year";
        
        $incomeStmt = $this->con->prepare($incomeQuery);
        $incomeStmt->execute([':month' => $month, ':year' => $year]);
        $income = $incomeStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $expenseStmt = $this->con->prepare($expenseQuery);
        $expenseStmt->execute([':month' => $month, ':year' => $year]);
        $expense = $expenseStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'month' => $month,
            'year' => $year,
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense
        ];
    }

    public function getProfitLoss($startDate, $endDate)
    {
        $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM incomes 
                        WHERE income_date BETWEEN :start_date AND :end_date";
        $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
                         WHERE expense_date BETWEEN :start_date AND :end_date";
        
        $incomeStmt = $this->con->prepare($incomeQuery);
        $incomeStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        $income = $incomeStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $expenseStmt = $this->con->prepare($expenseQuery);
        $expenseStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        $expense = $expenseStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense
        ];
    }
}
