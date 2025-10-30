<?php

class Staff extends Connection
{
    public function getAllStaff()
    {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.status = 1 
                  ORDER BY u.fullname ASC";
        return $this->con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaff($id)
    {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addStaff($data)
    {
        $query = "INSERT INTO users (username, password, email, fullname, role_id, salary, hire_date, user_position) 
                  VALUES (:username, :password, :email, :fullname, :role_id, :salary, :hire_date, :user_position)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':email' => $data['email'],
            ':fullname' => $data['fullname'],
            ':role_id' => $data['role_id'] ?? null,
            ':salary' => $data['salary'] ?? null,
            ':hire_date' => $data['hire_date'] ?? date('Y-m-d'),
            ':user_position' => $data['user_position'] ?? 3
        ]);
    }

    public function updateStaff($id, $data)
    {
        $query = "UPDATE users SET 
                  username = :username, 
                  email = :email, 
                  fullname = :fullname, 
                  role_id = :role_id, 
                  salary = :salary, 
                  user_position = :user_position 
                  WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':fullname' => $data['fullname'],
            ':role_id' => $data['role_id'],
            ':salary' => $data['salary'],
            ':user_position' => $data['user_position']
        ]);
    }

    public function deleteStaff($id)
    {
        $query = "UPDATE users SET status = 0 WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function paySalary($userId, $amount, $month, $year, $notes = null)
    {
        $query = "INSERT INTO salary_payments (user_id, amount, payment_date, period_month, period_year, notes) 
                  VALUES (:user_id, :amount, CURDATE(), :period_month, :period_year, :notes)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':user_id' => $userId,
            ':amount' => $amount,
            ':period_month' => $month,
            ':period_year' => $year,
            ':notes' => $notes
        ]);
    }

    public function getSalaryHistory($userId, $limit = 12)
    {
        $query = "SELECT * FROM salary_payments 
                  WHERE user_id = :user_id 
                  ORDER BY period_year DESC, period_month DESC 
                  LIMIT :limit";
        $stmt = $this->con->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addShift($userId, $date, $startTime, $endTime = null, $notes = null)
    {
        $query = "INSERT INTO shifts (user_id, shift_date, start_time, end_time, notes) 
                  VALUES (:user_id, :shift_date, :start_time, :end_time, :notes)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            ':user_id' => $userId,
            ':shift_date' => $date,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
            ':notes' => $notes
        ]);
    }

    public function getShifts($userId = null, $date = null)
    {
        $query = "SELECT s.*, u.fullname 
                  FROM shifts s 
                  LEFT JOIN users u ON s.user_id = u.id 
                  WHERE 1=1";
        $params = [];

        if ($userId) {
            $query .= " AND s.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        if ($date) {
            $query .= " AND s.shift_date = :shift_date";
            $params[':shift_date'] = $date;
        }
        $query .= " ORDER BY s.shift_date DESC, s.start_time DESC";

        $stmt = $this->con->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalStaffCount()
    {
        $query = "SELECT COUNT(*) as total FROM users WHERE status = 1";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getTotalSalaryExpense($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM salary_payments 
                  WHERE period_month = :month AND period_year = :year";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':month' => $month, ':year' => $year]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
